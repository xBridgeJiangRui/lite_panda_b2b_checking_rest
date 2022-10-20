<?php

require(APPPATH.'/libraries/REST_Controller.php');

class Check_compare_einv extends REST_Controller{

    public function __construct()
    {
        parent::__construct();

        // $this->load->model('Main_model');
        // $this->load->helper('url');
        $this->load->database();

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

        // echo $this->b2b_ip_https.'--'.$this->b2b_public_ip.'--'.$this->b2b_ip_port;
        $this->b2b_ip = $this->b2b_ip_https.$this->b2b_public_ip.$this->b2b_ip_port;

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
    }

    public function check_einv_data_hub_old_post()
    {
        $table = $_REQUEST['table'];
        $start_date = $_REQUEST['start_date'];
        $end_date = $_REQUEST['end_date'];
        $guid = $this->input->post('guid');
        $column_data = $_REQUEST['column_data'];
        $guid = implode("','",$guid);

        //print_r($guid); die;

        if ($guid !='' || $guid != 'null' || $guid != null) {
            $guid_where = "WHERE a.$column_data IN ($guid)";
            $imported_at_where = "";
        }else{
            $guid_where='';
            $imported_at_where ="WHERE a.created_at BETWEEN '$start_date' AND '$end_date'";
        }

        $query_einv = $this->db->query("SELECT a.* FROM b2b_hub.einv_main a 
        $guid_where
        $imported_at_where")->result_array();
        
        $response = array(
            'status' => "true",
            'result' => $query_einv
        );

        echo json_encode($response);die;

    }

    public function check_einv_data_hub_post()
    {
        $table = $_REQUEST['table'];
        $start_date = $_REQUEST['start_date'];
        $end_date = $_REQUEST['end_date'];
	    $guid = $this->input->post('guid');
        $column_data = $_REQUEST['column_data'];
 	    $select_add = '';
	    $condition_groupby = '';
        //print_r($guid); die;

        if ($guid !='') {
            $guid_where = "WHERE a.$column_data IN ($guid)";
            $imported_at_where = "";
        }else{
            $guid_where='';
            $imported_at_where ="WHERE a.imported_at BETWEEN '$start_date' AND '$end_date'";
        }

        if($table == 'einv_child')
        {
            $select_add = "ROUND(SUM(a.total_amt_incl_tax),2) AS total_amount,";
        $condition_groupby = "GROUP BY a.einv_guid";
        }

        if($table == 'ecn_child')
        {
            $select_add = "ROUND(SUM(a.total_gross),2) AS sum_total_gross,";
        $condition_groupby = "GROUP BY a.ecn_guid";
        }

        $query_einv = $this->db->query("SELECT $select_add a.* FROM b2b_hub.$table a 
        $guid_where
        $imported_at_where
	    $condition_groupby ")->result_array();
	    //echo $this->db->last_query();die;
        $response = array(
            'status' => "true",
            'result' => $query_einv
        );

        echo json_encode($response);die;
    }
}
