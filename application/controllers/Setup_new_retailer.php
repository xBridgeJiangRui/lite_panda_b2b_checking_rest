<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Setup_new_retailer extends REST_controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
    }

    public function index_post()
    {
        $AppPOST = json_decode(file_get_contents('php://input'), true);
        $date_start = $this->input->post('date_start');

        $status = '';
        $message = [];

        //first check database
        $check_database = $this->db->query("SHOW DATABASES LIKE 'rest_api'");

        if ($check_database->num_rows() == 0) {
            // create new database
            $create_database = $this->db->query("CREATE DATABASE rest_api");

            if ($create_database == 1) {
                $status = 'true';
                $message['database'] = 'Successful Create New Database rest_api';
            } else {
                $status = 'false';
                $message['database'] = 'Unsuccessful Create New Database rest_api';
            }
        } else {
            $status = 'true';
            $message['database'] = 'Database rest_api already exit';
        }

        if ($status == 'true' || $status == '') {
            // check table exit or no
            $this->db->query("USE rest_api");
            $check_table = $this->db->query("SHOW TABLES LIKE 'run_once_config'");

            if ($check_table->num_rows() == 0) {
                // create new table
                $create_table = $this->db->query("CREATE TABLE `run_once_config` (
                    `customer_guid` varchar(32) DEFAULT NULL,
                    `date_start` date DEFAULT NULL,
                    `active` smallint(6) DEFAULT '1'
                  )");

                if ($create_table == 1) {
                    $status = 'true';
                    $message['table_run_once_config'] = 'Successful Create New Table run_once_config at rest_api';
                } else {
                    $status = 'false';
                    $message['table_run_once_config'] = 'Unsuccessful Create New Table run_once_config at rest_api';
                }
            } else {
                $status = 'true';
                $message['table_run_once_config'] = 'Table rest_api.run_once_config already exit';
            }
        }

        // insert run once config data
        if ($status == 'true' || $status == '') {

            $check_run_once_config = $this->db->query("SELECT *
            FROM rest_api.run_once_config AS a")->result_array();

            if (count($check_run_once_config) > 0) {
                $status = 'true';
                $message['insert_run_once_config'] = 'Data already exit';
            } else {
                // insert data rest_api.run_once_config
                $insert_data = $this->db->query("REPLACE INTO rest_api.`run_once_config`
                VALUES(REPLACE(UPPER(UUID()),'-',''),'$date_start','1')");

                if ($insert_data == 1) {
                    $status = 'true';
                    $message['insert_run_once_config'] = 'Successful insert data run_once_config at rest_api';
                } else {
                    $status = 'false';
                    $message['insert_run_once_config'] = 'Unsuccessful insert data run_once_config at rest_api';
                }
            }
        } else {
            $status = 'true';
            $message['insert_run_once_config'] = 'Data already exit';
        }

        // check b2b_config
        if ($status == 'true' || $status == '') {
            $this->db->query("USE rest_api");
            // check table exit or no
            $check_table = $this->db->query("SHOW TABLES LIKE 'b2b_config'");

            if ($check_table->num_rows() == 0) {
                // create new table
                $create_table = $this->db->query("CREATE TABLE rest_api.`b2b_config` (
                    `guid` varchar(32) NOT NULL,
                    `code` varchar(50) DEFAULT NULL,
                    `value` varchar(255) DEFAULT NULL,
                    `isactive` smallint(6) DEFAULT NULL,
                    `created_by` varchar(32) DEFAULT NULL,
                    `created_at` datetime DEFAULT NULL,
                    `updated_by` varchar(32) DEFAULT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`guid`)
                  )");

                if ($create_table == 1) {
                    $status = 'true';
                    $message['table_b2b_config'] = 'Successful Create New Table b2b_config at rest_api';
                } else {
                    $status = 'false';
                    $message['table_b2b_config'] = 'Unsuccessful Create New Table b2b_config at rest_api';
                }
            } else {
                $status = 'true';
                $message['table_b2b_config'] = 'Table rest_api.b2b_config already exit';
            }
        }

        // 
        if ($status == 'true' || $status == '') {

            $check_b2b_config = $this->db->query("SELECT *
            FROM rest_api.b2b_config AS a")->result_array();

            if (count($check_b2b_config) > 0) {
                $this->db->query("DELETE FROM rest_api.b2b_config");
            }

            // insert data rest_api.b2b_config
            $data[] = array(
                'guid' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                'code' => 'HTTP',
                'value' => 'https://',
                'isactive' => '1',
                'created_by' => 'b2b_system',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_by' => 'b2b_system',
                'updated_at' => date("Y-m-d H:i:s"),
            );
            $data[] = array(
                'guid' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                'code' => 'IP',
                'value' => 'api.xbridge.my',
                'isactive' => '1',
                'created_by' => 'b2b_system',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_by' => 'b2b_system',
                'updated_at' => date("Y-m-d H:i:s"),
            );
            $data[] = array(
                'guid' => $this->db->query("SELECT UPPER(REPLACE(UUID(),'-','')) as guid")->row('guid'),
                'code' => 'PORT',
                'value' => '',
                'isactive' => '1',
                'created_by' => 'b2b_system',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_by' => 'b2b_system',
                'updated_at' => date("Y-m-d H:i:s"),
            );
            $insert_data_b2b_config = $this->db->replace_batch('rest_api.b2b_config', $data);

            if ($insert_data_b2b_config > 1) {
                $status = 'true';
                $message['insert_b2b_config'] = 'Successful insert data b2b_config at rest_api';
            } else {
                $status = 'false';
                $message['insert_b2b_config'] = 'Unsuccessful insert data b2b_config at rest_api';
            }
        } else {
            $status = 'true';
            $message['insert_b2b_config'] = 'Data already exit';
        }

        $json = array(
            'status' => $status,
            'message' => $message,
        );
        $this->response($json);
    }

    public function cp_set_branch_to_b2b_get()
    {

        $data = $this->db->query("SELECT a.`customer_guid`,b.`BRANCH_GUID`,b.`BRANCH_CODE`,b.`BRANCH_NAME`,b.`BRANCH_ADD`,b.`BRANCH_TEL`,b.`BRANCH_FAX`,b.`SCRIPT_TABLENAME`,b.`SET_RATIO`,b.`SET_PRIORITY`,b.`CREATED_AT`,b.`CREATED_BY`,b.`UPDATED_AT`,b.`UPDATED_BY`,b.`SET_SUPPLIER_CODE`,b.`SET_CUSTOMER_CODE`,b.`OUTLET_CODE_ACC`,b.`OUTLET_NO_ACC`,b.`sshHostname`,b.`sshPort`,b.`sshUser`,b.`sshPass`,b.`databaset_default`,b.`mysql_user`,b.`mysql_pass`,b.`sshCDestHost`,b.`sshCDestPort`,b.`sshCSourcePort`,b.`script_database_tablename`,b.`PeriodEndOn`,b.`LastRecalDateTime`,b.`RecalTime`,b.`branch_desc`
        FROM rest_api.`run_once_config` AS a
        INNER JOIN backend.cp_set_branch AS b");

        $username = 'admin'; //get from rest.php
        $password = '1234'; //get from rest.php

        $prefix_url = $this->db->query("SELECT GROUP_CONCAT(a.value SEPARATOR '') as prefix_url
        FROM rest_api.b2b_config AS a")->row('prefix_url');

        //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/cp_set_branch2';
        $url = $prefix_url . '/rest_api/index.php/panda_b2b/cp_set_branch2';
        // $url = '';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data->result()));

        $result = curl_exec($ch);
        // echo $result;die;
        $output =  json_decode($result);
        $status = $output->message;
        if ($status == "true") {

            $json = array(
                'status' => TRUE,
                'message' => 'Success'
            );
        } else {

            $json = array(
                'status' => FALSE,
                'message' => 'Unsuccess'
            );
        }

        $this->response($json);
    }

    public function supcus_to_b2b_get()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '848M');

        $data = $this->db->query("SELECT (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,          
        Type,Code,Name,Add1,Add2,Add3,City,State,Country,Postcode,Tel,Fax,Contact,Mobile,Term,PaymentDay,BankAcc,CreditLimit,MonitorCredit,Remark,                                                     
        PointBF,PointCumm,PointSum,Member,memberno,DATE_FORMAT(ExpiryDate, '%Y-%m-%d') AS ExpiryDate,CycleVisit,DeliveryTerm,DATE_FORMAT(IssuedStamp, '%Y-%m-%d %H:%i:%s') AS IssuedStamp,
        DATE_FORMAT(LastStamp, '%Y-%m-%d %H:%i:%s') AS LastStamp,dadd1,dadd2,dadd3,dattn,dtel,dfax,email,AccountCode,AccPDebit,AccPCredit,                 
        CalDueDateby,supcusGroup,region,pcode,Add4,Contact2,DAdd4,poprice_method,stockday_min,stockday_max,stock_returnable,stock_return_cost_type,AutoClosePO,Consign,Block,exclude_orderqty_control,supcus_guid,
        acc_no,Ord_W1,Ord_W2,Ord_W3,Ord_W4,Ord_D1,Ord_D2,Ord_D3,Ord_D4,Ord_D5,Ord_D6,Ord_D7,Rec_Method_1,Rec_Method_2,Rec_Method_3,
        Rec_Method_4,Rec_Method_5,pur_expiry_days,grn_baseon_pocost,Ord_set_global,rules_code,po_negative_qty,grpo_variance_qty,grpo_variance_price,price_include_tax,delivery_early_in_day,delivery_late_in_day,tax_code,
        DATE_FORMAT(gst_start_date, '%Y-%m-%d') AS gst_start_date,gst_no,reg_no,name_reg,multi_tax_rate,grn_allow_negative_margin,rebate_as_inv,discount_as_inv,
        poso_line_max,apply_actual_cn,PromoRebateAsTaxInv,PurchaseDNAmtAsTaxInv,member_accno,RoundingAdjust
        FROM backend.supcus");

        $prefix_url = $this->db->query("SELECT GROUP_CONCAT(a.value SEPARATOR '') as prefix_url
        FROM rest_api.b2b_config AS a")->row('prefix_url');

        $username = 'admin'; //get from rest.php
        $password = '1234'; //get from rest.php

        //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/supcus2';
        $url = $prefix_url . '/rest_api/index.php/panda_b2b/supcus3';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data->result()));

        $result = curl_exec($ch);
        //echo $result;die;
        $output =  json_decode($result);
        $status = $output->message;
        if ($status == "true") {
            $json = array(
                'status' => TRUE,
                'message' => 'Success'
            );
        } else {
            $json = array(
                'status' => FALSE,
                'message' => 'Unsuccess'
            );
        }
        $this->response($json);
    }


    public function insert_new_acc_from_backend_post()
    {
        $isactive = $this->input->post("isactive");
        $file_path = $this->input->post("file_path");
        $rest_url = $this->input->post("rest_url");
        $trial_mode = $this->input->post("trial_mode");
        $logo = $this->input->post("logo");
        $jasper_url = $this->input->post("jasper_url");
        $public_ip = $this->input->post("public_ip");
        $public_ip_2 = $this->input->post("public_ip_2");
        $seq = $this->input->post("seq");
        $row_seq = $this->input->post("row_seq");
        $username = $this->input->post("username");

        $status = '';
        $message = '';

        $acc_guid = $this->db->query("SELECT a.customer_guid
        FROM rest_api.run_once_config AS a
        WHERE a.active = '1'
        LIMIT 1")->row('customer_guid');

        $company_info = $this->db->query("SELECT *
        FROM backend.companyprofile AS a")->result_array();

        $prefix_url = $this->db->query("SELECT GROUP_CONCAT(a.value SEPARATOR '') as prefix_url
        FROM rest_api.b2b_config AS a")->row('prefix_url');

        $data = array(
            'acc_guid' => $acc_guid,
            'isactive' => $isactive,
            'acc_name' => $company_info[0]['CompanyName'],
            'acc_regno' => $company_info[0]['comp_reg_no'],
            'acc_gstno' => $company_info[0]['gst_no'],
            'acc_taxcode' => $company_info[0]['gst_no'],
            'acc_add1' => $company_info[0]['Address1'],
            'acc_add2' => $company_info[0]['Address2'],
            'acc_add3' => $company_info[0]['Address3'],
            'acc_postcode' => $company_info[0]['postalcode'],
            'acc_state' => $company_info[0]['state'],
            'acc_doc_name' => $company_info[0]['CompanyName'],
            'file_path' => $file_path,
            'rest_url' => $rest_url,
            'trial_mode' => $trial_mode,
            'logo' =>  $logo,
            'jasper_url' => $jasper_url,
            'seq' => $seq,
            'row_seq' =>  $row_seq,
            'public_ip' => $public_ip,
            'public_ip_2' => $public_ip_2,
            'username' => $username,
        );

        $username = 'admin'; //get from rest.php
        $password = '1234'; //get from rest.php

        $url = $prefix_url . '/rest_b2b/index.php/Setup_new_retailer/insert_new_acc';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);
        //echo $result;die;
        $output =  json_decode($result, true);

        $status = $output['status'];

        if ($status == "true" || $status == true) {
            $json = array(
                'status' => TRUE,
                'message' => 'Success'
            );
        } else {
            $json = array(
                'status' => FALSE,
                'message' => 'Unsuccess'
            );
        }

        $json = array(
            'status' =>  $status,
            'message' => $message,
        );

        $this->response($json);
    }
}
