<?php

require(APPPATH.'/libraries/REST_Controller.php');

class File_checking_autocount extends REST_Controller{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");

        $check_table_existed = $this->db->query("SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = 'rest_api' AND table_name = 'b2b_config'");
        if($check_table_existed->row('table_count') <= 0)
        {
            $response = array(
                        'status' => "false",
                        'message' => "Database or Table not setup"
                    );

            echo json_encode($response);die;
        }

        $b2b_ip_https = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'HTTP' AND isactive = 1");
        if($b2b_ip_https->num_rows() <= 0)
        {
            $this->b2b_ip_https = '';
            $response = array(
                        'status' => "false",
                        'message' => "Protocol not setup"
                    );

            echo json_encode($response);die;
        }
        else
        {
            $this->b2b_ip_https = $b2b_ip_https->row('value');
        }

        $b2b_public_ip = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'IP' AND isactive = 1");
        if($b2b_public_ip->num_rows() <= 0)
        {
            $this->b2b_public_ip = '';
            $response = array(
                        'status' => "false",
                        'message' => "IP not setup"
                    );

            echo json_encode($response);die;
        }
        else
        {
            $this->b2b_public_ip = $b2b_public_ip->row('value');
        }

        $b2b_ip_port = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'PORT' AND isactive = 1");
        if($b2b_ip_port->num_rows() <= 0)
        {
            $this->b2b_ip_port = '';
            $response = array(
                        'status' => "false",
                        'message' => "Port not setup"
                    );

            echo json_encode($response);die;
        }
        else
        {
            $this->b2b_ip_port = $b2b_ip_port->row('value');
        }
        
