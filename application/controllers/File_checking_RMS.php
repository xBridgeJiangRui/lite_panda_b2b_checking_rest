<?php

require(APPPATH.'/libraries/REST_Controller.php');

class File_checking_RMS extends REST_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
    }

    public function setting($type)
    {
        $result = $this->db->query("SELECT `value` FROM b2b_doc.b2b_setting_parameter WHERE `module` = 'RMS' AND isactive = '1' AND `type` = '$type' LIMIT 1")->row('value');

        return rtrim($result, '/');
    }

    public function index_get()
    {
        $directory = $this->setting('from_location');

        $files = scandir($directory);
        $file_inserted = 0;

        foreach ($files as $file) {

            $filePath = $directory .'/'. $file;

            if ($file !== '.' && $file !== '..' && is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'txt') {
                $check_exist = $this->db->query("SELECT COUNT(*) AS cnt FROM b2b_doc.rms_filename WHERE `filename` = '$file'")->row('cnt');

                if($check_exist == 0){

                    $insert_data = array(
                        'filename_guid' => $this->db->query("SELECT REPLACE(UPPER(UUID()),'-','') AS `guid`;")->row('guid'),
                        'filename'      => $file,
                        'created_at'    => $this->db->query("SELECT NOW() AS `current_time`;")->row('current_time'),
                        'doc_status'    => 'PENDING'
                    );
            
                    $this->db->insert('b2b_doc.rms_filename', $insert_data);
                    $file_inserted++;

                }
            }
        }

        $this->response(
            [
                'status' => true,
                'message' => $file_inserted != 0 ? 'Document Inserted' : 'No Document to be Insert',
            ]

        );
    }

    public function get_pending_get()
    {
        $directory = $this->setting('from_location');
        $pending_list = $this->db->query("SELECT * FROM b2b_doc.rms_filename WHERE `doc_status` = 'PENDING'")->result_array();

        foreach ($pending_list as $list){

            $file_guid = $list['filename_guid'];
            $file = $directory.'/'.$list['filename'];
            $content = file_get_contents($file);
            
            $rows = explode("\n", $content);
            $headers = explode("|", $rows[0]);

            $sortedData = [];

            for ($i = 1; $i < count($rows); $i++) {
                $columns = explode("|", $rows[$i]);

                if(count($headers) == count($columns)){

                    $cleanedHeaders = array_map(function ($item) {
                        return preg_replace('/\s+/', '', trim($item));
                    }, $headers);

                    $rowData = array_combine($cleanedHeaders, $columns);
                    $sortedData[] = $rowData;
                }
                
            }

            usort($sortedData, function ($a, $b) {
                return strcmp($a['INVOICENUMBER'], $b['INVOICENUMBER']);
            });

            foreach($sortedData as $row){

                if(isset($row['VENDORCODE']) && isset($row['INVOICEDATE']) && isset($row['INVOICENUMBER']) && isset($row['INVOICETYPE']) && isset($row['AMOUNT']) && isset($row['INVOICEFILENAME'])){

                    $inv_file_name = $row['INVOICEFILENAME'];
                    $inv_file_name = str_replace('.pdf', '', $inv_file_name);

                    $inv_directory = $this->setting('inv_location');
                    $inv_files = scandir($inv_directory);

                    $inv_files = array_map(function ($value) {
                        return str_replace('.pdf', '', $value);
                    }, $inv_files);

                    $inv_files = array_map(function ($value) {
                        return preg_replace('/\s+/', '', trim($value));
                    }, $inv_files);

                    if(in_array(trim($inv_file_name), $inv_files)){

                        $insert_data = array(
                            'supcode'    => $row['VENDORCODE'],
                            'supname'    => '',
                            'doctype'    => 'RMS_'.$row['INVOICETYPE'],
                            'doctime'    => $list['created_at'],
                            'refno'      => $row['INVOICENUMBER'],
                            'created_by' => 'URL_TASK',
                            'created_at' => $this->db->query("SELECT NOW() AS `current_time`;")->row('current_time'),
                        );

                        $sql = $this->db->insert_string('b2b_doc.other_doc', $insert_data);
                        $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
                        $this->db->query($sql);

                        $insert_mappingdata = array(
                            'doctype'       => 'RMS_'.$row['INVOICETYPE'],
                            'cross_refno'   => $row['INVOICENUMBER'],
                            'file_refno'    => $inv_file_name,
                            'cross_supcode' => $row['VENDORCODE'],
                            'file_supcode'  => $row['VENDORCODE'],
                            'filename'      => $list['filename'],
                            'created_at'    => $this->db->query("SELECT NOW() AS `current_time`;")->row('current_time'),
                            'created_by'    => 'URL_TASK',
                            'updated_at'    => $this->db->query("SELECT NOW() AS `current_time`;")->row('current_time'),
                            'updated_by'    => 'URL_TASK',
                        );
                
                        $sql = $this->db->insert_string('b2b_doc.other_doc_mapping', $insert_mappingdata);
                        $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
                        $this->db->query($sql);

                    }
                }
                
            }

            $this->db->query("UPDATE b2b_doc.rms_filename SET `doc_status` = 'COMPLETE', extracted_at = NOW() WHERE filename_guid = '$file_guid'");

        }

        $this->response(
            [
                'status' => true,
                'message' => sizeof($pending_list) == 0 ? 'No pending document' : 'Success extract document',
            ]
        );
    }

    public function old_upload_document_get()
    {
        $directory = $this->setting('from_location');
        $remoteHost = $this->setting('sftp_host');
        $remoteUsername = $this->setting('sftp_username');
        $remotePassword = $this->setting('sftp_pwd');
        $remoteDirectory = $this->setting('sftp_dir');

        $ftpConnection = ftp_connect($remoteHost);
        if (!$ftpConnection) {

            $this->response(
                [
                    'status' => false,
                    'message' => 'Failed to connect to FTP server',
                ]
            );die;

        }

        if (!ftp_login($ftpConnection, $remoteUsername, $remotePassword)) {

            $this->response(
                [
                    'status' => false,
                    'message' => 'Failed to authenticate with FTP server',
                ]
            );die;

        }

        if (!ftp_chdir($ftpConnection, $remoteDirectory)) {

            $this->response(
                [
                    'status' => false,
                    'message' => 'Failed to change directory on FTP server',
                ]
            );die;

        }

        $pending_list = $this->db->query("SELECT * FROM b2b_doc.rms_filename WHERE `doc_status` = 'COMPLETE'")->result_array();

        foreach ($pending_list as $list){

            $file_guid = $list['filename_guid'];
            $file = $directory.'/'.$list['filename'];
            $content = file_get_contents($file);
            
            $rows = explode("\n", $content);
            $headers = explode("|", $rows[0]);

            $sortedData = [];

            for ($i = 1; $i < count($rows); $i++) {
                $columns = explode("|", $rows[$i]);

                if(count($headers) == count($columns)){

                    $cleanedHeaders = array_map(function ($item) {
                        return preg_replace('/\s+/', '', trim($item));
                    }, $headers);

                    $rowData = array_combine($cleanedHeaders, $columns);
                    $sortedData[] = $rowData;
                }
                
            }

            usort($sortedData, function ($a, $b) {
                return strcmp($a['INVOICENUMBER'], $b['INVOICENUMBER']);
            });

            foreach($sortedData as $row){

                if(isset($row['VENDORCODE']) && isset($row['INVOICEDATE']) && isset($row['INVOICENUMBER']) && isset($row['INVOICETYPE']) && isset($row['AMOUNT']) && isset($row['INVOICEFILENAME'])){
                
                    $refno = $row['INVOICENUMBER'];
                    $filename = $row['INVOICEFILENAME'];
                    $inv_file_name = $row['INVOICEFILENAME'];
                    $inv_file_name = str_replace('.pdf', '', $inv_file_name);

                    $inv_directory = $this->setting('inv_location');
                    $inv_files = scandir($inv_directory);

                    $inv_files = array_map(function ($value) {
                        return str_replace('.pdf', '', $value);
                    }, $inv_files);

                    $inv_files = array_map(function ($value) {
                        return preg_replace('/\s+/', '', trim($value));
                    }, $inv_files);

                    if(in_array(trim($inv_file_name), $inv_files)){
    
                        $localFile = $this->setting('inv_location').'/'.$filename;
                        $log_guid = $this->db->query("SELECT REPLACE(UPPER(UUID()),'-','') AS uuid")->row('uuid');

                        $log_data = array(
                            'log_guid'          => $log_guid,
                            'doc_refno'         => $refno,
                            'file_name'         => $inv_file_name,
                            'method'            => 'ftp_put',
                            'from'              => $_SERVER['SERVER_NAME'],
                            'to'                => $remoteHost,
                            'datetime_start'    => $this->db->query("SELECT NOW() as current_datetime")->row('current_datetime'),
                        );
                
                        $this->db->insert('b2b_doc.doc_movement_log', $log_data);

                        if (!ftp_put($ftpConnection, basename($localFile), $localFile, FTP_BINARY)) {

                            $response = array(
                                'status'    => false,
                                'message'   => 'Failed to upload file to FTP server',
                            );

                            $log_data = array(
                                'response'      => json_encode($response),
                                'datetime_end'  => $this->db->query("SELECT NOW() as current_datetime")->row('current_datetime'),
                                'status'        => 0,
                            );
                    
                            $this->db->where('log_guid', $log_guid);
                            $this->db->update('b2b_doc.doc_movement_log', $log_data);
                            
                            echo json_encode($response);die;
                        }

                        $response = array(
                            'status'    => true,
                            'message'   => 'Success upload file to FTP server',
                        );

                        $log_data = array(
                            'response'      => json_encode($response),
                            'datetime_end'  => $this->db->query("SELECT NOW() as current_datetime")->row('current_datetime'),
                            'status'        => 1,
                        );
                
                        $this->db->where('log_guid', $log_guid);
                        $this->db->update('b2b_doc.doc_movement_log', $log_data);

                        $this->db->query("UPDATE b2b_doc.other_doc SET `doc_uploaded` = '1', doc_uploaded_at = NOW() WHERE refno = '$refno' AND doc_uploaded = '0' LIMIT 1");
                    }
                }
                
            }

        }

        ftp_close($ftpConnection);

        $this->response(
            [
                'status' => true,
                'message' => sizeof($pending_list) == 0 ? 'No document to be uploaded' : 'Success upload document',
            ]
        );
    }

    public function upload_document_get()
    {
        $directory = $this->setting('from_location');
        $remoteHost = $this->setting('sftp_host');
        $remoteUsername = $this->setting('sftp_username');
        $remotePassword = $this->setting('sftp_pwd');
        $remoteDirectory = $this->setting('sftp_dir');
        $uploadUrl = $this->setting('upload_url');

        $pending_list = $this->db->query("SELECT * FROM b2b_doc.rms_filename WHERE `doc_status` = 'COMPLETE'")->result_array();

        foreach ($pending_list as $list){

            $file_guid = $list['filename_guid'];
            $file = $directory.'/'.$list['filename'];
            $content = file_get_contents($file);
            
            $rows = explode("\n", $content);
            $headers = explode("|", $rows[0]);

            $sortedData = [];

            for ($i = 1; $i < count($rows); $i++) {
                $columns = explode("|", $rows[$i]);

                if(count($headers) == count($columns)){

                    $cleanedHeaders = array_map(function ($item) {
                        return preg_replace('/\s+/', '', trim($item));
                    }, $headers);

                    $rowData = array_combine($cleanedHeaders, $columns);
                    $sortedData[] = $rowData;
                }
                
            }

            usort($sortedData, function ($a, $b) {
                return strcmp($a['INVOICENUMBER'], $b['INVOICENUMBER']);
            });

            foreach($sortedData as $row){

                if(isset($row['VENDORCODE']) && isset($row['INVOICEDATE']) && isset($row['INVOICENUMBER']) && isset($row['INVOICETYPE']) && isset($row['AMOUNT']) && isset($row['INVOICEFILENAME'])){
                
                    $refno = $row['INVOICENUMBER'];
                    $filename = $row['INVOICEFILENAME'];
                    $inv_file_name = $row['INVOICEFILENAME'];
                    $inv_file_name = str_replace('.pdf', '', $inv_file_name);

                    $inv_directory = $this->setting('inv_location');
                    $inv_files = scandir($inv_directory);

                    $inv_files = array_map(function ($value) {
                        return str_replace('.pdf', '', $value);
                    }, $inv_files);

                    $inv_files = array_map(function ($value) {
                        return preg_replace('/\s+/', '', trim($value));
                    }, $inv_files);

                    if(in_array(trim($inv_file_name), $inv_files)){
    
                        $localFile = $this->setting('inv_location').'/'.$filename;
                        $log_guid = $this->db->query("SELECT REPLACE(UPPER(UUID()),'-','') AS uuid")->row('uuid');

                        $log_data = array(
                            'log_guid'          => $log_guid,
                            'doc_refno'         => $refno,
                            'file_name'         => $inv_file_name,
                            'method'            => 'ftp_put',
                            'from'              => $_SERVER['SERVER_NAME'],
                            'to'                => $remoteHost,
                            'datetime_start'    => $this->db->query("SELECT NOW() as current_datetime")->row('current_datetime'),
                        );
                
                        $this->db->insert('b2b_doc.doc_movement_log', $log_data);

                        $curl = curl_init();

                        $headers = array(
                            "0" => "Original-Filename: ".$inv_file_name, 
                            "1" => "Content-Type: application/pdf"
                        );
                        
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $uploadUrl."?filename=".trim($inv_file_name),
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_VERBOSE => true,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            // CURLOPT_CUSTOMREQUEST => 'POST',
                            // // CURLOPT_POSTFIELDS => array('file'=> new CURLFILE($localFile)),
                            CURLOPT_CUSTOMREQUEST => 'PUT',
                            CURLOPT_POSTFIELDS => file_get_contents(trim($localFile)),
                            // CURLOPT_HTTPHEADER => $headers
                            CURLOPT_HTTPHEADER => ["Content-Type: application/pdf"]
                        ));

                        $result = curl_exec($curl);

                        curl_close($curl);

                        $response = json_decode($result, true);

                        if ($response['status'] == false || $response == false) {
                            
                            $response = array(
                                'status'    => false,
                                'message'   => 'Failed to upload file to FTP server',
                            );

                            $log_data = array(
                                'response'      => json_encode($response),
                                'datetime_end'  => $this->db->query("SELECT NOW() as current_datetime")->row('current_datetime'),
                                'status'        => 0,
                            );
                    
                            $this->db->where('log_guid', $log_guid);
                            $this->db->update('b2b_doc.doc_movement_log', $log_data);

                        } else {

                            $response = array(
                                'status'    => true,
                                'message'   => 'Success upload file to FTP server',
                            );
    
                            $log_data = array(
                                'response'      => json_encode($response),
                                'datetime_end'  => $this->db->query("SELECT NOW() as current_datetime")->row('current_datetime'),
                                'status'        => 1,
                            );
                    
                            $this->db->where('log_guid', $log_guid);
                            $this->db->update('b2b_doc.doc_movement_log', $log_data);
    
                            $this->db->query("UPDATE b2b_doc.other_doc SET `doc_uploaded` = '1', doc_uploaded_at = NOW() WHERE refno = '$refno' AND doc_uploaded = '0' LIMIT 1");

                        }
 
                    }
                }
                
            }

        }

        $this->response(
            [
                'status' => true,
                'message' => sizeof($pending_list) == 0 ? 'No document to be uploaded' : 'Success upload document',
            ]
        );
    }

}

