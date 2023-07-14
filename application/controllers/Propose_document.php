<?php

require(APPPATH.'/libraries/REST_Controller.php');

class Propose_document extends REST_Controller{

  public function __construct()
  {
    parent::__construct();  
    $this->load->model('Main_model');
    $this->load->helper('url');
    $this->load->database();
    date_default_timezone_set("Asia/Kuala_Lumpur");
  }

  public function retrieve_propose_po_post()
  {
    $json_data = file_get_contents('php://input');
    $daily_array = json_decode($json_data,true);

    // print_r($daily_array);  echo "\n";
    // print_r($daily_array['base_url']);  echo "\n";
    // print_r($daily_array['key']['refno']); echo "\n";
    $error_insert = 0;
    $error_ack = 0;
    $data_array = $daily_array['key'];
    $base_url = $daily_array['base_url'];
    $acknowledge_url = $daily_array['acknowledge_url'];

    $to_shoot_url = $base_url;

    //echo $to_shoot_url; die;

    $cuser_name = 'ADMIN';
    $cuser_pass = '1234';

    $ch = curl_init($to_shoot_url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
    // curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
    $result = curl_exec($ch);
    $output = json_decode($result,true);
    // $status = json_encode($output);
    // print_r($result);die;
    // print_r($output->result);die;
    // print_r(json_decode(json_encode($output->result), true)); die;
    //echo $result;die;
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($httpcode == '200')
    {
        // print_r(json_encode($output));  die;
        $poex_guid = $output['poex_guid'];
        $total_child_count = $output['total_child_count'];
        $propose_po_child = $output['ProposePoExC_poex_guid'];
        $count_propose_po_child = count($propose_po_child);
        // print_r($count_propose_po_child); 
        // print_r($poex_guid); die;

        if($total_child_count == $count_propose_po_child)
        {
            // print_r($propose_po_child); die;
            $delete_child = $this->db->query("DELETE FROM `b2b_hub`.`propose_poex_c` WHERE poex_guid = '$poex_guid'");
            $delete_main = $this->db->query("DELETE FROM `b2b_hub`.`propose_poex` WHERE poex_guid = '$poex_guid'");

            $data_main = array(
                'poex_guid' => $output['poex_guid'],
                'retailer_guid' => $output['retailer_guid'],
                'trans_type' => $output['trans_type'],
                'sup_code' => $output['sup_code'],
                'sup_name' => $output['sup_name'],
                'remark' => $output['remark'],
                'salesman' => $output['salesman'],
                'docdate' => $output['docdate'],
                'delivery_date' => $output['delivery_date'],
                'doh_min' => $output['doh_min'],
                'doh_max' => $output['doh_max'],
                'hq_update' => $output['hq_update'],
                'branch' => $output['branch'],
                'branch_desc' => $output['branch_desc'],
                'doc_no' => $output['doc_no'],
                'date_start' => $output['date_start'],
                'date_stop' => $output['date_stop'],
                'order_day' => $output['order_day'],
                'selected_h' => $output['selected_h'],
                'include_qoh' => $output['include_qoh'],
                'increase_value' => $output['increase_value'],
                'sold_datefrom' => $output['sold_datefrom'],
                'sold_dateto' => $output['sold_dateto'],
                'doc_type' => $output['doc_type'],
                'rep_all_ads' => $output['rep_all_ads'],
                'ads_inc_ibt' => $output['ads_inc_ibt'],
                'doc_status' => $output['doc_status'],
                'created_at' => $output['created_at'],
                'created_by' => $output['created_by'],
                'updated_at' => $output['updated_at'],
                'updated_by' => $output['updated_by'],
                'posted' => $output['posted'],
                'posted_at' => $output['posted_at'],
                'posted_by' => $output['posted_by'],
                'cancel' => $output['cancel'],
                'cancel_at' => $output['cancel_at'],
                'cancel_by' => $output['cancel_by'],
                'imported' => 1,
                'imported_at' => $output['imported_at'],
                'imported_by' => 'HQ_GRAB',
                'exported' => 0,
                'is_valid' => 0,
                "total_child_count" => $output['total_child_count'],
                'po_refno' => $output['po_refno'],
                'refno' => $output['refno'],
            );
            $this->db->insert('b2b_hub.propose_poex', $data_main);

            foreach($propose_po_child as $child)
            {
                $data_child = array(
                    'detail_guid' => $child['detail_guid'],
                    'seq' => $child['seq'],
                    'itemcode' => $child['itemcode'],
                    'description' => $child['description'],
                    'qty_propose' => $child['qty_propose'],
                    'qty_actual' => $child['qty_actual'],
                    'um' => $child['um'],
                    'price_propose' => $child['price_propose'],
                    'price_actual' => $child['price_actual'],
                    'qty_foc_propose' => $child['qty_foc_propose'],
                    'qty_foc_actual' => $child['qty_foc_actual'],
                    'amount_propose' => $child['amount_propose'],
                    'amount_actual' => $child['amount_actual'],
                    'packsize' => $child['packsize'],
                    'dept' => $child['dept'],
                    'subdept' => $child['subdept'],
                    'category' => $child['category'],
                    'manufacturer' => $child['manufacturer'],
                    'brand' => $child['brand'],
                    'created_by' => $child['created_by'],
                    'created_at' => $child['created_at'],
                    'updated_by' => $child['updated_by'],
                    'updated_at' => $child['updated_at'],
                    'bulkqty' => $child['bulkqty'],
                    'umbulk' => $child['umbulk'],
                    'qty_opn' => $child['qty_opn'],
                    'qty_rec' => $child['qty_rec'],
                    'qty_sold' => $child['qty_sold'],
                    'qty_other' => $child['qty_other'],
                    'qty_bal' => $child['qty_bal'],
                    'ads' => $child['ads'],
                    'ams' => $child['ams'],
                    'aws' => $child['aws'],
                    'days' => $child['days'],
                    'itemlink' => $child['itemlink'],
                    'qty_min' => $child['qty_min'],
                    'qty_max' => $child['qty_max'],
                    'qty_need' => $child['qty_need'],
                    'date_start' => $child['date_start'],
                    'date_stop' => $child['date_stop'],
                    'first_grdate' => $child['first_grdate'],
                    'hq_update' => $child['hq_update'],
                    'sent_outlet' => $child['sent_outlet'],
                    'carton_cost' => $child['carton_cost'],
                    'doh' => $child['doh'],
                    'order_lot' => $child['order_lot'],
                    'qty_po' => $child['qty_po'],
                    'qty_req' => $child['qty_req'],
                    'qty_so' => $child['qty_so'],
                    'qty_pos' => $child['qty_pos'],
                    'qty_si' => $child['qty_si'],
                    'qty_tbr' => $child['qty_tbr'],
                    'doh_new' => $child['doh_new'],
                    'qty_hp_out' => $child['qty_hp_out'],
                    'qty_ibt_sales' => $child['qty_ibt_sales'],
                    'convert_po' => $child['convert_po'],
                    'selected_c' => $child['selected_c'],
                    'qty_ibt_grn' => $child['qty_ibt_grn'],
                    'qty_safety' => $child['qty_safety'],
                    'ads_all' => $child['ads_all'],
                    'articleno' => $child['articleno'],
                    'barcode' => $child['barcode'],
                    'qty_max_im' => $child['qty_max_im'],
                    'supplier_qoh' => $child['supplier_qoh'],
                    'entrytype' => $child['entrytype'],
                    'poex_guid' => $child['poex_guid'],
                    // 'imported' => 1,
                    // 'imported_at' => $this->db->query("SELECT now() as today")->row('today'),
                    // 'imported_by' => 'jiangrui',
                );
                $this->db->insert('b2b_hub.propose_poex_c', $data_child);
            }

            // $check_propose_child = $this->db->query("SELECT COUNT(a.poex_guid) AS total_child FROM b2b_hub.propose_poex_c a WHERE a.poex_guid = '$poex_guid' GROUP BY a.poex_guid")->row('total_child');

            // print_r($check_propose_child); die;

            $check_propose_child = $this->db->query("SELECT a.detail_guid FROM b2b_hub.propose_poex_c a WHERE a.poex_guid = '$poex_guid' GROUP BY a.detail_guid")->result_array();

            $check_propose_main = $this->db->query("SELECT a.poex_guid FROM b2b_hub.propose_poex a WHERE a.poex_guid = '$poex_guid'")->result_array();

            if(count($check_propose_child) == $count_propose_po_child && count($check_propose_main) == 1)
            {
                $data_ack[] = array(
                    'poex_guid' => $poex_guid,
                    'propose_child' => $check_propose_child,
                );
            }
            else
            {
                $data_ack = [];
                $error_insert++;

                $data_log = array(
                    'guid' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                    'doc_type' => 'PO',
                    'doc_guid' => $poex_guid,
                    'module_key' => '',
                    'module' => 'RetreiveFromB2B',
                    'url' => $base_url,
                    'post_data' => json_encode($output),
                    'response' => 'Failed to insert',
                    'resp_flag' => 0,
                    'curl_info' => '',
                    'date' => $this->db->query("SELECT CURDATE() as `date`")->row('date'),
                    'datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
                    'resp_datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
                    'status' => 'FAILED',
                );
                $this->db->insert('b2b_hub.propose_trans_logs', $data_log);
            }

        }
        else
        {
            $data_ack = [];
            $error_insert++;

            $data_log = array(
                'guid' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                'doc_type' => 'PO',
                'doc_guid' => $poex_guid,
                'module_key' => '',
                'module' => 'RetreiveFromB2B',
                'url' => $base_url,
                'post_data' => json_encode($output),
                'response' => 'TotolCountInvalid',
                'resp_flag' => 0,
                'curl_info' => '',
                'date' => $this->db->query("SELECT CURDATE() as `date`")->row('date'),
                'datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
                'resp_datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
                'status' => 'FAILED',
            );
            $this->db->insert('b2b_hub.propose_trans_logs', $data_log);
        }

        // $set_array = json_decode(json_encode($data_ack),true);  

        // $check_ack_error = json_decode(json_encode($this->db->query("SELECT a.poex_guid FROM b2b_hub.propose_poex a WHERE a.poex_guid = '$poex_guid' AND a.imported = '99' ")->result_array()),true);

        // $error_merge_to_ack = array_merge($set_array,$check_ack_error);

        // $json_data = json_encode($output->results);
        // print_r(json_encode($data_ack)); die;

        if(count($data_ack) > 0 )
        {
            //print_r(count($data_array)); die;

            $url = $acknowledge_url;
            // echo $url;die;
            // die;
            $cuser_name = 'ADMIN';
            $cuser_pass = '1234';

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_ack));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
            $result = curl_exec($ch);
            $output = json_decode($result);
            // $status = json_encode($output);
            // print_r($output->result);die;
            // echo $output;die;
            //close connection
            curl_close($ch);

            if(isset($output->status))
            {
                if($output->status == True)
                {
                    $response_ack = array(
                        'status' => "true",
                        'message' => "Success Acknowledge"
                    );
                }
                else
                {
                    $error_ack++;

                    $data_log = array(
                        'guid' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                        'doc_type' => 'PO',
                        'doc_guid' => $poex_guid,
                        'module_key' => '',
                        'module' => 'AcknowledgeFail',
                        'url' => $acknowledge_url,
                        'post_data' => json_encode($data_ack),
                        'response' => 'TotolCountInvalid',
                        'resp_flag' => 0,
                        'curl_info' => '',
                        'date' => $this->db->query("SELECT CURDATE() as `date`")->row('date'),
                        'datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
                        'resp_datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
                        'status' => 'FAILED',
                    );
                    $this->db->insert('b2b_hub.propose_trans_logs', $data_log);
                }
            }
            else
            {
                $error_ack++;

                $data_log = array(
                    'guid' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                    'doc_type' => 'PO',
                    'doc_guid' => $poex_guid,
                    'module_key' => '',
                    'module' => 'AcknowledgeProcess',
                    'url' => $acknowledge_url,
                    'post_data' => json_encode($data_ack),
                    'response' => 'TotolCountInvalid',
                    'resp_flag' => 0,
                    'curl_info' => '',
                    'date' => $this->db->query("SELECT CURDATE() as `date`")->row('date'),
                    'datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
                    'resp_datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
                    'status' => 'FAILED',
                );
                $this->db->insert('b2b_hub.propose_trans_logs', $data_log);
            }
        }

        if($error_insert == 0 && $error_ack == 0)
        {
            $reponse = array(
                'status' => "true",
                'message' => "Success Insert to HQ"
            );
        }
        else
        {
            $reponse = array(
                'status' => "false",
                'message' => "Failed to Insert to HQ"
            );
        }

        echo json_encode($reponse);
        die;
    }
    else
    {
        $data_log = array(
            'guid' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
            'doc_type' => 'PO',
            'doc_guid' => '',
            'module_key' => '',
            'module' => 'RetreiveFromB2B',
            'url' => $base_url,
            'post_data' => json_encode($output),
            'response' => 'FailedToShoot',
            'resp_flag' => 0,
            'curl_info' => '',
            'date' => $this->db->query("SELECT CURDATE() as `date`")->row('date'),
            'datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
            'resp_datetime' => $this->db->query("SELECT NOW() as today")->row('today'),
            'status' => 'FAILED',
        );
        $this->db->insert('b2b_hub.propose_trans_logs', $data_log);
        
        $reponse = array(
            'status' => "false",
            'message' => "Failed to Trigger."
        );

        echo json_encode($reponse);
        die;
    }
  }

}