        $this->b2b_ip = $this->b2b_ip_https.$this->b2b_public_ip.$this->b2b_ip_port;         
        // $this->b2b_ip = 'http://127.0.0.1';
    } 


    public function move_new_file_get()
    {
            header('Content-Type: text/html; charset=utf-8');
            $inactive_autocount_task = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'inactive_autocount_task' AND isactive = 1")->row('value');
            
            if($inactive_autocount_task == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'inactive_autocount_task inactive'
                    ]
                ); 
            }

            if($inactive_autocount_task == '1')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'task inactive'
                    ]
                ); 
            }
            $date_time_now = $this->db->query("SELECT NOW() as date_time_now")->row('date_time_now');

            $run_time = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'run_time' AND isactive = 1")->row('value');
            if($run_time == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time inactive'
                    ]
                ); 
            }

            $run_time_type = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'run_time_type' AND isactive = 1")->row('value');            
            if($run_time_type == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time_type inactive'
                    ]
                ); 
            }

            $run_time_length = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'run_time_length' AND isactive = 1")->row('value');
            if($run_time_length == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time_length inactive'
                    ]
                ); 
            }     

            $from_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'from_location' AND isactive = 1")->row('value');    
            if($from_location == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'from_location inactive'
                    ]
                ); 
            }

            $to_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'to_location' AND isactive = 1")->row('value');
            if($to_location == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'to_location inactive'
                    ]
                ); 
            }

            $file_format = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'file_format' AND isactive = 1")->row('value');     
            if($file_format == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'file_format inactive'
                    ]
                ); 
            }

            $time_format_column = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'time_format_column' AND isactive = 1")->row('value');            
            if($time_format_column == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'time_format_column inactive'
                    ]
                ); 
            }

            $time_format = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'time_format' AND isactive = 1")->row('value');   
            if($time_format == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'time_format inactive'
                    ]
                ); 
            }

            $document_running_time = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'document_running_time' AND isactive = 1")->row('value');           
            if($document_running_time == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'document_running_time inactive'
                    ]
                ); 
            }

            $move_file_after_send = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'move_file_after_send' AND isactive = 1")->row('value');            
            if($move_file_after_send == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'move_file_after_send inactive'
                    ]
                ); 
            }

            $supplier_name_get = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'supplier_name_get' AND isactive = 1")->row('value');

            if($supplier_name_get == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'supplier_name_get inactive'
                    ]
                ); 
            }

            if($date_time_now < $run_time)
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'next run time not reach'
                    ]
                ); 
            }

            $skip_folder = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'skip_folder'");
            if($skip_folder->num_rows() <= 0)
            {
                $data_skip_folder = array(
                    'module' => 'autocount',
                    'type' => 'skip_folder',
                    'value' => '',
                    'isactive' => 1,
                );
                $this->db->insert('b2b_doc.b2b_setting_parameter',$data_skip_folder);
            }           
            // print_r($skip_folder);die;
            $skip_folder_result = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'skip_folder'");
            if($skip_folder_result->row('value') == '' || $skip_folder_result->row('value') == null)
            {
                $data_skip_folder_string = '';
                $data_skip_folder_string_array = array();
            }  
            else
            {
                $data_skip_folder_string = '';
                $skip_folder_array = $skip_folder_result->row('value');
                // echo $skip_folder_array;
                $data_skip_folder_string_array = explode(",",$skip_folder_array);
                // print_r($data_skip_folder_string_array);die;
                // foreach ($data_skip_folder_string_array as $row) {
                //     $data_skip_folder_string .= $row;
                // }
            }
            // echo $data_skip_folder_string;die;            
            // die;

            $supp_column = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'supp_column' AND isactive = 1")->row('value');            
            if($supp_column == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'supp_column inactive'
                    ]
                ); 
            }       
            $supp_count = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'supp_count' AND isactive = 1")->row('value');            
            if($supp_count == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'supp_column inactive'
                    ]
                ); 
            }

            $supp_concat = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'supp_concat' AND isactive = 1")->row('value');

            if($supp_concat == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'supp_concat inactive'
                    ]
                ); 
            }

            $create_folder_month = '';
                     
            $path = scandir($from_location,1);
            // print_r($path);die;
            $cut_file_format = explode('_',$file_format);
            $insert_column = '';
            $insert_column_count = 1;
            foreach($cut_file_format as $column)
            {
                $insert_column .= $column.',';
                $insert_column_count++;
            }
            $insert_column2 = rtrim($insert_column,',');
            // print_r($insert_column2);die;
            // foreach($path as $row)
            // {
            //     echo $row.'<br>';
            // }
            // die;
            $document_running_count = 0;
            $supplier_concat = '0';            
            foreach($path as $row)
            {
                $filename = $row;
                // if($row != '.' && $row != '..' && $row != '????')
                if(!in_array($row,$data_skip_folder_string_array))
                {       
                    // echo $row.'<br>';
                    $bare_name = pathinfo($row, PATHINFO_FILENAME);
                    $bare_extension = pathinfo($row, PATHINFO_EXTENSION);
                    $cut = explode('_',$bare_name);
                    $insert_value = '';
                    $insert_value_count = 1;
                    $supplier_name = '';
                    // echo $bare_extension.'<br>';
                    if($bare_extension == 'pdf')
                    {

                        foreach($cut as $row2)
                        {
                            // echo $insert_value_count.$time_format_column;
                            if($insert_value_count == $supp_column)
                            {
                                // $supplier_name = $this->db->query("SELECT name as supplier_name FROM backend.supcus WHERE type = 'S' AND code = '$row2' LIMIT 1")->row('supplier_name');
                                // echo $row.'-'.$row2.'<br>';die;
                                if($row2 == 'SIN')
                                {
                                    $supplier_concat = '1';
                                }
                                else
                                {
                                    $supplier_concat = '0';
                                }
                                if($supp_concat == '0')
                                {
                                    // echo 1;die;
                                    $supplier_concat = '0';
                                }
                                // echo $supplier_concat;die;
                            }                            
                            if($insert_value_count == $time_format_column)
                            {
                                // echo $row2.'<br>';
                                $row2 = $this->db->query("SELECT STR_TO_DATE('$row2','$time_format') as xtime")->row('xtime');
                                // echo $this->db->last_query().'<br>';
                                $create_folder_month = $this->db->query("SELECT DATE_FORMAT('$row2','%Y-%m') as create_folder_month")->row('create_folder_month');
                                // echo '1'.$create_folder_month.$this->db->last_query();

                                // echo $row.'-'.$row2.'<br>';
                            }
                            if($insert_value_count == $supplier_name_get)
                            {
                                if($supplier_concat == '1')
                                {
                                    $supplier_name = $this->db->query("SELECT name as supplier_name FROM backend.supcus WHERE type <> 'C' AND code = SUBSTRING('$row2', $supp_count) LIMIT 1")->row('supplier_name');
                                }
                                else
                                {
                                    $supplier_name = $this->db->query("SELECT name as supplier_name FROM backend.supcus WHERE type <> 'C' AND code = '$row2' LIMIT 1")->row('supplier_name');
                                }
                                // $supplier_name = $this->db->query("SELECT name as supplier_name FROM backend.supcus WHERE type = 'S' AND code = '$row2' LIMIT 1")->row('supplier_name');
                                // echo $row.'-'.$row2.'<br>';
                                // echo $supplier_concat.$this->db->last_query();die;
                            }
                            $insert_value .= "'".$row2."',";
                            $insert_value_count++;
                        }
                        // die;
                        $insert_value .= "'".addslashes($supplier_name)."',";
                        $insert_value2 = rtrim($insert_value,',');
                        // echo $insert_column2.'<br>';
                        // echo $insert_value2.$filename.'<br>';
                    }
                    // die;
                    // echo $insert_column_count.$insert_value_count.'<br>';
                    // die;
                    if($insert_column_count == $insert_value_count)
                    {
                        $this->db->query("REPLACE INTO b2b_doc.other_doc ($insert_column2,supname,created_by,created_at) VALUES(".$insert_value2.",'URL_TASK',NOW()) ");
                        // echo $this->db->last_query();die;

                        $affected_rows = $this->db->affected_rows();

                        if($affected_rows > 0)
                        {
                            $file_path_checking = $to_location.'/'.$create_folder_month;
                            // echo $file_path_checking.'<br>';
                            if(!file_exists($file_path_checking))
                            {
                                mkdir($file_path_checking, 0777);
                                chmod($file_path_checking, 0777);
                            }
                            if($move_file_after_send == '1')
                            {
                                // echo 1;die;
                                rename($from_location.'/'.$filename, $file_path_checking.'/'.$filename);
                //chmod($file_path_checking.'/'.$filename, 0777);
                            }
                            else
                            {
                                copy($from_location.'/'.$filename, $file_path_checking.'/'.$filename);
                            }
                        }
                    }//close foreach insert data and move file

                }
                $document_running_count++;
                if($document_running_count == $document_running_time)
                {
                    break;
                }
            }
            $next_run_time = $this->db->query("SELECT DATE_ADD(NOW(), INTERVAL $run_time_length $run_time_type) as next_run_time")->row('next_run_time');
            $this->db->query("UPDATE b2b_doc.b2b_setting_parameter SET value = '$next_run_time'  WHERE module = 'autocount' AND type = 'run_time'");
            // echo $this->db->last_query();die;

            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'Success'
                ]
            ); 
    }      
     
    public function other_doc_rest_get()
    {
        $task_active = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount_rest' AND type = 'active_autocount_rest_task' AND isactive = 1")->row('value');
        $autocount_doc_start_date = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount_rest' AND type = 'autocount_doc_start_date' AND isactive = 1")->row('value');

        if($task_active == '')
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'active_autocount_rest_task inactive'
                ]
            ); 
        }
        if($autocount_doc_start_date == '')
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'autocount_doc_start_date inactive'
                ]
            ); 
        }

        if($task_active == '0')
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'task inactive'
                ]
            ); 
        }

        $check_sup_name_empty = $this->db->query("SELECT a.refno FROM b2b_doc.other_doc a INNER JOIN b2b_doc.other_doc_mapping b ON a.refno = b.file_refno INNER JOIN backend.supcus c ON b.cross_supcode = c.code AND c.type<>'C' WHERE a.supname = ''");
        if($check_sup_name_empty->num_rows() > 0)
        {
            $this->db->query("UPDATE b2b_doc.other_doc a INNER JOIN b2b_doc.other_doc_mapping b ON a.refno = b.file_refno INNER JOIN backend.supcus c ON b.cross_supcode = c.code AND c.type<>'C' SET a.supname = c.name,a.hq_update = 0 WHERE a.supname = ''");
        }

        $check_sup_name = $this->db->query("SELECT c.code,c.name,c.accpdebit,b.cross_supcode,b.cross_refno,a.* FROM b2b_doc.`other_doc` a INNER JOIN b2b_doc.`other_doc_mapping` b ON a.refno = b.file_refno AND a.supcode = b.file_supcode INNER JOIN backend.supcus c ON b.cross_supcode = c.accpdebit AND c.type<>'C' WHERE a.supname = ''");

        if($check_sup_name->num_rows() > 0)
        {
            $this->db->query("UPDATE b2b_doc.`other_doc` a INNER JOIN b2b_doc.`other_doc_mapping` b ON a.refno = b.file_refno AND a.supcode = b.file_supcode INNER JOIN backend.supcus c ON b.cross_supcode = c.accpdebit AND c.type<>'C' SET a.supname = c.name,a.hq_update = 0 WHERE a.supname = ''");
        }

        $data = $this->db->query("SELECT '' AS status, b.`cross_refno` AS refno, b.`cross_supcode` AS supcode, a.supname, a.doctype, a.doctime, a.hq_update, a.uploaded, a.uploaded_at, a.created_by, a.created_at, (SELECT customer_guid FROM rest_api.run_once_config WHERE active = 1 LIMIT 1) AS customer_guid FROM b2b_doc.other_doc a INNER JOIN b2b_doc.`other_doc_mapping` b ON a.refno = b.`file_refno` AND a.supcode = b.file_supcode WHERE a.hq_update = 0 AND a.doc_uploaded = '1' AND a.doctime >= '$autocount_doc_start_date' LIMIT 150");        
        // echo $this->db->last_query();die;
        if($data->num_rows() > 0)
        {

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/other_doc';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/other_doc';
            // echo $url;die;
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            // $status = "true";

            // foreach($data->result() as $row)
            // {
            //     $this->db->query("UPDATE b2b_doc.other_doc a INNER JOIN b2b_doc.other_doc_mapping b ON a.refno = b.file_refno SET a.hq_update = 1,a.uploaded = 1,a.uploaded_at = NOW() WHERE b.cross_refno = '$row->refno' AND a.doctype = '$row->doctype'");
            // }
            if($status == "true")
            {
                    foreach($data->result() as $row)
                    {
                        $this->db->query("UPDATE b2b_doc.other_doc a INNER JOIN b2b_doc.other_doc_mapping b ON a.refno = b.file_refno SET a.hq_update = 1,a.uploaded = 1,a.uploaded_at = NOW() WHERE b.cross_refno = '$row->refno' AND a.doctype = '$row->doctype'");
                    }                
                    $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'Success'
                    ]
                    // $this->Main_model->query_call('Api','login_validation_get', $data)
                );  
            }
            else
            {
                    $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'Unsuccess'
                    ]
                    // $this->Main_model->query_call('Api','login_validation_get', $data)
                );  
            }
        }
        else
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'No data found'
                ]
                    // $this->Main_model->query_call('Api','login_validation_get', $data)
            ); 
        }

    }   

    public function test_move_file()
    {
        $file_path_checking = '/media/b2b_shared/upload'.'/'.'2020-10';
        if(!file_exists($file_path_checking))
        {
            mkdir($file_path_checking, 0777);
            chmod($file_path_checking, 0777);
        }die;

        // rename($from_location.'/'.$filename, $file_path_checking.'/'.$filename);
        copy('/media/b2b_shared/upload/SIN_011020000000_DG006_IN2010016.pdf', '/media/b2b_shared/upload/2020-10/SIN_011020000000_DG006_IN2010016.pdf');
    }

    public function move_new_file_json_get()
    {
            header('Content-Type: text/html; charset=utf-8');
            $inactive_autocount_task = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'inactive_autocount_task' AND isactive = 1")->row('value');
            
            if($inactive_autocount_task == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'inactive_autocount_task inactive'
                    ]
                ); 
            }

            if($inactive_autocount_task == '1')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'task inactive'
                    ]
                ); 
            }
            $date_time_now = $this->db->query("SELECT NOW() as date_time_now")->row('date_time_now');

            $run_time = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'run_time' AND isactive = 1")->row('value');
            if($run_time == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time inactive'
                    ]
                ); 
            }

            $run_time_type = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'run_time_type' AND isactive = 1")->row('value');            
            if($run_time_type == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time_type inactive'
                    ]
                ); 
            }

            $run_time_length = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'run_time_length' AND isactive = 1")->row('value');
            if($run_time_length == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time_length inactive'
                    ]
                ); 
            }     

            $from_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'mapping_from_location' AND isactive = 1")->row('value');    
            if($from_location == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'from_location inactive'
                    ]
                ); 
            }

            $to_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'mapping_to_location' AND isactive = 1")->row('value');
            if($to_location == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'to_location inactive'
                    ]
                ); 
            }


            $document_running_time = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'document_running_time' AND isactive = 1")->row('value');           
            if($document_running_time == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'document_running_time inactive'
                    ]
                ); 
            }

            $move_file_after_send = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'move_file_after_send' AND isactive = 1")->row('value');            
            if($move_file_after_send == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'move_file_after_send inactive'
                    ]
                ); 
            }

            if($date_time_now < $run_time)
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'next run time not reach'
                    ]
                ); 
            }
            // die;
            $create_folder_month = '';
                     
            $path = scandir($from_location,1);
            // print_r($path);die;

            $document_running_count = 0;
            foreach($path as $row)
            {
                $filename = $row;
                if($row != '.' && $row != '..' && $row != '????')
                {       
                    // echo $row.'<br>';
                    $bare_name = pathinfo($row, PATHINFO_FILENAME);
                    $bare_extension = pathinfo($row, PATHINFO_EXTENSION);
                    $cut = explode('_',$bare_name);
                    $insert_value = '';
                    $insert_value_count = 1;
                    $supplier_name = '';
                    // echo $bare_extension.'<br>';
                    if($bare_extension == 'txt')
                    {                      
                        foreach($cut as $row2)
                        {
                            // echo $insert_value_count.$time_format_column;
                            // echo $insert_value_count;
                            if($insert_value_count == 2)
                            {
                                // echo $row2.'<br>';
                                $row2 = $this->db->query("SELECT STR_TO_DATE('$row2','%Y%m%d') as xtime")->row('xtime');
                                // echo $this->db->last_query().'<br>';die;
                                $create_folder_month = $this->db->query("SELECT DATE_FORMAT('$row2','%Y-%m') as create_folder_month")->row('create_folder_month');
                                // echo '1'.$create_folder_month.$this->db->last_query();

                                // echo $row.'-'.$row2.'<br>';
                            }                      

                            $insert_value .= "'".$row2."',";
                            $insert_value_count++;
                        }
                        $myfile = file_get_contents($from_location.'/'.$row, "r") or die("Unable to open file!");
                        $output = json_decode($myfile);   
                        // echo $myfile.'<br>';
                        // echo '1'.'<br>';
                        // print_r($output);
                        foreach($output AS $row3)
                        {   
                            $DocType = $row3->DocType;
                            $AutoCountDocNo = $row3->AutoCountDocNo;
                            $FileDocNo = $row3->FileDocNo;
                            $AutoCountAccNo = $row3->AutoCountAccNo;
                            $FileAccNo = $row3->FileAccNo;
                            $FtpFileName = $row3->FtpFileName;
                            $this->db->query("REPLACE INTO b2b_doc.other_doc_mapping (doctype,cross_refno,file_refno,cross_supcode,file_supcode,filename,created_at,created_by,updated_at,updated_by) VALUES('$DocType','$AutoCountDocNo','$FileDocNo','$AutoCountAccNo','$FileAccNo','$FtpFileName',NOW(),'task_agent','1001-01-01 00:00:00','task_agent') ");
                        }//close foreach loop through row column                           

                        $affected_rows = $this->db->affected_rows();

                        if($affected_rows > 0)
                        {
                            $file_path_checking = $to_location.'/'.$create_folder_month;
                            // echo $file_path_checking.'<br>';
                            if(!file_exists($file_path_checking))
                            {
                                mkdir($file_path_checking, 0777);
                                chmod($file_path_checking, 0777);
                            }
                            if($move_file_after_send == '1')
                            {
                                // echo 1;die;
                                rename($from_location.'/'.$filename, $file_path_checking.'/'.$filename);
                //chmod($file_path_checking.'/'.$filename, 0777);
                            }
                            else
                            {
                                copy($from_location.'/'.$filename, $file_path_checking.'/'.$filename);
                            }
                        }
                    }

                }
                $document_running_count++;
                if($document_running_count == $document_running_time)
                {
                    break;
                }
            }
            $next_run_time = $this->db->query("SELECT DATE_ADD(NOW(), INTERVAL $run_time_length $run_time_type) as next_run_time")->row('next_run_time');
            $this->db->query("UPDATE b2b_doc.b2b_setting_parameter SET value = '$next_run_time'  WHERE module = 'autocount' AND type = 'run_time'");
            // echo $this->db->last_query();die;

            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'Success'
                ]
            ); 
    }   

    public function reflow_doc_mapping_get()
    {
        $get_doc = $this->db->query("SELECT a.* FROM b2b_doc.other_doc a LEFT JOIN b2b_doc.other_doc_mapping b ON a.refno = b.cross_refno WHERE a.uploaded = '0' AND a.supname <> '' AND b.cross_refno IS NULL AND DATE(a.created_at) != CURDATE() GROUP BY a.refno ");

        if($get_doc->num_rows() > 0)
        {
            foreach($get_doc->result() as $row)
            {
                $DocType = $row->doctype;
                $other_doc_refno = $row->refno;
                $other_doc_supcode = $row->supcode;
                $datetime = $this->db->query("SELECT DATE_FORMAT(NOW(),'%d%m%y000000') AS `datetime`")->row('datetime');
                $FtpFileName = $DocType.'_'.$datetime.'_'.$other_doc_supcode.'_'.$other_doc_refno;

                $insert_data = $this->db->query("REPLACE INTO b2b_doc.other_doc_mapping (doctype,cross_refno,file_refno,cross_supcode,file_supcode,filename,created_at,created_by,updated_at,updated_by) VALUES('$DocType','$other_doc_refno','$other_doc_refno','$other_doc_supcode','$other_doc_supcode','$FtpFileName',NOW(),'reflow_agent','1001-01-01 00:00:00','reflow_agent') ");
            }
        }
        else
        {
            $message = 'No Data';
        }

        $affected_rows = $this->db->affected_rows();

        if($affected_rows > 0)
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'Success'
                ]
            ); 
        }
        else
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => $message
                ]
            ); 
        }
    }

    public function azure_blob_setting($type)
    {
        $result = $this->db->query("SELECT `value` FROM b2b_doc.b2b_setting_parameter WHERE `module` = 'azure_blob_storage' AND isactive = '1' AND `type` = '$type' LIMIT 1")->row('value');

        return rtrim($result, '/');
    }

    public function other_doc_pdf_get()
    {
        ini_set('memory_limit','-1');
        // ini_set('display_errors', 1);
        // error_reporting(E_ALL);

        $check_doc_uploaded_at = $this->db->query("SELECT COUNT(*) AS result FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'b2b_doc' AND TABLE_NAME = 'other_doc' AND COLUMN_NAME = 'doc_uploaded_at'")->row('result');
        $check_doc_uploaded = $this->db->query("SELECT COUNT(*) AS result FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'b2b_doc' AND TABLE_NAME = 'other_doc' AND COLUMN_NAME = 'doc_uploaded'")->row('result');
        $check_log_table = $this->db->query("SELECT COUNT(*) AS result FROM information_schema.tables WHERE table_schema = 'b2b_doc' AND table_name = 'doc_movement_log'")->row('result');

        if($check_doc_uploaded_at == '0'){
            $this->db->query("ALTER TABLE b2b_doc.`other_doc` 
            ADD COLUMN `doc_uploaded_at` datetime DEFAULT NULL;");
        }

        if($check_doc_uploaded == '0'){
            $this->db->query("ALTER TABLE b2b_doc.`other_doc` 
            ADD COLUMN `doc_uploaded` smallint(6) DEFAULT '0';");
        }

        if($check_log_table == '0'){

        	$this->db->query("CREATE TABLE b2b_doc.`doc_movement_log` (
                `log_guid` varchar(32) NOT NULL,
                `doc_refno` varchar(100) DEFAULT NULL,
                `file_name` varchar(255) DEFAULT NULL,
                `method` varchar(50) DEFAULT NULL,
                `from` varchar(200) DEFAULT NULL,
                `to` varchar(200) DEFAULT NULL,
                `post_data` text,
                `response` text,
                `curl_info` text,
                `datetime_start` timestamp NULL DEFAULT NULL,
                `datetime_end` timestamp NULL DEFAULT NULL,
                `status` tinyint(5) DEFAULT '0',
                PRIMARY KEY (`log_guid`),
                KEY `module` (`method`),
                KEY `status` (`status`)
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1
            ");

        }

        $pending_list = $this->db->query("SELECT * FROM b2b_doc.other_doc WHERE `doc_uploaded` = '0' AND doctype NOT LIKE '%RMS%' LIMIT 40")->result_array();

        if(sizeof($pending_list) == 0){

            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'No data found'
                ]

            ); die;
        }

        $blob_link = $this->azure_blob_setting('blob_link') != '' ? $this->azure_blob_setting('blob_link') : 'https://api3.xbridge.my/';
        $blob_username = $this->azure_blob_setting('blob_username') != '' ? $this->azure_blob_setting('blob_username') : 'panda';
        $blob_password = $this->azure_blob_setting('blob_password') != '' ? $this->azure_blob_setting('blob_password') : '&_)GZh9Kd?D6gHRu';
        $local_doc_path = $this->azure_blob_setting('local_doc_path') != '' ? $this->azure_blob_setting('local_doc_path') : '/home/autocount/SENT/';
        $mount_root_folder = $this->azure_blob_setting('mount_root_folder') != '' ? $this->azure_blob_setting('mount_root_folder') : 'panda';

        // print_r(sizeof($pending_list)); die;

	    $success_upload = 0;

        foreach ($pending_list as $list){

            $log_guid = $this->db->query("SELECT REPLACE(UPPER(UUID()),'-','') AS uuid")->row('uuid');
            $doc_type = $list['doctype'];
            $supcode = $list['supcode'];
            $doc_refno = $list['refno'];
            $doc_time = $list['doctime'];
            $formatted_doctime = date("dmyHis", strtotime($doc_time));
            $doc_period = date("Y-m", strtotime($doc_time));

            $file_name = $doc_type.'_'.$formatted_doctime.'_'.$supcode.'_'.$doc_refno;
            $file_path = $local_doc_path.'/'.$doc_period;

            $log_data = array(
                'log_guid'          => $log_guid,
                'doc_refno'         => $doc_refno,
                'file_name'         => $file_name,
                'method'            => 'azure_blob',
                'from'              => $_SERVER['SERVER_NAME'],
                'to'                => $blob_link,
                'datetime_start'    => $this->db->query("SELECT NOW() as current_datetime")->row('current_datetime'),
            );
    
            $this->db->insert('b2b_doc.doc_movement_log', $log_data);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $blob_link.'/api/token/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('username' => $blob_username,'password' => $blob_password),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            
            $result = json_decode($response, true);
            $token = isset($result['access']) ? $result['access'] : '';

            if($token != ''){

		// echo $file_path.'/'.$file_name.'.pdf'; die;

                $post_data = array(
                    'file'                  => new CURLFILE($file_path.'/'.$file_name.'.pdf', 'application/pdf', $file_name.'.pdf'),
                    'action'                => 'upload',
                    'azure_directory_path'  => 'HQ/Accounting/'.$supcode.'/'.$doc_type.'/',
                    'doc_type'              => 'STRB',
                    'azure_container_name'  => $mount_root_folder,
                    'filename'              => $file_name,
                    'blob_path'             => ''
                );

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $blob_link.'/azure/upload/',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $post_data,
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer '.$token
                    ),
                ));

                $response = curl_exec($curl);
                $info = curl_getinfo($curl);

                curl_close($curl);
                
                $result = json_decode($response, true);

                $status = isset($result['status']) ? $result['status'] : 'false';
                $message = isset($result['message']) ? $result['message'] : '';

                if($status == 'true' || $message == 'Status Code: 409 The specified blob already exists'){
                    $this->db->query("UPDATE b2b_doc.other_doc SET `doc_uploaded` = '1', doc_uploaded_at = NOW() WHERE refno = '$doc_refno' AND supcode = '$supcode' AND doctype = '$doc_type' AND doc_uploaded = '0' LIMIT 1");
                }else{
		    $this->db->query("UPDATE b2b_doc.other_doc SET `doc_uploaded` = '99', doc_uploaded_at = NOW() WHERE refno = '$doc_refno' AND supcode = '$supcode' AND doctype = '$doc_type' AND doc_uploaded = '0' LIMIT 1");
		}

                $log_data = array(
                    'post_data'     => json_encode($post_data),
                    'response'      => $response,
                    'curl_info'     => json_encode($info),
                    'datetime_end'  => $this->db->query("SELECT NOW() as current_datetime")->row('current_datetime'),
                    'status'        => $status == 'true' ? 1 : 0,
                );
        
                $this->db->where('log_guid', $log_guid);
                $this->db->update('b2b_doc.doc_movement_log', $log_data);

                $success_upload++;
            
            }else{
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'Invalid token'
                    ]
    
                ); die;
            }

        }

	    if(sizeof($pending_list) == 0){
            $this->response(
                [
                    'status' => true,
                    'message' => 'No file to be uploaded'
                ]

            ); die;
        }

        if(sizeof($pending_list) != 0 && $success_upload == 0){
            $this->response(
                [
                    'status' => false,
                    'message' => 'Fail to upload file'
                ]

            ); die;
        }

        if(sizeof($pending_list) == $success_upload){
            $this->response(
                [
                    'status' => true,
                    'message' => 'Successfully upload file'
                ]

            ); die;
        }

        if(sizeof($pending_list) != $success_upload){
            $this->response(
                [
                    'status' => true,
                    'message' => 'Success upload '.$success_upload.' file'
                ]

            ); die;
        }
    }

    public function get_fail_upload_get()
    {   
        $todate = isset($_REQUEST['current_date']) ? $_REQUEST['current_date'] : date('Y-m-d');
        $yesterdate = date('Y-m-d', strtotime($todate . ' -1 day'));

	    $other_doc_start_date = isset($_REQUEST['other_doc_start_date']) ? $_REQUEST['other_doc_start_date'] : '2023-01-01';

        $yesterday_total_doc = $this->db->query("SELECT COUNT(*) AS total_cnt FROM b2b_doc.other_doc WHERE doctype NOT LIKE '%RMS%' AND created_at BETWEEN '$yesterdate 09:00:00' AND '$todate 09:00:00'")->row('total_cnt');
        $yesterday_total_fail = $this->db->query("SELECT COUNT(*) AS total_cnt FROM b2b_doc.other_doc WHERE `doc_uploaded` = '99' AND doctype NOT LIKE '%RMS%' AND created_at BETWEEN '$yesterdate 09:00:00' AND '$todate 09:00:00'")->row('total_cnt');
        $all_time_fail = $this->db->query("SELECT COUNT(*) AS total_cnt FROM b2b_doc.other_doc WHERE `doc_uploaded` = '99' AND doctype NOT LIKE '%RMS%' and date(created_at) between '$other_doc_start_date' and curdate() ")->row('total_cnt');

        $data = array(
            'yesterday_total_doc'   => $yesterday_total_doc,
            'yesterday_total_fail'  => $yesterday_total_fail,
            'all_time_fail'         => $all_time_fail,
        );

        echo json_encode($data);
    }
}

