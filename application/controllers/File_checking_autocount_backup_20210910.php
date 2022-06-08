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
            $create_folder_month = '';
                     
            $path = scandir($from_location,1);
            //print_r($path);die;
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
				$supplier_concat = '0';
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

        $check_sup_name_empty = $this->db->query("SELECT a.refno FROM b2b_doc.other_doc a INNER JOIN b2b_doc.other_doc_mapping b ON a.refno = b.file_refno INNER JOIN backend.supcus c ON b.cross_supcode = c.code WHERE a.supname = ''");
        if($check_sup_name_empty->num_rows() > 0)
        {
            $this->db->query("UPDATE b2b_doc.other_doc a INNER JOIN b2b_doc.other_doc_mapping b ON a.refno = b.file_refno INNER JOIN backend.supcus c ON b.cross_supcode = c.code SET a.supname = c.name,a.hq_update = 0 WHERE a.supname = ''");
        }

        $check_sup_name = $this->db->query("SELECT c.code,c.name,c.accpdebit,b.cross_supcode,b.cross_refno,a.* FROM b2b_doc.`other_doc` a INNER JOIN b2b_doc.`other_doc_mapping` b ON a.refno = b.file_refno AND a.supcode = b.file_supcode INNER JOIN backend.supcus c ON b.cross_supcode = c.accpdebit WHERE a.supname = ''");

        if($check_sup_name->num_rows() > 0)
        {
            $this->db->query("UPDATE b2b_doc.`other_doc` a INNER JOIN b2b_doc.`other_doc_mapping` b ON a.refno = b.file_refno AND a.supcode = b.file_supcode INNER JOIN backend.supcus c ON b.cross_supcode = c.accpdebit SET a.supname = c.name,a.hq_update = 0 WHERE a.supname = ''");
        }

        $data = $this->db->query("SELECT '' AS status, b.`cross_refno` AS refno, b.`cross_supcode` AS supcode, a.supname, a.doctype, a.doctime, a.hq_update, a.uploaded, a.uploaded_at, a.created_by, a.created_at, (SELECT customer_guid FROM rest_api.run_once_config WHERE active = 1 LIMIT 1) AS customer_guid FROM b2b_doc.other_doc a INNER JOIN b2b_doc.`other_doc_mapping` b ON a.refno = b.`file_refno` AND a.supcode = b.cross_supcode WHERE a.hq_update = 0 AND a.doctime >= '$autocount_doc_start_date'");        
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
            // $status = "true";

            foreach($data->result() as $row)
            {
                $this->db->query("UPDATE b2b_doc.other_doc a INNER JOIN b2b_doc.other_doc_mapping b ON a.refno = b.file_refno SET a.hq_update = 1,a.uploaded = 1,a.uploaded_at = NOW() WHERE b.cross_refno = '$row->refno' AND a.doctype = '$row->doctype'");
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

}

