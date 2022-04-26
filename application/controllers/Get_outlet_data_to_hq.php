<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Get_outlet_data_to_hq extends REST_controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");

        $check_table_existed = $this->db->query("SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = 'rest_api' AND table_name = 'b2b_config'");
        if ($check_table_existed->row('table_count') <= 0) {
            $response = array(
                'status' => "false",
                'message' => "Database or Table not setup"
            );

            echo json_encode($response);
            die;
        }

        $b2b_ip_https = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'HTTP' AND isactive = 1");
        if ($b2b_ip_https->num_rows() <= 0) {
            $this->b2b_ip_https = '';
            $response = array(
                'status' => "false",
                'message' => "Protocol not setup"
            );

            echo json_encode($response);
            die;
        } else {
            $this->b2b_ip_https = $b2b_ip_https->row('value');
        }

        $b2b_public_ip = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'IP' AND isactive = 1");
        if ($b2b_public_ip->num_rows() <= 0) {
            $this->b2b_public_ip = '';
            $response = array(
                'status' => "false",
                'message' => "IP not setup"
            );

            echo json_encode($response);
            die;
        } else {
            $this->b2b_public_ip = $b2b_public_ip->row('value');
        }

        $b2b_ip_port = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'PORT' AND isactive = 1");
        if ($b2b_ip_port->num_rows() <= 0) {
            $this->b2b_ip_port = '';
            $response = array(
                'status' => "false",
                'message' => "Port not setup"
            );

            echo json_encode($response);
            die;
        } else {
            $this->b2b_ip_port = $b2b_ip_port->row('value');
        }

        // echo $this->b2b_ip_https.'--'.$this->b2b_public_ip.'--'.$this->b2b_ip_port;
        $this->b2b_ip = $this->b2b_ip_https . $this->b2b_public_ip . $this->b2b_ip_port;
        // echo $this->b2b_ip;
        // die;
        // die;
        // $this->b2b_ip = 'http://52.163.112.202';
        // $this->b2b_ip = 'http://127.0.0.1';
    }

    public function index_post()
    {
        $type = $this->input->post('type');
        // $date_from = '';
        // $date_to = '';
        $status = 'false';
        $message = 'Unsuccessful scap outlet data to HQ';

        if ($type == 'STRB') {
            $data = array(
                'refno' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                'SqlScript' => "INSERT INTO `sqlhq`.`sqlscript` ( `refno`, `SqlScript`, `CreatedDateTime`, `CreatedBy`, `Status` ) SELECT REPLACE(UPPER(UUID()),'-','') AS refno, CONCAT(\" INSERT INTO `b2b_hub`.`dbnote_batch_checking` ( `loc_group`, `batch_no`, `sup_code`, `sup_name`, `created_at`, `canceled`, `hq_update`, `posted`, `posted_at`, `status` ) SELECT '\",loc_group,\"', '\",batch_no,\"', '\",sup_code,\"', '\",sup_name,\"', '\",created_at,\"', '\",canceled,\"', '\",hq_update,\"', '\",posted,\"', '\",posted_at,\"', '\",STATUS,\"' \") AS    SqlScript, NOW() AS    CreatedDateTime, 'b2b_system' AS    CreatedBy, 0 AS    STATUS FROM backend.dbnote_batch WHERE posted_at BETWEEN TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND TIMESTAMP(CURDATE());",
                'CreatedDateTime' => date("Y-m-d H:i:s"),
                'CreatedBy' => 'bot_b2b',
                'Status' => '0',
                'KeyField' => '',
            );
            // before insert delete previous data
            $this->db->query("DELETE FROM b2b_hub.dbnote_batch_checking");
            // insert record for update to all outlet
            $result =  $this->db->insert('sqlserver.sqlscript', $data);
            if ($result == '') {
                $status = 'true';
                $message = 'Success scap outlet data to HQ';
            }
        }

        $json = array(
            'status' => $status,
            'message' => $message,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($json));
    }

    public function dbnote_batch_get()
    {
        $data = $this->db->query("SELECT * FROM b2b_hub.dbnote_batch_checking AS a");

        foreach ($data->result() as $row) {

            $date = $this->db->query("SELECT NOW() as now")->row('now');
            $data2 = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            a.*
            FROM b2b_hub.`dbnote_batch_checking` AS a
            WHERE a.batch_no = '$row->batch_no'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            $url = $this->b2b_ip . '/rest_api/index.php/panda_b2b/dbnote_batch';
            // echo $url;die;
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
        } //close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
        );
    }

    function dbnote_batch_post()
    {
        // $url_file = 'C:\xampp\htdocs\rest_api\application\data.json';
        //    $json_data = file_get_contents($url_file, true);
        $json_data = file_get_contents('php://input');
        $daily_array = json_decode($json_data, true);

        //echo var_dump($json_data);
        foreach ($daily_array as $row => $value) {

            $data[] = array(
                'customer_guid' => $value['customer_guid'],
                'loc_group' => $value['loc_group'],
                'batch_no' => $value['batch_no'],
                'sup_code' => $value['sup_code'],
                'sup_name' => $value['sup_name'],
                'created_at' => $value['created_at'],
                'canceled' => $value['canceled'],
                'hq_update' => $value['hq_update'],
                'posted' => $value['posted'],
                'posted_at' => $value['posted_at'],
                'status' => $value['status'],

            );

            $insertqr = $this->db->replace_batch('b2b_checking.dbnote_batch', $data);
        }
        $afrows = $this->db->affected_rows();
        if ($afrows > 0) {
            echo 'true';
        } else {
            echo 'false';
        }
    }
}
