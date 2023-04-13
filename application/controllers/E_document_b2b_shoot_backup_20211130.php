<?php

require(APPPATH.'/libraries/REST_Controller.php');

class E_document_b2b_shoot extends REST_Controller{

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

    public function einv_main_get()
    {
        $database1 = 'rest_api';
        $table1 = 'run_once_config';

        $customer_guid_array = $this->db->query("SELECT * FROM $database1.$table1 WHERE active = 1");

        if($customer_guid_array->num_rows() <= 0)
        {
            $reponse = array(
                'status' => "false",
                'message' => "Customer Guid Not Setup"
            );
            echo json_encode($reponse);
            die;
        }

        $customer_guid = $customer_guid_array->row('customer_guid');

        $data = array();
        $data[] = array(
            'customer_guid' => $customer_guid,
        );
        // echo json_encode($data);die;
        $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/einv_main';
        // echo $url;die;
        // die;
        $cuser_name = 'ADMIN';
        $cuser_pass = '1234';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        $result = curl_exec($ch);
        $output = json_decode($result);
        // $status = json_encode($output);
        // print_r($output->result);die;
        // echo $result;die;
        //close connection
        curl_close($ch);
        if(isset($output->status))
        {
            if($output->status == "true")
            {
                $json_data = $output->result;
                // echo $json_data;die;
                // echo json_encode($json_data);die;
                $success_array = array();
                $success_array_json = array();
                $shoot_acknowledge = 0;
                foreach($json_data as $row)
                {
                    $shoot_acknowledge++;
                    $database2 = 'b2b_hub';
                    $table2 = 'einv_main';
                    $einv_guid = $row->einv_guid;
                    $trans_guid = $row->trans_guid;

                    $check_before_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE einv_guid = '$einv_guid'");

                    if($check_before_insert->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'einv_main','$einv_guid','value inserted to HQ by fetched from b2b again','HQ_grab',NOW())");
                        $success_array['type'] = 'einv_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'success';
                        $success_array_json[] = $success_array;
                        continue;
                    }

                    $data_insert = array(
                        'trans_guid' => $row->trans_guid,
                        'einv_guid' => $row->einv_guid,
                        'customer_guid' => $row->customer_guid,
                        'refno' => $row->refno,
                        'einvno' => $row->einvno,
                        'invno' => $row->invno,
                        'dono' => $row->dono,
                        'einv_generated_date' => $row->einv_generated_date,
                        'inv_date' => $row->inv_date,
                        'gr_date' => $row->gr_date,
                        'revision' => $row->revision,
                        'total_excl_tax' => $row->total_excl_tax,
                        'tax_amount' => $row->tax_amount,
                        'total_incl_tax' => $row->total_incl_tax,
                        'posted' => $row->posted,
                        'posted_at' => $row->posted_at,
                        'posted_by' => $row->posted_by,
                        'converted' => $row->converted,
                        'converted_at' => $row->converted_at,
                        'created_at' => $row->created_at,
                        'created_by' => $row->created_by,
                        'updated_at' => $row->updated_at,
                        'updated_by' => $row->updated_by,
                        'exported' => 0,
                        'exported_at' => '',
                        'imported' => 1,
                        'imported_at' => $this->db->query("SELECT NOW() as now")->row('now'),
                    );

                    $this->db->insert($database2.'.'.$table2,$data_insert);

                    $check_after_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE einv_guid = '$einv_guid'");

                    if($check_after_insert->num_rows() > 0)
                    {
                        $success_array['type'] = 'einv_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'success';
                    }
                    else
                    {
                        $success_array['type'] = 'einv_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'fail';
                    }
                    $success_array_json[] = $success_array;
                }// foreach json data

                if($shoot_acknowledge > 0)
                {
                    $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/acknowledge_update';
                    // echo $url;die;
                    // die;
                    $cuser_name = 'ADMIN';
                    $cuser_pass = '1234';

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                    curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($success_array_json));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                    $result = curl_exec($ch);
                    $output = json_decode($result);
                    // $status = json_encode($output);
                    // print_r($output->result);die;
                    // echo $result;die;
                    //close connection
                    curl_close($ch);
                    if(isset($output->status))
                    {
                        if($output->status == "true")
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Success"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                        else
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Unsuccess"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                    }
                    else
                    {
                        $reponse = array(
                            'status' => "false",
                            'message' => "No response from b2b"
                        );
                        echo json_encode($reponse);
                        die;
                    }
                }//close shoot acknowledge
                else
                {
                    $reponse = array(
                            'status' => "true",
                            'message' => "No Record to execute"
                    );
                    echo json_encode($reponse);
                    die;
                }
                // echo json_encode($success_array_json);die;
            }
            else
            {
                $reponse = array(
                    'status' => "false",
                    'message' => "Got response but false"
                );
                echo json_encode($reponse);
                die;
            }
        }
        else
        {
            $reponse = array(
                'status' => "false",
                'message' => "No response from b2b"
            );
            echo json_encode($reponse);
            die;
        } 
    }

    public function einv_child_get()
    {
        $database1 = 'rest_api';
        $table1 = 'run_once_config';

        $customer_guid_array = $this->db->query("SELECT * FROM $database1.$table1 WHERE active = 1");

        if($customer_guid_array->num_rows() <= 0)
        {
            $reponse = array(
                'status' => "false",
                'message' => "Customer Guid Not Setup"
            );
            echo json_encode($reponse);
            die;
        }

        $customer_guid = $customer_guid_array->row('customer_guid');

        $data = array();
        $data[] = array(
            'customer_guid' => $customer_guid,
        );
        // echo json_encode($data);die;
        $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/einv_child';
        // echo $url;die;
        // die;
        $cuser_name = 'ADMIN';
        $cuser_pass = '1234';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        $result = curl_exec($ch);
        $output = json_decode($result);
        // $status = json_encode($output);
        // print_r($output->result);die;
        // echo $result;die;
        //close connection
        curl_close($ch);
        if(isset($output->status))
        {
            if($output->status == "true")
            {
                $json_data = $output->result;
                // echo $json_data;die;
                // echo json_encode($json_data);die;
                $success_array = array();
                $success_array_json = array();
                $shoot_acknowledge = 0;
                foreach($json_data as $row)
                {
                    $shoot_acknowledge++;
                    $database2 = 'b2b_hub';
                    $table2 = 'einv_child';
                    $einv_guid = $row->einv_guid;
                    $trans_guid = $row->trans_guid;
                    $child_guid = $row->child_guid;

                    $check_before_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE child_guid = '$child_guid'");

                    if($check_before_insert->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'einv_child','$child_guid','value inserted to HQ by fetched from b2b again','HQ_grab',NOW())");
                        $success_array['type'] = 'einv_child';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'success';
                        $success_array_json[] = $success_array;
                        continue;
                    }

                    $data_insert = array(
                        'trans_guid' => $row->trans_guid,
                        'child_guid' => $row->child_guid,
                        'einv_guid' => $row->einv_guid,
                        'line' => $row->line,
                        'itemtype' => $row->itemtype,
                        'itemlink' => $row->itemlink,
                        'itemcode' => $row->itemcode,
                        'barcode' => $row->barcode,
                        'description' => $row->description,
                        'packsize' => $row->packsize,
                        'qty' => $row->qty,
                        'uom' => $row->uom,
                        'unit_price_before_disc' => $row->unit_price_before_disc,
                        'item_discount_description' => $row->item_discount_description,
                        'item_disc_amt' => $row->item_disc_amt,
                        'total_bill_disc_prorated' => $row->total_bill_disc_prorated,
                        'total_amt_excl_tax' => $row->total_amt_excl_tax,
                        'total_tax_amt' => $row->total_tax_amt,
                        'total_amt_incl_tax' => $row->total_amt_incl_tax,
                        'checked' => $row->checked,
                        'checked_at' => $row->checked_at,
                        'checked_by' => $row->checked_by,
                        'created_at' => $row->created_at,
                        'created_by' => $row->created_by,
                        'updated_at' => $row->updated_at,
                        'updated_by' => $row->updated_by,
                        'exported' => 0,
                        'exported_at' => '',
                        'imported' => 1,
                        'imported_at' => $this->db->query("SELECT NOW() as now")->row('now'),
                        'posted' => $row->posted,
                        'posted_at' => $row->posted_at,
                        'posted_by' => $row->posted_by,
                    );
                    // echo json_encode($data_insert);die;

                    $this->db->insert($database2.'.'.$table2,$data_insert);

                    $check_after_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE child_guid = '$child_guid'");

                    if($check_after_insert->num_rows() > 0)
                    {
                        $success_array['type'] = 'einv_child';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'success';
                    }
                    else
                    {
                        $success_array['type'] = 'einv_child';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'fail';
                    }
                    $success_array_json[] = $success_array;
                }// foreach json data

                if($shoot_acknowledge > 0)
                {
                    $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/acknowledge_update';
                    // echo $url;die;
                    // die;
                    $cuser_name = 'ADMIN';
                    $cuser_pass = '1234';

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                    curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($success_array_json));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                    $result = curl_exec($ch);
                    $output = json_decode($result);
                    // $status = json_encode($output);
                    // print_r($output->result);die;
                    // echo $result;die;
                    //close connection
                    curl_close($ch);
                    if(isset($output->status))
                    {
                        if($output->status == "true")
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Success"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                        else
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Unsuccess"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                    }
                    else
                    {
                        $reponse = array(
                            'status' => "false",
                            'message' => "No response from b2b"
                        );
                        echo json_encode($reponse);
                        die;
                    }
                }//close shoot acknowledge
                else
                {
                    $reponse = array(
                            'status' => "true",
                            'message' => "No Record to execute"
                    );
                    echo json_encode($reponse);
                    die;
                }
                // echo json_encode($success_array_json);die;
            }
            else
            {
                $reponse = array(
                    'status' => "false",
                    'message' => "Got response but false"
                );
                echo json_encode($reponse);
                die;
            }
        }
        else
        {
            $reponse = array(
                'status' => "false",
                'message' => "No response from b2b"
            );
            echo json_encode($reponse);
            die;
        } 
    }

    public function ecn_main_get()
    {
        $database1 = 'rest_api';
        $table1 = 'run_once_config';

        $customer_guid_array = $this->db->query("SELECT * FROM $database1.$table1 WHERE active = 1");

        if($customer_guid_array->num_rows() <= 0)
        {
            $reponse = array(
                'status' => "false",
                'message' => "Customer Guid Not Setup"
            );
            echo json_encode($reponse);
            die;
        }

        $customer_guid = $customer_guid_array->row('customer_guid');

        $data = array();
        $data[] = array(
            'customer_guid' => $customer_guid,
        );
        // echo json_encode($data);die;
        $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/ecn_main';
        // echo $url;die;
        // die;
        $cuser_name = 'ADMIN';
        $cuser_pass = '1234';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        $result = curl_exec($ch);
        $output = json_decode($result);
        // $status = json_encode($output);
        // print_r($output->result);die;
        // echo $result;die;
        //close connection
        curl_close($ch);
        if(isset($output->status))
        {
            if($output->status == "true")
            {
                $json_data = $output->result;
                // echo $json_data;die;
                // echo json_encode($json_data);die;
                $success_array = array();
                $success_array_json = array();
                $shoot_acknowledge = 0;
                foreach($json_data as $row)
                {
                    $shoot_acknowledge++;
                    $database2 = 'b2b_hub';
                    $table2 = 'ecn_main';
                    $ecn_guid = $row->ecn_guid;
                    $trans_guid = $row->trans_guid;

                    $check_before_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE ecn_guid = '$ecn_guid'");
                    // echo json_encode($check_before_insert->result());die;

                    if($check_before_insert->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'ecn_main','$ecn_guid','value inserted to HQ by fetched from b2b again','HQ_grab',NOW())");
                        $success_array['type'] = 'ecn_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'success';
                        $success_array_json[] = $success_array;
                        continue;
                    }

                    $data_insert = array(
                        'trans_guid' => $row->trans_guid,
                        'customer_guid' => $row->customer_guid,
                        'ecn_guid' => $row->ecn_guid,
                        'status' => $row->status,
                        'refno' => $row->refno,
                        'type' => $row->type,
                        'ext_doc1' => $row->ext_doc1,
                        'ext_date1' => $row->ext_date1,
                        'ecn_generated_date' => $row->ecn_generated_date,
                        'amount' => $row->amount,
                        'tax_rate' => $row->tax_rate,
                        'tax_amount' => $row->tax_amount,
                        'total_incl_tax' => $row->total_incl_tax,
                        'revision' => $row->revision,
                        'posted' => $row->posted,
                        'posted_at' => $row->posted_at,
                        'posted_by' => $row->posted_by,
                        'created_at' => $row->created_at,
                        'created_by' => $row->created_by,
                        'updated_at' => $row->updated_at,
                        'updated_by' => $row->updated_by,
                        'exported' => 0,
                        'exported_at' => '',
                        'imported' => 1,
                        'imported_at' => $this->db->query("SELECT NOW() as now")->row('now'),
                    );
                    // echo json_encode($data_insert);die;

                    $this->db->insert($database2.'.'.$table2,$data_insert);

                    $check_after_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE ecn_guid = '$ecn_guid'");

                    if($check_after_insert->num_rows() > 0)
                    {
                        $success_array['type'] = 'ecn_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'success';
                    }
                    else
                    {
                        $success_array['type'] = 'ecn_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'fail';
                    }
                    $success_array_json[] = $success_array;
                }// foreach json data
                // echo json_encode($success_array_json);die;
                if($shoot_acknowledge > 0)
                {
                    $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/acknowledge_update';
                    // echo $url;die;
                    // die;
                    $cuser_name = 'ADMIN';
                    $cuser_pass = '1234';

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                    curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($success_array_json));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                    $result = curl_exec($ch);
                    $output = json_decode($result);
                    // $status = json_encode($output);
                    // print_r($output->result);die;
                    // echo $result;die;
                    //close connection
                    curl_close($ch);
                    if(isset($output->status))
                    {
                        if($output->status == "true")
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Success"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                        else
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Unsuccess"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                    }
                    else
                    {
                        $reponse = array(
                            'status' => "false",
                            'message' => "No response from b2b"
                        );
                        echo json_encode($reponse);
                        die;
                    }
                }//close shoot acknowledge
                else
                {
                    $reponse = array(
                            'status' => "true",
                            'message' => "No Record to execute"
                    );
                    echo json_encode($reponse);
                    die;
                }
                // echo json_encode($success_array_json);die;
            }
            else
            {
                $reponse = array(
                    'status' => "false",
                    'message' => "Got response but false"
                );
                echo json_encode($reponse);
                die;
            }
        }
        else
        {
            $reponse = array(
                'status' => "false",
                'message' => "No response from b2b"
            );
            echo json_encode($reponse);
            die;
        } 
    }

    public function ecn_child_get()
    {
        $database1 = 'rest_api';
        $table1 = 'run_once_config';

        $customer_guid_array = $this->db->query("SELECT * FROM $database1.$table1 WHERE active = 1");

        if($customer_guid_array->num_rows() <= 0)
        {
            $reponse = array(
                'status' => "false",
                'message' => "Customer Guid Not Setup"
            );
            echo json_encode($reponse);
            die;
        }

        $customer_guid = $customer_guid_array->row('customer_guid');

        $data = array();
        $data[] = array(
            'customer_guid' => $customer_guid,
        );
        // echo json_encode($data);die;
        $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/ecn_child';
        // echo $url;die;
        // die;
        $cuser_name = 'ADMIN';
        $cuser_pass = '1234';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        $result = curl_exec($ch);
        $output = json_decode($result);
        // $status = json_encode($output);
        // print_r($output->result);die;
        // echo $result;die;
        //close connection
        curl_close($ch);
        if(isset($output->status))
        {
            if($output->status == "true")
            {
                $json_data = $output->result;
                // echo $json_data;die;
                // echo json_encode($json_data);die;
                $success_array = array();
                $success_array_json = array();
                $shoot_acknowledge = 0;
                foreach($json_data as $row)
                {
                    $shoot_acknowledge++;
                    $database2 = 'b2b_hub';
                    $table2 = 'ecn_child';
                    $ecn_guid = $row->ecn_guid;
                    $trans_guid = $row->trans_guid;
                    $child_guid = $row->child_guid;

                    $check_before_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE child_guid = '$child_guid'");

                    if($check_before_insert->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'ecn_child','$child_guid','value inserted to HQ by fetched from b2b again','HQ_grab',NOW())");
                        $success_array['type'] = 'ecn_child';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'success';
                        $success_array_json[] = $success_array;
                        continue;
                    }

                    $data_insert = array(
                        'trans_guid' => $row->trans_guid,
                        'child_guid' => $row->child_guid,
                        'customer_guid' => $row->customer_guid,
                        'ecn_guid' => $row->ecn_guid,
                        'status' => $row->status,
                        'refno' => $row->refno,
                        'refno_dn' => $row->refno_dn,
                        'transtype' => $row->transtype,
                        'location' => $row->location,
                        'line' => $row->line,
                        'itemcode' => $row->itemcode,
                        'barcode' => $row->barcode,
                        'description' => $row->description,
                        'qty' => $row->qty,
                        'inv_qty' => $row->inv_qty,
                        'inv_netunitprice' => $row->inv_netunitprice,
                        'inv_totalprice' => $row->inv_totalprice,
                        'supplier' => $row->supplier,
                        'invno' => $row->invno,
                        'dono' => $row->dono,
                        'porefno' => $row->porefno,
                        'title2' => $row->title2,
                        'notes' => $row->notes,
                        'pounitprice' => $row->pounitprice,
                        'invactcost' => $row->invactcost,
                        'netunitprice' => $row->netunitprice,
                        'pototal' => $row->pototal,
                        'articleno' => $row->articleno,
                        'packsize' => $row->packsize,
                        'variance_amt' => $row->variance_amt,
                        'reason' => $row->reason,
                        'tax_amount' => $row->tax_amount,
                        'total_gross' => $row->total_gross,
                        'created_at' => $row->created_at,
                        'created_by' => $row->created_by,
                        'updated_at' => $row->updated_at,
                        'updated_by' => $row->updated_by,
                        'posted' => $row->posted,
                        'posted_at' => $row->posted_at,
                        'posted_by' => $row->posted_by,
                        'exported' => 0,
                        'exported_at' => '',
                        'imported' => 1,
                        'imported_at' => $this->db->query("SELECT NOW() as now")->row('now'),
                    );
                    // echo json_encode($data_insert);die;

                    $this->db->insert($database2.'.'.$table2,$data_insert);

                    $check_after_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE child_guid = '$child_guid'");

                    if($check_after_insert->num_rows() > 0)
                    {
                        $success_array['type'] = 'ecn_child';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'success';
                    }
                    else
                    {
                        $success_array['type'] = 'ecn_child';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'fail';
                    }
                    $success_array_json[] = $success_array;
                }// foreach json data

                if($shoot_acknowledge > 0)
                {
                    $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/acknowledge_update';
                    // echo $url;die;
                    // die;
                    $cuser_name = 'ADMIN';
                    $cuser_pass = '1234';

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                    curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($success_array_json));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                    $result = curl_exec($ch);
                    $output = json_decode($result);
                    // $status = json_encode($output);
                    // print_r($output->result);die;
                    // echo $result;die;
                    //close connection
                    curl_close($ch);
                    if(isset($output->status))
                    {
                        if($output->status == "true")
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Success"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                        else
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Unsuccess"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                    }
                    else
                    {
                        $reponse = array(
                            'status' => "false",
                            'message' => "No response from b2b"
                        );
                        echo json_encode($reponse);
                        die;
                    }
                }//close shoot acknowledge
                else
                {
                    $reponse = array(
                            'status' => "true",
                            'message' => "No Record to execute"
                    );
                    echo json_encode($reponse);
                    die;
                }
                // echo json_encode($success_array_json);die;
            }
            else
            {
                $reponse = array(
                    'status' => "false",
                    'message' => "Got response but false"
                );
                echo json_encode($reponse);
                die;
            }
        }
        else
        {
            $reponse = array(
                'status' => "false",
                'message' => "No response from b2b"
            );
            echo json_encode($reponse);
            die;
        } 
    }

    public function consignment_e_invoice_main_get()
    {
        $database1 = 'rest_api';
        $table1 = 'run_once_config';

        $customer_guid_array = $this->db->query("SELECT * FROM $database1.$table1 WHERE active = 1");

        if($customer_guid_array->num_rows() <= 0)
        {
            $reponse = array(
                'status' => "false",
                'message' => "Customer Guid Not Setup"
            );
            echo json_encode($reponse);
            die;
        }

        $customer_guid = $customer_guid_array->row('customer_guid');

        $data = array();
        $data[] = array(
            'customer_guid' => $customer_guid,
        );
        // echo json_encode($data);die;
        $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/consignment_einv_main';
        // echo $url;die;
        // die;
        $cuser_name = 'ADMIN';
        $cuser_pass = '1234';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        $result = curl_exec($ch);
        $output = json_decode($result);
        // $status = json_encode($output);
        // print_r($output->result);die;
        // echo $result;die;
        //close connection
        curl_close($ch);
        if(isset($output->status))
        {
            if($output->status == "true")
            {
                $json_data = $output->result;
                // echo $json_data;die;
                // echo json_encode($json_data);die;
                $success_array = array();
                $success_array_json = array();
                $shoot_acknowledge = 0;
                foreach($json_data as $row)
                {
                    $shoot_acknowledge++;
                    $database2 = 'b2b_hub';
                    $table2 = 'consignment_e_invoice_main';
                    $einv_guid = $row->einv_guid;

                    $check_before_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE einv_guid = '$einv_guid'");
                    // echo json_encode($check_before_insert->result());die;

                    if($check_before_insert->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'consignment_e_invoice_main','$einv_guid','value inserted to HQ by fetched from b2b again','HQ_grab',NOW())");
                        $success_array['type'] = 'consignment_e_invoice_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $einv_guid;
                        $success_array['status'] = 'success';
                        $success_array_json[] = $success_array;
                        continue;
                    }

                    $data_insert = array(
                        'einv_guid' => $row->einv_guid,
                        'supcus_code' => $row->supcus_code,
                        'unique_key' => $row->unique_key,
                        'einv_date' => $row->einv_date,
                        'b2b_inv_no' => $row->b2b_inv_no,
                        'b2b_inv_date' => $row->b2b_inv_date,
                        'gen_doc_date' => $row->gen_doc_date,
                        'total_amt' => $row->total_amt,
                        'total_incl_tax' => $row->total_incl_tax,
                        'total_child_count' => $row->total_child_count,
                        'created_at' => $row->created_at,
                        'created_by' => $row->created_by,
                        'updated_at' => $row->updated_at,
                        'updated_by' => $row->updated_by,
                        'export_account' => $row->export_account,
                        'exported_at' => $row->exported_at,
                        'exported_by' => $row->exported_by,
                        'exported_to_hq' => 0,
                        'exported_to_hq_at' => '',
                        'exported_to_hq_by' => '',
                        'imported' => 1,
                        'imported_at' => $this->db->query("SELECT NOW() as now")->row('now'),
                    );
                    // echo json_encode($data_insert);die;

                    $this->db->insert($database2.'.'.$table2,$data_insert);

                    $check_after_insert = $this->db->query("SELECT * FROM $database2.$table2 WHERE einv_guid = '$einv_guid'");

                    if($check_after_insert->num_rows() > 0)
                    {
                        $success_array['type'] = 'consignment_e_invoice_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $einv_guid;
                        $success_array['status'] = 'success';
                    }
                    else
                    {
                        $success_array['type'] = 'consignment_e_invoice_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $einv_guid;
                        $success_array['status'] = 'fail';
                    }
                    $success_array_json[] = $success_array;
                }// foreach json data
                // echo json_encode($success_array_json);die;
                if($shoot_acknowledge > 0)
                {
                    $url = $this->b2b_ip.'/rest_b2b/index.php/E_document_b2b_response/acknowledge_update';
                    // echo $url;die;
                    // die;
                    $cuser_name = 'ADMIN';
                    $cuser_pass = '1234';

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                    curl_setopt($ch, CURLOPT_USERPWD, "$cuser_name:$cuser_pass");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($success_array_json));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                    $result = curl_exec($ch);
                    $output = json_decode($result);
                    // $status = json_encode($output);
                    // print_r($output->result);die;
                    // echo $result;die;
                    //close connection
                    curl_close($ch);
                    if(isset($output->status))
                    {
                        if($output->status == "true")
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Success"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                        else
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "Unsuccess"
                            );
                            echo json_encode($reponse);
                            die;
                        }
                    }
                    else
                    {
                        $reponse = array( 
                            'status' => "false",
                            'message' => "No response from b2b"
                        );
                        echo json_encode($reponse);
                        die;
                    }
                }//close shoot acknowledge
                else
                {
                    $reponse = array(
                            'status' => "true",
                            'message' => "No Record to execute"
                    );
                    echo json_encode($reponse);
                    die;
                }
                // echo json_encode($success_array_json);die;
            }
            else
            {
                $reponse = array(
                    'status' => "false",
                    'message' => "Got response but false"
                );
                echo json_encode($reponse);
                die;
            }
        }
        else
        {
            $reponse = array(
                'status' => "false",
                'message' => "No response from b2b"
            );
            echo json_encode($reponse);
            die;
        } 
    }
}
