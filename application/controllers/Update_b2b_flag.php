<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Update_b2b_flag extends REST_controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
        // $this->b2b_ip = 'http://52.163.112.202';
        // $this->b2b_ip = 'http://127.0.0.1';
    } 

    public function index_post()
    {
        // $json_data = file_get_contents('php://input');
        // $daily_array = [json_decode($json_data, true)];
        $vendor_code = $this->input->post('vendor_code');
        $type = $this->input->post('type');
        $insert_sql_query = $this->input->post('insert_sql_query');
        $status = '';
        $message = '';
        // convert string to array
        $vendor_code = explode(',', $vendor_code);

        if ($type == 'create') {

            foreach ($vendor_code as $key => $value) {

                // get vendor code status
                $vendor_code_b2b_status = $this->db->query("SELECT a.`Code`,a.`Name`,a.`b2b_registration`,a.`supcus_guid`
                FROM backend.`supcus` AS a
                WHERE a.`Code` = '$value'");

                // if b2b_registration not equeal to 1 update b2b_registration flag and insert sqlserver.sqlscript
                if ($vendor_code_b2b_status->row('b2b_registration') != '1') {
                    // update supcus b2b_registration flag to 1
                    $this->db->query("UPDATE backend.supcus AS a
                    SET a.`b2b_registration` ='1', a.LastStamp = NOW()
                    WHERE a.`b2b_registration`='0' AND a.`Code` = '$value'");

                    if ($insert_sql_query == '1') {
                        // $data = array(
                        //     'refno' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                        //     'SqlScript' => "UPDATE backend.supcus SET b2b_registration = 1 WHERE supcus_guid='" . $vendor_code_b2b_status->row('supcus_guid') . "'",
                        //     'CreatedDateTime' => date("Y-m-d H:i:s"),
                        //     'CreatedBy' => 'bot_b2b',
                        //     'Status' => '1',
                        //     'KeyField' => '',
                        // );

                        // insert record for update to all outlet
                        // $this->db->insert('sqlserver.sqlscript', $data);
                        $this->db->query("INSERT INTO sqlserver.sqlscript
                        SELECT REPLACE(UPPER(UUID()),'-','') AS refno, 
                        CONCAT('UPDATE backend.supcus SET b2b_registration = 1, LastStamp = NOW() WHERE supcus_guid=\'',supcus_guid,'\'') AS SqlScript,
                        NOW() AS CreatedDateTime,
                        'bot_b2b' AS CreatedBy,
                        0 AS STATUS,
                        '' AS KeyField
                         FROM backend.`supcus` WHERE supcus_guid = '" . $vendor_code_b2b_status->row('supcus_guid') . "'");
                    }


                    $affected_row = $this->db->affected_rows();

                    if ($affected_row > 0) {
                        $status = 'true';
                        $message = 'Successful Update B2B Registration Flag';
                    } else {
                        $status = 'false';
                        $message = 'Fail to Update B2B Registration Flag';
                    }
                }else{
                    $status = 'true';
                    $message = 'No record Update';
                }
            }
        } else  if ($type == 'delete') {

            foreach ($vendor_code as $key => $value) {

                // get vendor code status
                $vendor_code_b2b_status = $this->db->query("SELECT a.`Code`,a.`Name`,a.`b2b_registration`,a.`supcus_guid`
                FROM backend.`supcus` AS a
                WHERE a.`Code` = '$value'");

                // if b2b_registration not equeal to 0 update b2b_registration flag and insert sqlserver.sqlscript
                if ($vendor_code_b2b_status->row('b2b_registration') != '0') {
                    // update supcus b2b_registration flag to 0
                    $this->db->query("UPDATE backend.supcus AS a
                    SET a.`b2b_registration` ='0', a.LastStamp = NOW()
                    WHERE a.`b2b_registration`='1' AND a.`Code` = '$value'");

                    if ($insert_sql_query == '1') {

                        // $data = array(
                        //     'refno' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                        //     'SqlScript' => "UPDATE backend.supcus SET b2b_registration = 0 WHERE supcus_guid='" . $vendor_code_b2b_status->row('supcus_guid') . "'",
                        //     'CreatedDateTime' => date("Y-m-d H:i:s"),
                        //     'CreatedBy' => 'bot_b2b',
                        //     'Status' => '0',
                        //     'KeyField' => '',
                        // );

                        // // insert record for update to all outlet
                        // $this->db->insert('sqlserver.sqlscript', $data);

                        $this->db->query("INSERT INTO sqlserver.sqlscript
                        SELECT REPLACE(UPPER(UUID()),'-','') AS refno, 
                        CONCAT('UPDATE backend.supcus SET b2b_registration = 0, LastStamp = NOW() WHERE supcus_guid=\'',supcus_guid,'\'') AS SqlScript,
                        NOW() AS CreatedDateTime,
                        'bot_b2b' AS CreatedBy,
                        0 AS STATUS,
                        '' AS KeyField
                         FROM backend.`supcus` WHERE supcus_guid = '" . $vendor_code_b2b_status->row('supcus_guid') . "'");
                    }

                    $affected_row = $this->db->affected_rows();

                    if ($affected_row > 0) {
                        $status = 'true';
                        $message = 'Successful Update B2B Registration Flag';
                    } else {
                        $status = 'false';
                        $message = 'Fail to Update B2B Registration Flag';
                    }
                }else{
                    $status = 'true';
                    $message = 'No record Update';
                }
            }
        } else {
            $status = 'false';
            $message = 'Please Input Valid Type';
        }

        $json = array(
            'status' => $status,
            'message' => $message,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($json));
    }
}

