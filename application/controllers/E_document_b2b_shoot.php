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
                    $grmain_refno = $row->refno;

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

                    $check_backend_table = $this->db->query("SELECT * FROM backend.grmain WHERE refno = '$grmain_refno' AND b2b_sup_doc_no = '' ");

                    if($check_backend_table->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'einv_main','$einv_guid','b2b sup doc no empty','HQ_grab',NOW())");
                        $success_array['type'] = 'einv_main';
                        $success_array['customer_guid'] = $customer_guid; 
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'fail';
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
                    $grmain_refno = $row->refno;

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
                    
                    $check_backend_table = $this->db->query("SELECT * FROM backend.grmain WHERE refno = '$grmain_refno' AND b2b_sup_doc_no = '' ");

                    if($check_backend_table->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'einv_child','$child_guid','b2b sup doc no empty','HQ_grab',NOW())");
                        $success_array['type'] = 'einv_main';
                        $success_array['customer_guid'] = $customer_guid; 
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'fail';
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
                    $grda_refno = $row->refno;

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

                    $check_backend_table = $this->db->query("SELECT * FROM backend.grmain_dncn WHERE refno = '$grda_refno' AND ext_doc_no = '' ");

                    if($check_backend_table->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'ecn_main','$ecn_guid','ext doc no empty','HQ_grab',NOW())");
                        $success_array['type'] = 'ecn_main';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'fail';
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
                    $grda_refno = $row->refno;

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

                    $check_backend_table = $this->db->query("SELECT * FROM backend.grmain_dncn WHERE refno = '$grda_refno' AND ext_doc_no = '' ");

                    if($check_backend_table->num_rows() > 0)
                    {
                        $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'ecn_child','$child_guid','ext doc no empty','HQ_grab',NOW())");
                        $success_array['type'] = 'ecn_child';
                        $success_array['customer_guid'] = $customer_guid;
                        $success_array['refno'] = $trans_guid;
                        $success_array['status'] = 'fail';
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

        $check_einvoice_main_empty = $this->db->query("SELECT * FROM b2b_hub.consignment_e_invoice_main a INNER JOIN b2b_hub.consignment_e_invoices b ON a.supcus_code = b.supcus_code AND a.b2b_inv_date = b.b2b_inv_date AND a.b2b_inv_no = b.b2b_inv_no WHERE ( b.einv_guid IS NULL OR b.einv_guid = '' )");

        if($check_einvoice_main_empty->num_rows() > 0)
        {
            $this->db->query("UPDATE b2b_hub.consignment_e_invoice_main a INNER JOIN b2b_hub.consignment_e_invoices b ON a.supcus_code = b.supcus_code AND a.b2b_inv_date = b.b2b_inv_date AND a.b2b_inv_no = b.b2b_inv_no SET b.einv_guid = a.einv_guid WHERE (b.einv_guid IS NULL OR b.einv_guid  = '')");
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
                        $check_update_data = $check_before_insert->row('exported_to_hq');

                        if($check_update_data == '99')
                        {
                            $new_amt = $row->total_amt;
                            $count_data_from_b2b = $row->total_child_count;

                            $update_data = $this->db->query("UPDATE $database2.$table2 SET total_amt = '$new_amt' , total_incl_tax = '$new_amt' , total_child_count = '$count_data_from_b2b' WHERE einv_guid = '$einv_guid' ");

                            $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'consignment_e_invoice_main','$einv_guid','value updated due to missing generate','HQ_grab',NOW())");
                            $success_array['type'] = 'consignment_e_invoice_main';
                            $success_array['customer_guid'] = $customer_guid;
                            $success_array['refno'] = $einv_guid;
                            $success_array['status'] = 'success';
                            $success_array_json[] = $success_array;
                            continue;
                        }
                        else
                        {
                            $this->db->query("INSERT INTO $database2.error_log(trans_guid,module,refno,message,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'consignment_e_invoice_main','$einv_guid','value inserted to HQ by fetched from b2b again','HQ_grab',NOW())");
                            $success_array['type'] = 'consignment_e_invoice_main';
                            $success_array['customer_guid'] = $customer_guid;
                            $success_array['refno'] = $einv_guid;
                            $success_array['status'] = 'success';
                            $success_array_json[] = $success_array;
                            continue;
                        }
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

                    $check_einvoice_main_empty = $this->db->query("SELECT * FROM b2b_hub.consignment_e_invoice_main a INNER JOIN b2b_hub.consignment_e_invoices b ON a.supcus_code = b.supcus_code AND a.b2b_inv_date = b.b2b_inv_date AND a.b2b_inv_no = b.b2b_inv_no WHERE ( b.einv_guid IS NULL OR b.einv_guid = '' )");

                    if($check_einvoice_main_empty->num_rows() > 0)
                    {
                        $this->db->query("UPDATE b2b_hub.consignment_e_invoice_main a INNER JOIN b2b_hub.consignment_e_invoices b ON a.supcus_code = b.supcus_code AND a.b2b_inv_date = b.b2b_inv_date AND a.b2b_inv_no = b.b2b_inv_no SET b.einv_guid = a.einv_guid WHERE (b.einv_guid IS NULL OR b.einv_guid  = '')");
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

    //jr created 2022-07-14 for flow b2b to hq update backend documents
    public function e_invoice_generated_get()
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

        $data = array(
            'customer_guid' => $customer_guid,
        );
        //echo json_encode($data);die;
        $url = $this->b2b_ip.'/rest_api/index.php/Panda_b2b/einv_generated_data';
        //echo $url;die;
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
        //echo $result;die;
        //close connection
        curl_close($ch);

        if(isset($output->status))
        {
            if($output->status == "true")
            {
                $json_data = $output->result;
                //print_r($json_data); die;
                foreach($json_data as $row)
                {
                    $refno = $row->refno;
                    $einvno = $row->einvno;
                    $inv_date = $row->inv_date;

                    $update_status = $this->db->query("UPDATE backend.grmain SET `status` = 'COMPLETED',b2b_sup_doc_no = '$einvno',ext_doc_date = '$inv_date' WHERE RefNo = '$refno'");

                    //$data_array = [];
                    if($update_status > 0)
                    {
                        $data_array[] = array(
                            'refno' => $refno,
                            'customer_guid' => $customer_guid
                        );
                    }
                    else
                    {   
                        $data_array = [];
                    }
                }
                
                if(count($data_array) > 0 )
                {
                    //print_r(count($data_array)); die;

                    $url = $this->b2b_ip.'/rest_api/index.php/Panda_b2b/update_b2b_acc_flag';
                    //echo $url;die;
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
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_array));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                    $result = curl_exec($ch);
                    $output = json_decode($result);
                    // $status = json_encode($output);
                    // print_r($output->result);die;
                    //echo $result;die;
                    //close connection
                    curl_close($ch);

                    if(isset($output->status))
                    {
                        if($output->status == "true")
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "EINV Success Acknowledge Acc Flag to B2b."
                            );
                        }
                        else
                        {
                            $reponse = array(
                                'status' => "false",
                                'message' => "EINV Failed Acknowledge Acc Flag to B2b."
                            );
                        }
                    }
                    else
                    {
                        $reponse = array(
                            'status' => "false",
                            'message' => "EINV No response from b2b acknowledge update acc flag"
                        );
                        echo json_encode($reponse);
                        die;
                    }
                } 
                
                //print_r($data_array); die;
                if($update_status > 0)
                {
                    $reponse = array(
                        'status' => "true",
                        'message' => "EINV Success Update"
                    );
                }
                else
                {
                    $reponse = array(
                        'status' => "false",
                        'message' => "EINV Failed to Update"
                    );
                }

                echo json_encode($reponse);
                die;

            }
            else
            {
                $reponse = array(
                    'status' => "false",
                    'message' => "EINV Got response but Empty Data"
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

    public function e_cn_generated_get()
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

        $data = array(
            'customer_guid' => $customer_guid,
        );
        //echo json_encode($data);die;
        $url = $this->b2b_ip.'/rest_api/index.php/Panda_b2b/ecn_generated_data';
        //echo $url;die;
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
        //echo $result;die;
        //close connection
        curl_close($ch);

        if(isset($output->status))
        {
            if($output->status == "true")
            {
                $json_data = $output->result;
                //print_r($json_data); die;
                foreach($json_data as $row)
                {
                    $refno = $row->ecn_refno;
                    $ecn_sup_no = $row->ecn_sup_no;
                    $ecn_sup_date = $row->ecn_sup_date;
                    $transtype = $row->transtype;

                    $update_status = $this->db->query("UPDATE backend.grmain_dncn SET ext_doc_no = '$ecn_sup_no',ext_doc_date = '$ecn_sup_date' WHERE RefNo = '$refno' AND transtype = '$transtype'");

                    //$data_array = [];
                    if($update_status > 0)
                    {
                        $data_array[] = array(
                            'refno' => $refno,
                            'customer_guid' => $customer_guid,
                            'transtype' => $transtype,
                        );
                    }
                    else
                    {   
                        $data_array = '';
                    }
                }

                if(count($data_array) > 0 )
                {
                    //print_r(count($data_array)); die;

                    $url = $this->b2b_ip.'/rest_api/index.php/Panda_b2b/update_b2b_ecn_acc_flag';
                    //echo $url;die;
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
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_array));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                    $result = curl_exec($ch);
                    $output = json_decode($result);
                    // $status = json_encode($output);
                    // print_r($output->result);die;
                    //echo $result;die;
                    //close connection
                    curl_close($ch);

                    if(isset($output->status))
                    {
                        if($output->status == "true")
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "ECN Success Acknowledge Acc Flag to B2b."
                            );
                        }
                        else
                        {
                            $reponse = array(
                                'status' => "false",
                                'message' => "ECN Failed Acknowledge Acc Flag to B2b."
                            );
                        }
                    }
                    else
                    {
                        $reponse = array(
                            'status' => "false",
                            'message' => "ECN No response from b2b acknowledge update acc flag"
                        );
                        echo json_encode($reponse);
                        die;
                    }
                } 
                
                //print_r($data_array); die;
                if($update_status > 0)
                {
                    $reponse = array(
                        'status' => "true",
                        'message' => "ECN Success Update"
                    );
                }
                else
                {
                    $reponse = array(
                        'status' => "false",
                        'message' => "ECN Failed to Update"
                    );
                }

                echo json_encode($reponse);
                die;

            }
            else
            {
                $reponse = array(
                    'status' => "false",
                    'message' => "ECN Got response but Empty Data"
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

    public function e_prdn_generated_get()
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

        $data = array(
            'customer_guid' => $customer_guid,
        );
        //echo json_encode($data);die;
        $url = $this->b2b_ip.'/rest_api/index.php/Panda_b2b/prdn_generated_data';
        //echo $url;die;
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
        //echo $result;die;
        //close connection
        curl_close($ch);

        if(isset($output->status))
        {
            if($output->status == "true")
            {
                $json_data = $output->result;
                //print_r($json_data); die;
                foreach($json_data as $row)
                {
                    $refno = $row->refno;
                    $ecn_sup_no = $row->ecn_sup_no;
                    $ecn_sup_date = $row->ecn_sup_date;

                    $update_status = $this->db->query("UPDATE backend.dbnotemain SET b2b_sup_doc_no = '$ecn_sup_no',ext_doc_date = '$ecn_sup_date' , b2b_status = 'COMPLETED'  WHERE RefNo = '$refno' ");

                    //$data_array = [];
                    if($update_status > 0)
                    {
                        $data_array[] = array(
                            'refno' => $refno,
                            'customer_guid' => $customer_guid,
                        );
                    }
                    else
                    {   
                        $data_array = '';
                    }
                }

                if(count($data_array) > 0 )
                {
                    //print_r(count($data_array)); die;

                    $url = $this->b2b_ip.'/rest_api/index.php/Panda_b2b/update_b2b_prdn_acc_flag';
                    //echo $url;die;
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
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_array));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                    $result = curl_exec($ch);
                    $output = json_decode($result);
                    // $status = json_encode($output);
                    // print_r($output->result);die;
                    //echo $result;die;
                    //close connection
                    curl_close($ch);

                    if(isset($output->status))
                    {
                        if($output->status == "true")
                        {
                            $reponse = array(
                                'status' => "true",
                                'message' => "PRDNCN Success Acknowledge Acc Flag to B2b."
                            );
                        }
                        else
                        {
                            $reponse = array(
                                'status' => "false",
                                'message' => "PRDNCN Failed Acknowledge Acc Flag to B2b."
                            );
                        }
                    }
                    else
                    {
                        $reponse = array(
                            'status' => "false",
                            'message' => "PRDNCN No response from b2b acknowledge update acc flag"
                        );
                        echo json_encode($reponse);
                        die;
                    }
                } 
                
                //print_r($data_array); die;
                if($update_status > 0)
                {
                    $reponse = array(
                        'status' => "true",
                        'message' => "PRDNCN Success Update"
                    );
                }
                else
                {
                    $reponse = array(
                        'status' => "false",
                        'message' => "PRDNCN Failed to Update"
                    );
                }

                echo json_encode($reponse);
                die;

            }
            else
            {
                $reponse = array(
                    'status' => "false",
                    'message' => "PRDNCN Got response but Empty Data"
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
    // end here 2022-07-14
}
