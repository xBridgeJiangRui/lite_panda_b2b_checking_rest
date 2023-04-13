<?php

require(APPPATH.'/libraries/REST_Controller.php');

class File_checking extends REST_Controller{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
    } 


    public function move_new_file_get()
    {
            header('Content-Type: text/html; charset=utf-8');
            $inactive_navition_task = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'inactive_navition_task' AND isactive = 1")->row('value');
            
            if($inactive_navition_task == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'inactive_navition_task inactive'
                    ]
                ); 
            }

            if($inactive_navition_task == '1')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'task inactive'
                    ]
                ); 
            }
            $date_time_now = $this->db->query("SELECT NOW() as date_time_now")->row('date_time_now');
            $from_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'from_location' AND isactive = 1")->row('value');
            $to_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'to_location' AND isactive = 1")->row('value');
            $file_format = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'file_format' AND isactive = 1")->row('value');
            $time_format_column = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'time_format_column' AND isactive = 1")->row('value');
            $time_format = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'time_format' AND isactive = 1")->row('value');
            $document_running_time = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'document_running_time' AND isactive = 1")->row('value');
            $move_file_after_send = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'move_file_after_send' AND isactive = 1")->row('value');
            $create_folder_month = '';
            $supplier_name_get = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'supplier_name_get' AND isactive = 1")->row('value');
            $run_time = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'run_time' AND isactive = 1")->row('value');
            $run_time_type = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'run_time_type' AND isactive = 1")->row('value');
            $run_time_length = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'run_time_length' AND isactive = 1")->row('value');
            //mkdir($to_location, 0777);die;
        //chmod($to_location, 0777);die;
            //array_map('unlink', glob("$to_location/2020-03/*.*"));die;
            //rmdir($to_location.'/2020-03');die;
        //rmdir($to_location.'');die;
            if($run_time == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time inactive'
                    ]
                ); 
            }
            if($run_time_type == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time_type inactive'
                    ]
                ); 
            }
            if($run_time_length == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'run_time_length inactive'
                    ]
                ); 
            }            
            if($from_location == '')
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'from_location inactive'
                    ]
                ); 
            }
            if($to_location == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'to_location inactive'
                    ]
                ); 
            }
            if($file_format == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'file_format inactive'
                    ]
                ); 
            }
            if($time_format_column == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'time_format_column inactive'
                    ]
                ); 
            }
            if($time_format == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'time_fromat inactive'
                    ]
                ); 
            }
            if($document_running_time == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'document_running_time inactive'
                    ]
                ); 
            }
            if($move_file_after_send == '')
            {
                $this->response(
                    [
                        'status' => FALSE,
                        'message' => 'move_file_after_send inactive'
                    ]
                ); 
            }
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
            // foreach($path as $row)
            // {
            //     echo $row.'<br>';
            // }
            // die;
            $document_running_count = 0;
            foreach($path as $row)
            {
                $filename = $row;
                // echo $filename;die;
                if($row != '.' && $row != '..' && $row != '????' && $row != 'lost+found' && $row != 'b2b_shared_20200603.zip' && $row != 'b2b_shared_20200603' && $row != 'acc_doc.zip' && $row != 'acc_doc' && $row != 'SENT')
                {      
                    // echo $filename;die; 
                    // echo $row.'<br>';
                    $bare_name = pathinfo($row, PATHINFO_FILENAME);
                    $cut = explode('_',$bare_name);
                    $insert_value = '';
                    $insert_value_count = 1;
                    $supplier_name = '';
                    foreach($cut as $row2)
                    {
                        if($insert_value_count == $time_format_column)
                        {
                            $row2 = $this->db->query("SELECT STR_TO_DATE('$row2','$time_format') as xtime")->row('xtime');
                            $create_folder_month = $this->db->query("SELECT DATE_FORMAT('$row2','%Y-%m') as create_folder_month")->row('create_folder_month');

                            // echo $row.'-'.$row2.'<br>';
                        }
                        if($insert_value_count == $supplier_name_get)
                        {
                            $supplier_name = $this->db->query("SELECT name as supplier_name FROM backend.supcus WHERE type <> 'C' AND code = '$row2' LIMIT 1")->row('supplier_name');
                            // echo $row.'-'.$row2.'<br>';
                        }
                        $insert_value .= "'".$row2."',";
                        $insert_value_count++;
                    }
                    $insert_value .= "'".addslashes($supplier_name)."',";
                    $insert_value2 = rtrim($insert_value,',');
                    // echo $insert_column2.'<br>';
                    // echo $insert_value2.'<br>';die;
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
                    }
                    else
                    {
                        $file_path_checking = $to_location.'/error'.'/'.$create_folder_month;
                        $file_path_checking_array = explode("/",$file_path_checking);
                        // print_r($file_path_checking_array);
                        // echo $file_path_checking;
                        $create_path = '';
                        foreach($file_path_checking_array as $row)
                        {
                            if($row != '')
                            {
                                // echo $row.'<br>';
                                $create_path .= '/'.$row;
                                if($row != 'media' && $row != 'b2b_shared' && $row != 'SENT')
                                {
                                    if(!file_exists($create_path))
                                    {
                                        // echo $create_path.'<br>';
                                        mkdir($create_path, 0777);
                                        chmod($create_path, 0777);
                                    }
                                    // echo $create_path;die;
                                    // echo $row.'<br>';
                                }
                            }
                        }
                        // echo $create_path;
                        // echo $from_location.'/'.$filename.'***'.$file_path_checking.'/'.$filename;
                        // echo $file_path_checking.'/'.$filename;
                        // die;
			$check_extension_array = pathinfo($from_location.'/'.$filename);
			//print_r($check_extension_array);die;
			//print_r(pathinfo('/media/b2b_shared/SENT/error/2021-05/SIN_030521155305_27H033_270521NSBPRA4546_1.pdf'));die;
			//echo $check_extension_array['extension'];die;
			$check_extension = $check_extension_array['extension'];
			if($check_extension == 'pdf')
			{
                        	$month_folder = $create_folder_month;
                        	$from = $from_location.'/'.$filename;
                        	$to = $file_path_checking.'/'.$filename;
                        	$file_name = $filename;
                        	$created_at = $this->db->query("SELECT now() as now")->row('now');
                        	$created_by = 'task_agent';
                        	$updated_at = $this->db->query("SELECT now() as now")->row('now');
                        	$updated_by = 'task_agent';

                        	$this->db->query("INSERT INTO b2b_doc.error_log (month_folder,`from`,`to`,file_name,created_at,created_by) VALUES ('$month_folder','$from','$to','$file_name','$created_at','$created_by') ON DUPLICATE KEY UPDATE updated_by = '$updated_by', updated_at='$updated_at',revise = revise +1");
                        	rename($from_location.'/'.$filename, $file_path_checking.'/'.$filename);
			}
                        // echo 2;die;
                    }

                }
                $document_running_count++;
                if($document_running_count == $document_running_time)
                {
                    break;
                }
            }
            $next_run_time = $this->db->query("SELECT DATE_ADD(NOW(), INTERVAL $run_time_length $run_time_type) as next_run_time")->row('next_run_time');
            $this->db->query("UPDATE b2b_doc.b2b_setting_parameter SET value = '$next_run_time'  WHERE module = 'navition' AND type = 'run_time'");
            // echo $this->db->last_query();die;

            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'Success'
                ]
            ); 
    }  

    public function check_file_time_get()
    {
            header('Content-Type: text/html; charset=utf-8');
            $dir    = 'e:/file/';
            $files1 = scandir($dir);
            $files2 = scandir($dir,1);
// die;

            foreach($files2 as $row)
            {
                $filename = $row;
                if($row != '.' && $row != '..' && $row != '????')
                {       
                        date_default_timezone_set("Asia/Kuala_Lumpur");
                        $file_date = date("Y-m-d H:i:s.", filemtime($dir.$row));
                        $date_now = $this->db->query("SELECT NOW() as now")->row('now');
                        $date_interval = $this->db->query("SELECT DATE_ADD(NOW(), INTERVAL -6 MONTH) as now")->row('now');
                        echo $row.'  ';
                        // echo $date_now.$file_date.'<br>';
                        if($file_date <= $date_interval)
                        {
                            echo '** '.$row;
                        }
                        echo '<br>';
            // copy('../web_module_setup/application/controllers/new/'.$filename, '../web_module_setup/application/controllers/pro/'.$filename);
                }
            }
            die;

            // print_r($files1);
            // print_r($files2); 

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }     
     
    public function test_storeproc_get()
    {
        $result = $this->db->query("CALL lite_b2b.testing('b2b_summary')");
        print_r($result->result());die;
    }      

    public function other_doc_rest_get()
    {
        $task_active = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition_rest' AND type = 'active_navition_rest_task' AND isactive = 1")->row('value');
        $navition_doc_start_date = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition_rest' AND type = 'navition_doc_start_date' AND isactive = 1")->row('value');

        if($task_active == '')
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'active_navition_rest_task inactive'
                ]
            ); 
        }
        if($navition_doc_start_date == '')
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'navition_doc_start_date inactive'
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

        $data = $this->db->query("SELECT '' as status,refno,supcode,supname,doctype,doctime,hq_update,uploaded,uploaded_at,created_by,created_at,(SELECT customer_guid FROM rest_api.run_once_config WHERE active = 1 LIMIT 1) as customer_guid FROM b2b_doc.other_doc WHERE hq_update = 0 AND doctime >= '$navition_doc_start_date'");
        // echo $this->db->last_query();die;
        if($data->num_rows() > 0)
        {

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/other_doc';
             $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/other_doc';
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

            foreach($data->result() as $row)
            {
                $this->db->query("UPDATE b2b_doc.other_doc SET hq_update = 1,uploaded = 1,uploaded_at = NOW() WHERE refno = '$row->refno' AND doctype = '$row->doctype'");
            }
            if($status == "true")
            {
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


}

