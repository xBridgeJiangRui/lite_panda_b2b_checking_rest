<?php

require(APPPATH.'/libraries/REST_Controller.php');

class Upload_sales_data extends REST_Controller{

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
    } 

    public function sku_cs_date_get()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);        
        $database = 'b2b_hub';
        $table = 'acc_trans_code_sku_cs_date';
        $query = "SELECT * FROM $database.$table WHERE exported = 0 ORDER BY supcus_code ASC LIMIT 100";
        $data = $this->db->query("$query");
        $customer_guid_array = $this->db->query("SELECT customer_guid FROM rest_api.`run_once_config` WHERE active = 1 LIMIT 1");
        $customer_guid = $customer_guid_array->row('customer_guid');
        $message = '';
        $i = 0;
        $ii = 0;
        $iii = 0;
        $time_start = microtime(true);

        if($customer_guid_array->num_rows() <= 0)
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'Rest api config not active'
                ]
            );             
        }

        if($data->num_rows() <= 0)
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'No record to sync'
                ]
            );             
        }
        // print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            $iii++;
             //echo $row->RefNo;die; 
            $acc_refno = $row->refno;
            $operation = $row->operation;
            $database2 = 'report_summary';
            $table2 = 'sku_cs_date';
            $query2 = "SELECT * FROM $database2.$table2 WHERE acc_refno = '$acc_refno'";
            // echo $query2;die;
            $result = $this->db->query("$query2");
            // echo $this->db->last_query();die;
            if($result->num_rows() > 0)
            {   
                $database3 = 'backend';
                $table3 = 'acc_trans';
                $acc_total = $this->db->query("SELECT ROUND(SUM(amount),2) as acc_total FROM $database3.$table3 WHERE refno = '$acc_refno'");
                // echo $this->db->last_query();die;
                // echo $acc_total->row('acc_total');
                // die;
                $sum_query = "SELECT ROUND(SUM(Cost_CS),2) as cost FROM $database2.$table2 WHERE acc_refno = '$acc_refno'";
                $result_sum = $this->db->query("$sum_query");

                // echo $result_sum->row('cost');
                $variance = $result_sum->row('cost') -$acc_total->row('acc_total');
                // echo $variance; 
                if($variance <= 1.00)
                {
                    // echo 'valid';
                }
                else
                {
                    $run = $this->db->query("UPDATE $database.$table SET exported = 9,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                    $ii++;
                    continue;
                }                  
                // echo 1;die;
                $data = array(
                    'customer_guid' => $customer_guid,
                    'operation' => $operation,
                    'refno' => $acc_refno,
                    'result' => $result->result(),
                );
                // print_r($data);die;
                $username = 'admin'; //get from rest.php
                $password = '1234'; //get from rest.php

                //$url = 'http://127.0.0.1/PANDA_GITHUB/rest_b2b/index.php/Get_sales/sku_cs_date';
                // $url = 'http://127.0.0.1/rest_b2b/index.php/Get_sales/sku_cs_date';
                $url = $this->b2b_ip.'/rest_b2b/index.php/Get_sales/sku_cs_date';
                $ch = curl_init($url);
                // echo $url;die;

                curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                $result = curl_exec($ch);
                // echo $result;die;
                $output =  json_decode($result);
                // print_r($output);die;
                $status = $output->status;
                // echo $status;die;
                if($status == "true")
                {
                    $run = $this->db->query("UPDATE $database.$table SET exported = 1,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                    $i++;
                    // echo 1;die;
                }
                else
                {
                    // echo $iii.'error';die;
                    // $run = $this->db->query("UPDATE $database.$table SET exported = 99,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                }
            }
            else
            {
                $run = $this->db->query("UPDATE $database.$table SET exported = 9,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                $ii++;
            }
        }//close foreach
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        $message = 'Used time '.$execution_time.' Uploaded '.$i.' record'.'    '.$ii.' sales data record not found';
        $this->response(
            [
                'status' => TRUE,
                'message' => $message
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }

    public function update_sku_cs_date_post()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $message = '';
        $time_start = microtime(true);

        $refno = $this->input->post('refno');
        if($refno == '' || $refno == null)
        {
            $response = array(
                'status' => "false",
                'message' => "Refno is empty",
            ); 
            echo json_encode($response);die;            
        }        

        $database = 'b2b_hub';
        $table = 'acc_trans_code_sku_cs_date';
        $query = "SELECT * FROM $database.$table WHERE refno = '$refno' LIMIT 1";
        // echo $query;die;
        $data = $this->db->query("$query");

        if($data->num_rows() <= 0)
        {
            $response = array(
                'status' => "false",
                'message' => "Refno is not existed",
            ); 
            echo json_encode($response);die; 
        }
        // die;

        $query2 = "UPDATE $database.$table SET exported = 0 WHERE refno = '$refno'";
        // echo $query2;die;
        $data = $this->db->query("$query2");

        $affected_rows = $this->db->affected_rows();

        // $customer_guid_array = $this->db->query("SELECT customer_guid FROM rest_api.`run_once_config` WHERE active = 1 LIMIT 1");


        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        $message = 'Used time '.$execution_time;

        if($affected_rows <= 0)
        {
            $response = array(
                    'status' => "false",
                    'message' => 'No record updated. '.$message
            ); 
            // echo json_encode($response);die;              
        }
        else
        {
            $response = array(
                    'status' => "true",
                    'message' => 'Record updated. '.$message
            ); 
            // echo json_encode($response);die;            
        }

        echo json_encode($response);die;
        // print_r($data->num_rows());die;
    }  

    public function upload_sku_cs_date_get()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $error = 0;
        $database = 'report_summary';
        $interval = '-1';
        $table = 'sku_cs_date';
        $start_date = $this->db->query("SELECT DATE_FORMAT(DATE_ADD(NOW(), INTERVAL $interval MONTH),'%Y-%m-01') as now")->row('now');
        $end_date = $this->db->query("SELECT DATE_FORMAT(LAST_DAY(DATE_ADD(NOW(), INTERVAL -0 MONTH)),'%Y-%m-%d') as now")->row('now');

        $start_date = '2021-08-01';
        $end_date = '2021-08-31';

        $customer_guid = $this->db->query("SELECT customer_guid FROM rest_api.run_once_config WHERE active = 1")->row('customer_guid');

        $result = $this->db->query("SELECT '$customer_guid' as customer_guid,a.* FROM $database.$table a WHERE a.b2b_status = 0 AND a.bizdate BETWEEN '$start_date' AND '$end_date' LIMIT 500");

        foreach($result->result() as $row)
        {
            $bizdate = $row->bizdate;
            $location = $row->Location;
            $itemcode = $row->itemcode;
            $itemtype = $row->itemtype;
            // echo $row->bizdate.' '.$row->Location.' '.$row->itemcode.' '.$row->itemtype.'<br>';
            $data = array($row);
            // echo json_encode($data);die;
            $module = 'rest_b2b/index.php/Get_sales/get_sku_cs_date';
            $to_shoot_url = $this->b2b_ip."/".$module;
            // echo $to_shoot_url;die;

            $cuser_name = 'ADMIN';
            $cuser_pass = '1234';

            $ch = curl_init($to_shoot_url);
           // curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-API-KEY: " . "CODEX1234" ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            $output = json_decode($result);

            // echo $result;die;
            // print_r($output->status);
            if(isset($output->status))
            {
                if($output->status == 'true')
                {
                    $this->db->query("UPDATE $database.$table SET b2b_status = 1 WHERE bizdate = '$bizdate' AND Location = '$location' AND itemcode = '$itemcode' AND itemtype = '$itemtype'");
                }
                else
                {
                    $error++;
                }
                $status = $output->status;
            }
            else
            {
                $error++;
            }
        }

        if($error > 0)
        {
            $message = 'Send unsuccessful';
            $return_status = 'false';
        }
        else
        {
            $message = 'Send successfully';
            $return_status = 'true';
        }

        $response = array(
            'status' => $return_status,
            'message' => $message,
        );
        echo json_encode($response);die;
    }

    public function everrise_sku_cs_date_get()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);        
        $database = 'b2b_hub';
        $table = 'acc_trans_code_sku_cs_date';
        $query = "SELECT * FROM $database.$table WHERE exported = 0 ORDER BY supcus_code ASC LIMIT 100";
        $data = $this->db->query("$query");
        $customer_guid_array = $this->db->query("SELECT customer_guid FROM rest_api.`run_once_config` WHERE active = 1 LIMIT 1");
        $customer_guid = $customer_guid_array->row('customer_guid');
        $message = '';
        $i = 0;
        $ii = 0;
        $iii = 0;
        $time_start = microtime(true);

        if($customer_guid_array->num_rows() <= 0)
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'Rest api config not active'
                ]
            );             
        }

        if($data->num_rows() <= 0)
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'No record to sync'
                ]
            );             
        }
        // print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            $iii++;
             //echo $row->RefNo;die; 
            $acc_refno = $row->refno;
            $operation = $row->operation;
            $database2 = 'report_summary';
            $table2 = 'sku_cs_date';
            // jr edited due to sku cs date cant get data and everrise want to divide to two quater upload sales - 26/04/2022
            $get_acc_trans = $this->db->query("SELECT refno,date_from,date_to,supcus_code FROM backend.acc_trans WHERE refno = '$acc_refno'");
            $get_acc_trans_c2 = $this->db->query("SELECT trans_guid,outlet FROM backend.acc_trans_c2 a WHERE a.`trans_guid` = '$acc_refno' LIMIT 1");
            $sup_code = $get_acc_trans->row('supcus_code');
            $start_date = $get_acc_trans->row('date_from'); 
            $end_date = $get_acc_trans->row('date_to'); 
            $loc_group = $get_acc_trans_c2->row('outlet'); 

            if($sup_code == '' || $sup_code == 'null' || $sup_code == null || $start_date == '' || $start_date == 'null' || $start_date == null 
            || $end_date == '' || $end_date == 'null' || $end_date == null || $loc_group == '' || $loc_group == 'null' || $loc_group == null)
            {
            $run = $this->db->query("UPDATE $database.$table SET exported = 9,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                $ii++;
                continue;
            }
            //$query2 = "SELECT * FROM $database2.$table2 WHERE acc_refno = '$acc_refno'";
            $query2 = "SELECT * FROM report_summary.sku_cs_date WHERE sup_code = '$sup_code' AND location = '$loc_group' AND bizdate BETWEEN '$start_date' AND '$end_date'";
            // echo $query2;die;
            $result = $this->db->query("$query2");
            // echo $this->db->last_query();die;
            if($result->num_rows() > 0)
            {   
                $database3 = 'backend';
                $table3 = 'acc_trans';
                $acc_total = $this->db->query("SELECT ROUND(SUM(amount),2) as acc_total FROM $database3.$table3 WHERE refno = '$acc_refno'");
                // echo $this->db->last_query();die;
                // echo $acc_total->row('acc_total');
                // die;

                //$sum_query = "SELECT ROUND(SUM(Cost_CS),2) as cost FROM $database2.$table2 WHERE acc_refno = '$acc_refno'";
                $sum_query = "SELECT ROUND(SUM(Cost_CS),2) AS cost FROM report_summary.sku_cs_date WHERE sup_code = '$sup_code' AND location = '$loc_group' AND bizdate BETWEEN '$start_date' AND '$end_date'";
                // end jr edited 26/04/2022
                $result_sum = $this->db->query("$sum_query");

                // echo $result_sum->row('cost');
                $variance = $result_sum->row('cost') -$acc_total->row('acc_total');
                // echo $variance; 
                if($variance <= 1.00)
                {
                    // echo 'valid';
                }
                else
                {
                    $run = $this->db->query("UPDATE $database.$table SET exported = 9,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                    $ii++;
                    continue;
                }                  
                // echo 1;die;
                $data = array(
                    'customer_guid' => $customer_guid,
                    'operation' => $operation,
                    'refno' => $acc_refno,
                    'result' => $result->result(),
                );
                // print_r($data);die;
                $username = 'admin'; //get from rest.php
                $password = '1234'; //get from rest.php

                //$url = 'http://127.0.0.1/PANDA_GITHUB/rest_b2b/index.php/Get_sales/sku_cs_date';
                // $url = 'http://127.0.0.1/rest_b2b/index.php/Get_sales/sku_cs_date';
                $url = $this->b2b_ip.'/rest_b2b/index.php/Get_sales/sku_cs_date_by_datetrans';
		        // edited by jr sku_cs_date - 26/04/2022
                $ch = curl_init($url);
                // echo $url;die;

                curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                $result = curl_exec($ch);
                // echo $result;die;
                $output =  json_decode($result);
                 //print_r($output);die;
                $status = $output->status;
                //echo $status;die;
                if($status == "true")
                {
                    $run = $this->db->query("UPDATE $database.$table SET exported = 1,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                    $i++;
                    // echo 1;die;
                }
                else
                {
                    // echo $iii.'error';die;
                    // $run = $this->db->query("UPDATE $database.$table SET exported = 99,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                }
            }
            else
            {
                $run = $this->db->query("UPDATE $database.$table SET exported = 9,exported_at = NOW() WHERE refno = '$acc_refno'"); 
                $ii++;
            }
        }//close foreach
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        $message = 'Used time '.$execution_time.' Uploaded '.$i.' record'.'    '.$ii.' sales data record not found';
        $this->response(
            [
                'status' => TRUE,
                'message' => $message
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }
}

