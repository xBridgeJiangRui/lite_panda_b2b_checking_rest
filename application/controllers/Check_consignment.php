<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Check_consignment extends REST_controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
    }

    public function index_get()
    {
        $date_trans = $_REQUEST['date_trans'];

        $result = $this->db->query("SELECT a.* FROM backend.acc_trans a
        WHERE a.date_trans >= '$date_trans' 
        AND a.trans_type = 'INV-CS'
        AND a.approval = '1'
        AND a.company_id = ''")->result_array();

        $json = array(
            'result' => $result,
        );

        $this->response($json);
    }
}
