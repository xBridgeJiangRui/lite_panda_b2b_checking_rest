<?php

require(APPPATH.'/libraries/REST_Controller.php');

class Upload_data_temp extends REST_Controller{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $this->b2b_ip = 'http://52.163.112.202';
        // $this->b2b_ip = 'http://127.0.0.1';
    } 

    public function purchase_order_get()
    {
        $data = $this->db->query("SELECT refno as RefNo,'PO' as TYPE FROM b2b_hub.reupload WHERE type = 'pomain' AND uploaded = '2'");

        //print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
             //echo $row->RefNo;die; 
            $data2 = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            IF(closed = '1', 'Closed', IF(cancel ='1', 'Cancelled',  '')) AS STATUS,
            RefNo,
            DATE_FORMAT(PODate, '%Y-%m-%d') AS PODate,
            DATE_FORMAT(DeliverDate, '%Y-%m-%d') AS DeliverDate,
            DATE_FORMAT(DueDate, '%Y-%m-%d') AS DueDate,
            DATE_FORMAT(IssueStamp,  '%Y-%m-%d %H:%i:%s') AS IssueStamp,
            IssuedBy,
            DATE_FORMAT(LastStamp, '%Y-%m-%d %H:%i:%s') AS LastStamp,
            Dept,
            Location,
            ApprovedBy,
            SCode,
            SName AS SName,
            STerm,
            STel,
            SFax,
            Remark AS Remark,
            SubTotal1,
            Discount1,
            Discount1Type,
            SubTotal2,
            Discount2,
            Discount2Type,
            Total,
            BillStatus,
            AccStatus,
            Closed,
            Amendment,
            Completed,
            Disc1Percent,
            Disc2Percent,
            SubDeptCode,
            postby,
            DATE_FORMAT(postdatetime, '%Y-%m-%d %H:%i:%s') AS postdatetime,
            CalDueDateby,
            DATE_FORMAT(expiry_date, '%Y-%m-%d') AS expiry_date,
            pur_expiry_days,
            hq_update,
            cp_main_guid,
            AutoClosePO,
            stockday_min,
            stockday_max,
            send,
            send_remark AS send_remark,
            DATE_FORMAT(send_at, '%Y-%m-%d %H:%i:%s') AS  send_at,
            send_by,
            rejected,
            rejected_remark,
            DATE_FORMAT(rejected_at, '%Y-%m-%d %H:%i:%s') AS  rejected_at,
            rejected_by,
            approved,
            approved_remark AS approved_remark,
            DATE_FORMAT(approved_at, '%Y-%m-%d %H:%i:%s') AS  approved_at,
            approved_by,
            loc_group,
            run_cost,
            rebate_amt,
            dn_amt,
            in_kind,
            cross_ref,
            cross_ref_module,
            hq_issue,
            gst_tax_sum,
            tax_code_purchase,
            total_include_tax,
            gst_tax_rate,
            price_include_tax,
            surchg_tax_sum,
            tax_inclusive,
            doc_name_reg,
            ibt,
            multi_tax_code,
            refno2,
            discount_as_inv,
            ibt_gst,
            rebate_as_inv,
            uploaded,
            DATE_FORMAT(uploaded_at, '%Y-%m-%d %H:%i:%s') AS uploaded_at,
            unpost,
            DATE_FORMAT(unpost_at, '%Y-%m-%d %H:%i:%s') AS unpost_at,
            unpost_by,
            cancel,
            DATE_FORMAT(cancel_at, '%Y-%m-%d %H:%i:%s') AS cancel_at,
            cancel_by,
            cancel_reason,
            b2b_status            
            FROM backend.`pomain`
            WHERE RefNo = '$row->RefNo'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            // $url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/pomain2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/pomain2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE b2b_hub.reupload SET uploaded = 3 WHERE refno = '$row->RefNo' AND type = 'pomain'"); 
            }
            else
            {
                    //$run = $this->db->query("UPDATE backend.pomain SET hq_update = '3' WHERE RefNo = '$row->RefNo'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }  
    
    public function goods_received_note_get()
    {
        $data = $this->db->query("SELECT refno as RefNo,'GRN' as TYPE FROM b2b_hub.reupload WHERE type = 'grmain' AND uploaded = '2'");

        // print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            // echo $row->RefNo;die; 
            $data2 = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            '' AS STATUS,
            RefNo,
            Location,
            DONo AS DONo,
            InvNo AS InvNo,
            DATE_FORMAT(DocDate, '%Y-%m-%d') AS DocDate,
            DATE_FORMAT(GRDate, '%Y-%m-%d') AS GRDate,
            DATE_FORMAT(IssueStamp, '%Y-%m-%d %H:%i:%s') AS IssueStamp,
            DATE_FORMAT(LastStamp, '%Y-%m-%d %H:%i:%s') AS LastStamp,
            `Code`,
            NAME AS `Name`,
            Term,
            Receivedby AS Receivedby,
            Remark AS Remark,
            BillStatus,
            AccStatus,
            DATE_FORMAT(DueDate, '%Y-%m-%d') AS DueDate,
            Total,
            Closed,
            Subtotal1,
            Discount1,
            Discount1Type,
            Subtotal2,
            Discount2,
            Discount2Type,
            Disc1Percent,
            Disc2Percent,
            Cancelled,
            DOState,
            InvState,
            InvRefno,
            subdept,
            CalcCost,
            SubDeptCode,
            consign,
            postby,
            DATE_FORMAT(postdatetime, '%Y-%m-%d %H:%i:%s') AS postdatetime,
            unpostby,
            DATE_FORMAT(unpostdatetime, '%Y-%m-%d %H:%i:%s') AS unpostdatetime,
            CalDueDateby,
            hq_update,
            EXPORT_ACCOUNT,
            DATE_FORMAT(EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,
            EXPORT_BY,
            InvAmount_Vendor,
            InvSurchargeDisc_Vendor,
            InvNetAmt_Vendor,
            loc_group,
            pay_by_invoice,
            rebate_amt,
            ibt,
            dn_amt,
            m_trans_type,
            in_kind,
            rebate,
            gst_tax_sum,
            tax_code_purchase,
            gst_tax_rate,
            gst_tax_sum_inv,
            InvSurcharge,
            price_include_tax,
            surchg_tax_sum,
            surchg_tax_sum_inv,
            total_include_tax,
            doc_name_reg AS doc_name_reg,
            multi_tax_code,
            refno2 AS refno2,
            gst_adj,
            rounding_adj,
            discount_as_inv,
            rebate_as_inv,
            ibt_gst,
            DATE_FORMAT(acc_post_date, '%Y-%m-%d') AS acc_post_date,
            uploaded,
            DATE_FORMAT(uploaded_at, '%Y-%m-%d %H:%i:%s') AS uploaded_at,
            input_amt_exc_tax,
            input_gst,
            input_amt_inc_tax,
            amt_matched,
            ibt_qty_actual,
            ibt_qty_grda,
            cross_ref,
            cross_ref_module
            FROM backend.`grmain`
            WHERE RefNo = '$row->RefNo'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            // $url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/grmain2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/grmain2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE b2b_hub.reupload SET uploaded = 3 WHERE refno = '$row->RefNo' AND type = 'grmain'"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.grmain SET hq_update = '3' WHERE RefNo = '$row->RefNo'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }
    
    public function grda_get()
    {
        $data = $this->db->query("SELECT a.refno AS RefNo, b.`transtype` AS transtype FROM b2b_hub.reupload a INNER JOIN backend.`grmain_dncn` b ON a.refno = b.refno 
WHERE a.type = 'grmain_dncn' AND a.uploaded = 2");

        // print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            // echo $row->RefNo;die; 
            $data2 = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            '' AS `status`,
            b.location AS location,
            a.RefNo,
            a.VarianceAmt,
            DATE_FORMAT(a.Created_at, '%Y-%m-%d %H:%i:%s') AS Created_at,
            a.Created_by,
            a.Created_by AS Created_by,
            DATE_FORMAT(a.Updated_at, '%Y-%m-%d %H:%i:%s') AS Updated_at,
            a.Updated_by AS Updated_by,
            a.hq_update,
            a.EXPORT_ACCOUNT,
            DATE_FORMAT(a.EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,
            a.EXPORT_BY,
            a.transtype,
            a.share_cost,
            a.gst_tax_sum,
            a.gst_adjust,
            a.gl_code,
            a.tax_invoice,
            a.ap_sup_code,
            a.refno2 AS refno2,
            a.rounding_adj,
            a.sup_cn_no AS sup_cn_no,
            DATE_FORMAT(a.sup_cn_date, '%Y-%m-%d') AS sup_cn_date,
            DATE_FORMAT(a.dncn_date, '%Y-%m-%d') AS dncn_date,
            DATE_FORMAT(a.dncn_date_acc, '%Y-%m-%d') AS dncn_date_acc
            FROM backend.`grmain_dncn` a 
            INNER JOIN backend.grmain b ON a.RefNo = b.RefNo
            WHERE a.RefNo = '$row->RefNo' AND a.transtype = '$row->transtype'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            // $url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/grda2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/grda2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE b2b_hub.reupload SET uploaded = 3 WHERE refno = '$row->RefNo' AND type = 'grmain_dncn'"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.grmain_dncn SET hq_update = '3' WHERE RefNo = '$row->RefNo' AND transtype = '$row->transtype'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    } 

    public function dbnotemain_get()
    {
        $data = $this->db->query("SELECT refno as RefNo,'DEBIT' as TYPE FROM b2b_hub.reupload WHERE type = 'DEBIT' AND uploaded = '2'");

        //print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            //echo $row->RefNo;die; 
            $data2 = $this->db->query("SELECT 
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            IF(closed = '1', 'Closed',  '') AS STATUS,
            TYPE,
            RefNo,
            Location,
            DocNo AS DocNo,
            DATE_FORMAT(DocDate,'%Y-%m-%d') AS DocDate,
            DATE_FORMAT(IssueStamp, '%Y-%m-%d %H:%i:%s') AS IssueStamp,
            DATE_FORMAT(LastStamp, '%Y-%m-%d %H:%i:%s') AS LastStamp,
            PONo,
            SCType,
            CODE,
            NAME AS NAME,
            Term,
            Issuedby AS Issuedby,
            Remark AS Remark,
            BillStatus,
            AccStatus,
            DATE_FORMAT(DueDate,  '%Y-%m-%d') AS DueDate,
            Amount,
            Closed,
            SubDeptCode,
            postby,
            DATE_FORMAT(postdatetime, '%Y-%m-%d %H:%i:%s') AS postdatetime,
            Consign,
            EXPORT_ACCOUNT,
            EXPORT_AT,
            EXPORT_BY,
            hq_update,
            locgroup,
            ibt,
            SubTotal1,
            Discount1,
            Discount1Type,
            SubTotal2,
            Discount2,
            Discount2Type,
            gst_tax_sum,
            tax_code_purchase,
            sup_cn_no AS sup_cn_no,
            DATE_FORMAT(sup_cn_date,'%Y-%m-%d') AS sup_cn_date,
            doc_name_reg AS doc_name_reg,
            gst_tax_rate,
            multi_tax_code,
            refno2 AS refno2,
            surchg_tax_sum,
            gst_adj,
            rounding_adj,
            unpostby,
            DATE_FORMAT(unpostdatetime, '%Y-%m-%d %H:%i:%s') AS unpostdatetime,
            ibt_gst,
            DATE_FORMAT(acc_posting_date,'%Y-%m-%d') AS acc_posting_date,
            RoundAdjNeed,
            stock_collected AS stock_collected,
            date_collected AS date_collected,
            stock_collected_by as stock_collected_by
            FROM backend.dbnotemain
            WHERE Type = '$row->TYPE' AND RefNo = '$row->RefNo'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            // $url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/dbnotemain2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/dbnotemain2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE b2b_hub.reupload SET uploaded = 3 WHERE refno = '$row->RefNo' AND type = 'DEBIT'"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.dbnotemain set hq_update = '3' WHERE Type = '$row->TYPE' AND RefNo = '$row->RefNo'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }  

    public function cnnotemain_get()
    {
        $data = $this->db->query("SELECT refno as RefNo,'CN' as TYPE FROM b2b_hub.reupload WHERE type = 'CN' AND uploaded = '2'");

        //print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            // echo $row->TYPE;die; 
            $data2 = $this->db->query("SELECT 
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            IF(closed = '1', 'Closed',  '') AS STATUS,
            TYPE,
            RefNo,
            Location,
            DocNo,
            DATE_FORMAT(DocDate,'%Y-%m-%d') AS DocDate,
            DATE_FORMAT(IssueStamp, '%Y-%m-%d %H:%i:%s') AS IssueStamp,
            DATE_FORMAT(LastStamp, '%Y-%m-%d %H:%i:%s') AS LastStamp,
            PONo,
            SCType,
            CODE,
            NAME,
            term,
            Issuedby,
            Remark,
            BillStatus,
            AccStatus,
            DATE_FORMAT(DueDate,  '%Y-%m-%d') AS DueDate,
            Amount,
            Closed,
            postby,
            DATE_FORMAT(postdatetime, '%Y-%m-%d %H:%i:%s') AS postdatetime,
            subdeptcode,
            hq_update,
            EXPORT_ACCOUNT,
            EXPORT_AT,
            EXPORT_BY,
            Consign,
            locgroup,
            ibt,
            SubTotal1,
            Discount1,
            Discount1Type,
            SubTotal2,
            Discount2,
            Discount2Type,
            gst_tax_sum,
            tax_code_purchase,
            sup_cn_no,
            DATE_FORMAT(sup_cn_date,'%Y-%m-%d') AS sup_cn_date,
            refno2,
            gst_tax_rate,
            multi_tax_code,
            doc_name_reg,
            ibt_gst,
            gst_adj,
            rounding_adj,
            surchg_tax_sum,
            unpostby,
            DATE_FORMAT(unpostdatetime, '%Y-%m-%d %H:%i:%s') AS unpostdatetime,
            DATE_FORMAT(acc_posting_date,'%Y-%m-%d') AS acc_posting_date,
            RoundAdjNeed
            FROM backend.cnnotemain
            WHERE Type = '$row->TYPE' AND RefNo = '$row->RefNo'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/cnnotemain2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/cnnotemain2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;

            if($status == "true")
            {
                    $run = $this->db->query("UPDATE b2b_hub.reupload SET uploaded = 3 WHERE refno = '$row->RefNo' AND type = 'CN' ;"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.cnnotemain SET hq_update = '3' WHERE type = '$row->TYPE' AND refno = '$row->RefNo'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }  
     
    public function cndn_amt_get()
    {
        $data = $this->db->query("SELECT 
	  a.refno AS RefNo,
	  b.`cndn_guid` AS cndn_guid
	FROM
	  b2b_hub.reupload a 
	  INNER JOIN backend.cndn_amt b 
	    ON a.refno = b.`refno`
	WHERE a.type = 'cndn_amt' 
	  AND a.uploaded = 2");

        //print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {

            $data2 = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            ''  AS STATUS,
            cndn_guid,
            trans_type,
            loc_group,
            location,
            refno,
            docno,
            DATE_FORMAT(DocDate,'%Y-%m-%d') AS DocDate,
            CODE,
            NAME,
            tax_code,
            remark,
            term,
            amount,
            gst_tax_sum,
            amount_include_tax,
            cndn_group,
            DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
            created_by,
            DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') AS updated_at,
            updated_by,
            posted,
            DATE_FORMAT(posted_at, '%Y-%m-%d %H:%i:%s') AS posted_at,
            posted_by,
            Consign,
            sup_cn_no,
            DATE_FORMAT(sup_cn_date,'%Y-%m-%d') AS sup_cn_date,
            doc_name_reg,
            gst_tax_rate,
            multi_tax_code,
            refno2,
            gst_adj,
            rounding_adj,
            unpostby,
            DATE_FORMAT(unpostdatetime, '%Y-%m-%d %H:%i:%s') AS unpostdatetime,
            ibt_gst,
            subdeptcode,
            EXPORT_ACCOUNT,
            DATE_FORMAT(EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,
            EXPORT_BY,
            hq_update,
            ibt,
            DATE_FORMAT(acc_posting_date, '%Y-%m-%d %H:%i:%s') AS acc_posting_date,
            trans_type_acc,
            RoundAdjNeed
            FROM backend.cndn_amt
            WHERE cndn_guid = '$row->cndn_guid'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/cndnamt2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/cndnamt2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            //echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE b2b_hub.reupload SET uploaded = 3 WHERE refno = '$row->RefNo' AND type = 'cndn_amt'"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.cndn_amt SET hq_update = '3' WHERE cndn_guid = '$row->cndn_guid'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }  
   
    public function promo_taxinv_get()
    {
        $data = $this->db->query("SELECT
        (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
        ''  AS STATUS,
        taxinv_guid,
        loc_group,
        loc_group_issue,
        seq,
        DATE_FORMAT(docdate,'%Y-%m-%d') AS docdate,
        term,
        DATE_FORMAT(datedue,'%Y-%m-%d') AS datedue,
        tax_inclusive,
        sup_code,
        sup_name,
        total_bf_tax,
        tax_code_supply,
        gst_tax_rate,
        gst_value,
        total_af_tax,
        gst_adj,
        rounding_adj,
        total_net,
        remark AS remark,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
        created_by,
        DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') AS updated_at,
        updated_by,
        posted,
        posted_at,
        posted_by,
        inv_refno,
        promo_refno,
        promo_guid,
        AR_cuscode,
        gl_code, 
        EXPORT_ACCOUNT,
        DATE_FORMAT(EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,
        EXPORT_BY,
        hq_update,
        refno,
        refno_line,
        uploaded,
        DATE_FORMAT(uploaded_at, '%Y-%m-%d %H:%i:%s') AS uploaded_at,
        issued_by_hq 
        FROM backend.promo_taxinv
        WHERE docdate >= DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 3 MONTH)
        AND posted = '1'  
        AND uploaded = '1'  
        LIMIT 100");

        //print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            // echo $row->RefNo;die; 
            $data2 = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            ''  AS STATUS,
            taxinv_guid,
            loc_group,
            loc_group_issue,
            seq,
            DATE_FORMAT(docdate,'%Y-%m-%d') AS docdate,
            term,
            DATE_FORMAT(datedue,'%Y-%m-%d') AS datedue,
            tax_inclusive,
            sup_code,
            sup_name,
            total_bf_tax,
            tax_code_supply,
            gst_tax_rate,
            gst_value,
            total_af_tax,
            gst_adj,
            rounding_adj,
            total_net,
            remark AS remark,
            DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
            created_by,
            DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') AS updated_at,
            updated_by,
            posted,
            posted_at,
            posted_by,
            inv_refno,
            promo_refno,
            promo_guid,
            AR_cuscode,
            gl_code, 
            EXPORT_ACCOUNT,
            DATE_FORMAT(EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,
            EXPORT_BY,
            hq_update,
            refno,
            refno_line,
            uploaded,
            DATE_FORMAT(uploaded_at, '%Y-%m-%d %H:%i:%s') AS uploaded_at,
            issued_by_hq 
            FROM backend.promo_taxinv
            WHERE taxinv_guid = '$row->taxinv_guid'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/promo_taxinv2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/promo_taxinv2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE backend.promo_taxinv SET hq_update = '3', uploaded = '2' WHERE taxinv_guid = '$row->taxinv_guid'"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.promo_taxinv SET hq_update = '3', uploaded = '2' WHERE taxinv_guid = '$row->taxinv_guid'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    } 

    public function dischemain_get()
    {
        $data = $this->db->query("SELECT
        (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
        ''  AS STATUS,
        taxinv_guid,
        loc_group,
        loc_group_issue,
        seq,
        DATE_FORMAT(docdate,'%Y-%m-%d') AS docdate,
        term,
        DATE_FORMAT(datedue,'%Y-%m-%d') AS datedue,
        tax_inclusive,
        sup_code,
        sup_name,
        total_bf_tax,
        tax_code_supply,
        gst_tax_rate,
        gst_value,
        total_af_tax,
        gst_adj,
        rounding_adj,
        total_net,
        remark AS remark,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
        created_by,
        DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') AS updated_at,
        updated_by,
        posted,
        DATE_FORMAT(posted_at, '%Y-%m-%d %H:%i:%s') AS posted_at,
        posted_by,
        inv_refno,
        refno,
        refno_line,
        AR_cuscode,
        gl_code, 
        EXPORT_ACCOUNT,
        DATE_FORMAT(EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,
        EXPORT_BY,
        hq_update, 
        uploaded,
        DATE_FORMAT(uploaded_at, '%Y-%m-%d %H:%i:%s') AS uploaded_at,
        division 
        FROM backend.discheme_taxinv
        WHERE docdate >= DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 3 MONTH)
        AND posted = '1'  
        AND uploaded = '1'   
        LIMIT 100");

        //print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            // echo $row->RefNo;die; 
            $data2 = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            ''  AS STATUS,
            taxinv_guid,
            loc_group,
            loc_group_issue,
            seq,
            DATE_FORMAT(docdate,'%Y-%m-%d') AS docdate,
            term,
            DATE_FORMAT(datedue,'%Y-%m-%d') AS datedue,
            tax_inclusive,
            sup_code,
            sup_name,
            total_bf_tax,
            tax_code_supply,
            gst_tax_rate,
            gst_value,
            total_af_tax,
            gst_adj,
            rounding_adj,
            total_net,
            remark AS remark,
            DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
            created_by,
            DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') AS updated_at,
            updated_by,
            posted,
            DATE_FORMAT(posted_at, '%Y-%m-%d %H:%i:%s') AS posted_at,
            posted_by,
            inv_refno,
            refno,
            refno_line,
            AR_cuscode,
            gl_code, 
            EXPORT_ACCOUNT,
            DATE_FORMAT(EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,
            EXPORT_BY,
            hq_update, 
            uploaded,
            DATE_FORMAT(uploaded_at, '%Y-%m-%d %H:%i:%s') AS uploaded_at,
            division 
            FROM backend.discheme_taxinv
            WHERE taxinv_guid = '$row->taxinv_guid'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/discheme_taxinv2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/discheme_taxinv2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE backend.discheme_taxinv SET hq_update = '3', uploaded = '2' WHERE taxinv_guid = '$row->taxinv_guid'"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.discheme_taxinv SET hq_update = '3', uploaded = '2' WHERE taxinv_guid = '$row->taxinv_guid'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    } 

    public function dbnote_batch_get()
    {
        $data = $this->db->query("SELECT 
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            dbnote_guid,
            batch_no,sup_code,
            sup_name,
            created_at,
            created_by,
            updated_at,
            updated_by,
            converted,
            converted_by,
            converted_at,
            canceled,
            canceled_at,
            canceled_by,
            send_print,
            location,
            sub_location,
            loc_group,hq_update,
            posted as posted,
            posted_by as posted_by,
            posted_at as posted_at
            FROM backend.dbnote_batch 
            WHERE hq_update = 0   
            LIMIT 100");

        // print_r($data->result());die;

        foreach($data->result() as $row)
        {
            // echo $row->RefNo;die; 
            $data2 = $this->db->query("SELECT 
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            dbnote_guid,
            batch_no,sup_code,
            sup_name,
            created_at,
            created_by,
            updated_at,
            updated_by,
            converted,
            converted_by,
            converted_at,
            canceled,
            canceled_at,
            canceled_by,
            send_print,
            location,
            sub_location,
            loc_group,hq_update,
            posted as posted,
            posted_by as posted_by,
            posted_at as posted_at,
            Amount,
            gst_tax_sum,
            unpostby,
            unpostdatetime,
            action_date
            FROM backend.dbnote_batch 
            WHERE dbnote_guid = '$row->dbnote_guid'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/dbnotebatch';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/dbnotebatch';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);
            // echo $result;die;
            $output =  json_decode($result);
            $status = $output->message;
            // echo $status;die;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE backend.dbnote_batch SET hq_update = '3' WHERE dbnote_guid = '$row->dbnote_guid'"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.dbnote_batch SET hq_update = '3' WHERE dbnote_guid = '$row->dbnote_guid'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }     

    public function supcus_get()
    {
        $data = $this->db->query("SELECT
        (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,          
        Type,
        Code,                       
        Name,
        Add1,
        Add2,
        Add3,                                        
        City,                       
        State,                      
        Country,                    
        Postcode,                   
        Tel,                        
        Fax,                       
        Contact,                    
        Mobile,                     
        Term,                       
        PaymentDay,                 
        BankAcc,                    
        CreditLimit,                
        MonitorCredit,              
        Remark,                                                     
        PointBF,                    
        PointCumm,                  
        PointSum,                   
        Member,                     
        memberno,
        DATE_FORMAT(ExpiryDate, '%Y-%m-%d') AS ExpiryDate,                   
        CycleVisit,                 
        DeliveryTerm,               
        DATE_FORMAT(IssuedStamp, '%Y-%m-%d %H:%i:%s') AS IssuedStamp,
        DATE_FORMAT(LastStamp, '%Y-%m-%d %H:%i:%s') AS LastStamp,
        dadd1,                                                     
        dadd2,                                                     
        dadd3,                                                     
        dattn,                                                                       
        dtel,                       
        dfax,                       
        email,                      
        AccountCode,                
        AccPDebit,                  
        AccPCredit,                 
        CalDueDateby,               
        supcusGroup,                
        region,                     
        pcode,                      
        Add4,                     
        Contact2,
        DAdd4,                                       
        poprice_method,             
        stockday_min,               
        stockday_max,               
        stock_returnable,           
        stock_return_cost_type,     
        AutoClosePO,                
        Consign,                    
        Block,                      
        exclude_orderqty_control,   
        supcus_guid,                
        acc_no,                     
        Ord_W1,                     
        Ord_W2,                     
        Ord_W3,                     
        Ord_W4,                     
        Ord_D1,                     
        Ord_D2,                     
        Ord_D3,                     
        Ord_D4,                     
        Ord_D5,                     
        Ord_D6,                     
        Ord_D7,                     
        Rec_Method_1,               
        Rec_Method_2,               
        Rec_Method_3,               
        Rec_Method_4,               
        Rec_Method_5,               
        pur_expiry_days,            
        grn_baseon_pocost,          
        Ord_set_global,             
        rules_code,                 
        po_negative_qty,            
        grpo_variance_qty,          
        grpo_variance_price,        
        price_include_tax,          
        delivery_early_in_day,      
        delivery_late_in_day,       
        tax_code,
        DATE_FORMAT(gst_start_date, '%Y-%m-%d') AS gst_start_date,                    
        gst_no,                     
        reg_no,                     
        name_reg,                   
        multi_tax_rate,             
        grn_allow_negative_margin,  
        rebate_as_inv,              
        discount_as_inv,            
        poso_line_max,              
        apply_actual_cn,            
        PromoRebateAsTaxInv,        
        PurchaseDNAmtAsTaxInv,      
        member_accno,               
        RoundingAdjust         
        FROM backend.supcus
        WHERE LEFT(laststamp,10) > DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-%d'), INTERVAL - 7 DAY)");
        
        //print_r($data->num_rows());die;

        $username = 'admin'; //get from rest.php
        $password = '1234'; //get from rest.php

        //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/supcus2';
        $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/supcus2';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data->result()));

        $result = curl_exec($ch);
        //echo $result;die;
        $output =  json_decode($result);
        $status = $output->message;
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
      
    public function cp_set_branch_get()
    {

        $data = $this->db->query("SELECT
        (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
             BRANCH_GUID,
             BRANCH_CODE,
             BRANCH_NAME,
             BRANCH_ADD,
             BRANCH_TEL,
             BRANCH_FAX,
             SCRIPT_TABLENAME,
             SET_RATIO,
             SET_PRIORITY,
             DATE_FORMAT(CREATED_AT, '%Y-%m-%d %H:%i:%s') AS  CREATED_AT,
             CREATED_BY,
             DATE_FORMAT(UPDATED_AT, '%Y-%m-%d %H:%i:%s') AS  UPDATED_AT,
             UPDATED_BY,
             SET_SUPPLIER_CODE,
             SET_CUSTOMER_CODE,
             OUTLET_CODE_ACC,
             sshHostname,
             sshPort,
             sshUser,
             sshPass,
             databaset_default,
             mysql_user,
             mysql_pass,
             sshCDestHost,
             sshCDestPort,
             sshCSourcePort,
             script_database_tablename,
             PeriodEndOn , branch_desc
             FROM backend.cp_set_branch
             WHERE updated_at >=  DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 1 MONTH)");
             //print_r($data->num_rows());die;

                $username = 'admin'; //get from rest.php
                $password = '1234'; //get from rest.php

                //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/cp_set_branch2';
                $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/cp_set_branch2';
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

    public function update_pomain_gr_completed_get()
    {
        $data = $this->db->query("SELECT
        (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
        -- '403810171FA711EA9BB8E4E7491C3E1E' AS customer_guid,
        RefNo,
        hq_update
        FROM backend.`pomain` AS a
        INNER JOIN backend.supcus AS b
        ON a.`SCode` = b.`Code`
        WHERE podate >= DATE_FORMAT((SELECT date_start FROM rest_api.`run_once_config` LIMIT 1),'%Y-%m-%d')
        AND billstatus = '1' 
        AND completed = '1'
        AND hq_update < '3' 
        and expiry_date > curdate() LIMIT 0");

        print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            //echo $row->RefNo;die; 
            $data2 = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            RefNo,
            hq_update
            FROM backend.`pomain` AS a
            INNER JOIN backend.supcus AS b
            ON a.`SCode` = b.`Code`
            WHERE RefNo = '$row->RefNo'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/po_completed2';
            $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/po_completed2';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data2->result()));

            $result = curl_exec($ch);

            $output =  json_decode($result);
            $status = $output->message;
            if($status == "true")
            {
                    $run = $this->db->query("UPDATE backend.pomain set hq_update = '3' WHERE RefNo = '$row->RefNo'"); 
            }
            else
            {
                    // $run = $this->db->query("UPDATE backend.pomain set hq_update = '3' WHERE RefNo = '$row->RefNo'");
            }
        }//close foreach

        $this->response(
            [
                'status' => TRUE,
                'message' => 'Success'
            ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
        ); 
    }

    public function checking_uploaded_po_get()
    {
        $refno = $this->db->query("SELECT (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,Refno FROM backend.pomain WHERE completed = 1 AND hq_update = 0 AND uploaded = 0 AND podate >= (SELECT date_start FROM rest_api.`run_once_config` WHERE active = '1' LIMIT 1) AND laststamp > (SELECT DATE_ADD(NOW(),INTERVAL - 6 MONTH)) LIMIT 150");
        // print_r($refno->result());die;
        $username = 'admin'; //get from rest.php
        $password = '1234'; //get from rest.php

        if($refno->num_rows() == '0')
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'No data found'
                ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
            );
        }

        // foreach ($refno->result() as $row) {
        //     echo $row->Refno;die;
        // }

        // $url = 'http://localhost/rest_api/index.php/panda_b2b/receive_checking_uploaded_po';
        $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/receive_checking_uploaded_po';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($refno->result()));
        $result = curl_exec($ch);
        echo $result;die;
        $output =  json_decode($result);
        $status = $output->status;
        // echo $status;die;
        if($status == "true")
        {
            $refno_array = $output->message;
            // print_r($refno_array);
            foreach($refno->result() as $row2)
            {
                $po_refno = $row2->Refno;
                // echo $po_refno.'<br>';
                if(in_array($po_refno,$refno_array))
                {
                    // echo 1;
                    $this->db->query("UPDATE backend.pomain SET hq_update = 3,uploaded =2  WHERE refno = '$po_refno'");
                }
                else
                {
                    // echo 2;
                    // $this->db->query("UPDATE backend.pomain SET hq_update = 3 WHERE refno = '$po_refno'");
                }
                // echo $row2.'<br>';
            }

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
            foreach($refno->result() as $row2)
            {
                $po_refno = $row2->Refno;

                $this->db->query("UPDATE backend.pomain SET hq_update = 3 WHERE refno = '$po_refno'");
            }

            $this->response(
                [
                    'status' => FALSE,
                    'message' => 'No data return'
                ]
            // $this->Main_model->query_call('Api','login_validation_get', $data)
            );
        }
    }
    
    public function simain_get()

    {

        $data = $this->db->query("SELECT

        (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,

        `RefNo`,

        `DocNo`,

        DATE_FORMAT(InvoiceDate, '%Y-%m-%d') AS InvoiceDate,

        DATE_FORMAT(DeliverDate, '%Y-%m-%d') AS DeliverDate,

        DATE_FORMAT(IssueStamp,  '%Y-%m-%d %H:%i:%s') AS IssueStamp,

        `IssuedBy`,

        DATE_FORMAT(LastStamp,  '%Y-%m-%d %H:%i:%s') AS LastStamp,

        `Code`,

        `Name`,

        `Add1`,

        `Add2`,

        `Add3`,

        `Attn`,

        `term`,

        `Tel`,

        `Fax`,

        `DAdd1`,

        `DAdd2`,

        `DAdd3`,

        `DAttn`,

        `DTel`,

        `DFax`,

        `Remark`,

        `SubTotal1`,

        `Discount1`,

        `Discount1Type`,

        `SubTotal2`,

        `Discount2`,

        `Discount2Type`,

        `Total`,

        `BillStatus`,

        `Disc1Percent`,

        `Disc2Percent`,

        `SubDeptCode`,

        `postby`,

        DATE_FORMAT(postdatetime, '%Y-%m-%d %H:%i:%s') AS postdatetime,

        `deflocation`,

        `AmtAsDescription`,

        `SALESMAN`,

        `EXPORT_ACCOUNT`,

        `hq_update`,

        `CONVERTED_FROM_MODULE`,

        `CONVERTED_FROM_AT`,

        `CONVERTED_FROM_BY`,

        `CONVERTED_FROM_GUID`,

        DATE_FORMAT(EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,

        `EXPORT_BY`,

        `DueDate`,

        `Deliverd_by`,

        `Vehicle_no`,

        `Doc_No`,

        `loc_group`,

        `ibt`,

        `gst_tax_sum`,

        `tax_code_purchase`,

        `tax_code_sales`,

        `total_include_tax`,

        `refno2`,

        `surchg_tax_sum`,

        `doc_name_reg`,

        `gst_tax_rate`,

        `multi_tax_code`,

        `tax_inclusive`,

        DATE_FORMAT(unpostdatetime, '%Y-%m-%d %H:%i:%s') AS unpostdatetime,

        `unpostby`,

        `gst_adj`,

        `ibt_gst`,

        `revision`,

        `member_accno`,

        `UpdateMembersPoint`,

        `PointsSum`,

        `RoundAdjNeed`,

        `rounding_adj`,

        `ibt_complete`,

        `ibt_rec_amt`,

        `cardtype`,

        `TotalTax`,

        `doc_status`,

        `doc_type`,

        `billto_name`,

        `billto_reg_no`,

        `billto_gst`,

        `credit_available`,

        `tran_volume`,

        `tran_weight`,

        `DOutlet_code`,

        `Add4`,

        `DAdd4`,

        `si_paid`,

        `si_point_multiply`

        

        FROM `backend`.`simain`

        WHERE billstatus = 1

        

        LIMIT 1");

 

        print_r($data->num_rows());die;



        foreach($data->result() as $row)

        {

             //echo $row->RefNo;die; 
	    $date = $this->db->query("SELECT NOW() as now")->row('now');

            $data2= $this->db->query("SELECT

                (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,

                `RefNo`,

                `DocNo`,

                DATE_FORMAT(InvoiceDate, '%Y-%m-%d') AS InvoiceDate,

                DATE_FORMAT(DeliverDate, '%Y-%m-%d') AS DeliverDate,

                DATE_FORMAT(IssueStamp,  '%Y-%m-%d %H:%i:%s') AS IssueStamp,

                `IssuedBy`,

                DATE_FORMAT(LastStamp,  '%Y-%m-%d %H:%i:%s') AS LastStamp,

                `Code`,

                `Name`,

                `Add1`,

                `Add2`,

                `Add3`,

                `Attn`,

                `term`,

                `Tel`,

                `Fax`,

                `DAdd1`,

                `DAdd2`,

                `DAdd3`,

                `DAttn`,

                `DTel`,

                `DFax`,

                `Remark`,

                `SubTotal1`,

                `Discount1`,

                `Discount1Type`,

                `SubTotal2`,

                `Discount2`,

                `Discount2Type`,

                `Total`,

                `BillStatus`,

                `Disc1Percent`,

                `Disc2Percent`,

                `SubDeptCode`,

                `postby`,

                DATE_FORMAT(postdatetime, '%Y-%m-%d %H:%i:%s') AS postdatetime,

                `deflocation`,

                `AmtAsDescription`,

                `SALESMAN`,

                `EXPORT_ACCOUNT`,

                `hq_update`,

                `CONVERTED_FROM_MODULE`,

                `CONVERTED_FROM_AT`,

                `CONVERTED_FROM_BY`,

                `CONVERTED_FROM_GUID`,

                DATE_FORMAT(EXPORT_AT, '%Y-%m-%d %H:%i:%s') AS EXPORT_AT,

                `EXPORT_BY`,

                `DueDate`,

                `Deliverd_by`,

                `Vehicle_no`,

                `Doc_No`,

                `loc_group`,

                `ibt`,

                `gst_tax_sum`,

                `tax_code_purchase`,

                `tax_code_sales`,

                `total_include_tax`,

                `refno2`,

                `surchg_tax_sum`,

                `doc_name_reg`,

                `gst_tax_rate`,

                `multi_tax_code`,

                `tax_inclusive`,

                DATE_FORMAT(unpostdatetime, '%Y-%m-%d %H:%i:%s') AS unpostdatetime,

                `unpostby`,

                `gst_adj`,

                `ibt_gst`,

                `revision`,

                `member_accno`,

                `UpdateMembersPoint`,

                `PointsSum`,

                `RoundAdjNeed`,

                `rounding_adj`,

                `ibt_complete`,

                `ibt_rec_amt`,

                `cardtype`,

                `TotalTax`,

                `doc_status`,

                `doc_type`,

                `billto_name`,

                `billto_reg_no`,

                `billto_gst`,

                `credit_available`,

                `tran_volume`,

                `tran_weight`,

                `DOutlet_code`,

                `Add4`,

                `DAdd4`,

                `si_paid`,

                `si_point_multiply`

               

                FROM `backend`.`simain`

                WHERE RefNo = '$row->RefNo'

                ");

            $query1 = $this->db->query("SELECT a.*,b.* FROM

                (SELECT CONCAT(a.CODE,IF(doutlet_code='',CONCAT(' - ',a.NAME),CONCAT('    Customer Outlet  ',doutlet_code))) AS customer,salesman,

                @euser AS USER,

                CONCAT(a.deliverd_by,IF(a.deliverd_by='','','  '),a.vehicle_no) AS delivered_by,

                a.docno AS refno2,

                a.tel,a.fax,a.term,deflocation AS location,

                tran_weight,

                tran_volume,

                IF(tran_weight=0 AND tran_volume=0,'',

                IF(tran_weight<>0 AND tran_volume=0,CONCAT('Total kg ',ROUND(tran_weight,1)),

                IF(tran_weight=0 AND tran_volume<>0,CONCAT('Total m3 ',ROUND(tran_volume,1)),

                CONCAT('KG: ',ROUND(tran_weight,1),'   M3: ',ROUND(tran_volume,1))))) AS total_weight,

                Amtasdescription,

                IF(d.refno IS NULL OR d.refno='',a.refno,d.refno) AS refno,invoicedate,a.deliverdate,

                a.subtotal1,discount1 * -1 AS discount1,a.subtotal2,discount2,a.total,issuestamp,issuedby,postdatetime,postby,a.laststamp,a.remark,

                IF(discount1type=1,'%','$') AS discount1type,IF(discount2type=1,'%','$') AS discount2type,

                IF(a.dadd1='' OR a.dadd1 IS NULL,a.add1,a.dadd1) AS add1,

                IF(a.dadd1='' OR a.dadd1 IS NULL,a.add2,a.dadd2) AS add2,

                IF(a.dadd1='' OR a.dadd1 IS NULL,a.add3,a.dadd3) AS add3,

                IF(a.dadd1='' OR a.dadd1 IS NULL,a.add4,a.dadd4) AS add4,

                c.city,c.state,c.postcode,c.country,

                CONCAT('Tel : ',IF(a.dadd1 IS NULL OR a.dadd1='',a.tel,a.dtel),IF(a.dadd1='' OR a.dadd1 IS NULL,IF(a.fax='' OR a.fax IS NULL,'',

                CONCAT('  Fax : ',a.fax)),CONCAT('  Fax : ',a.dfax))) AS contact,



                IF(converted_from_module='dc_picklist','IBT SALES INVOICE CUM DELIVERY ORDER','SALES INVOICE CUM DELIVERY ORDER') AS title,

                CONCAT('Doc Status : ',IF(billstatus=0,'Unpost','Posted')) AS doc_status,

                CONCAT(a.term, ' - ',b.description) AS termdesc,

                a.deliverd_by,a.vehicle_no,a.doc_no,

                e.refno AS ibt_refno,

                IF(a.billstatus=1,'','XXX') AS chk,IF(a.billstatus=1,'','Document Not Posted') AS chk_1,

                a.refno AS refno_si,

                IF(a.docno='' OR a.docno IS NULL,d.refno,a.docno) AS refno_pick,

                IF(a.ibt=1,'IBT Request','Other Refno') AS docno_title,

                a.doc_name_reg,



                IF(a.ibt=1,'Inter Branch Stock Transfer Outwards',

                IF(consign=1,'Consignment Note',

                IF((SELECT gst_end_date FROM backend.companyprofile)>=invoicedate,'Tax Invoice','Invoice'))) AS title_3,



                IF(billstatus=0,'Draft Copy','') AS draft,



                IF(a.ibt=1,'Refno','Refno') AS inv_title,



                IF(a.ibt=1,IF(a.ibt_gst=0,'Inter Branch Stock Transfer Outwards to','Inter Branch Stock Transfer Outwards to'),

                IF(a.ibt=2,IF(a.ibt_gst=0,'Sales to Inter Company Customer','Sales to Inter Company Customer'),

                IF(g.gst_tax_rate=0,

                'Sales to Registered GST Customer entitled to 0% Tax','Sales to Customer'))) AS title_gst,



                IF(a.ibt=1,'Inter Branch Stock Transfer Outwards Issued By',

                IF(a.ibt=2,'Inter Company Sales Invoice Issued By',

                'Sales Invoice Issued By')) AS title_issue,



                IF(a.ibt=1,'Inter Branch Stock Transfer Outwards Issued By',

                IF(a.ibt=2,'Inter Company Delivery Order Issued By',

                'Deliver Order Issued By')) AS title_issued_do,



                'Delivery Order' AS title_DO,







                CONCAT(a.deflocation,' - ',f.description) AS loc_desc,

                CONCAT(loc_group,IF(loc_group=a.deflocation,'',CONCAT(' (',a.deflocation,')'))) AS outlet_loc,

                IF(loc_group=a.deflocation,'Outlet','Outlet (Location)') AS outlet_title,





                CONCAT('Co Reg No : ',reg_no,

                IF(invoicedate BETWEEN (SELECT gst_start_date FROM backend.companyprofile)

                AND (SELECT gst_end_date FROM backend.companyprofile),

                IF(gst_no='','',CONCAT('    GST Reg No : ',gst_no,

                IF((SELECT COUNT(DISTINCT(gst_tax_code)) AS gst_count 

                FROM backend.sichild a

                INNER JOIN backend.simain b

                ON a.refno=b.refno

                WHERE a.refno='$row->RefNo'

                GROUP BY a.refno)=1 AND a.ibt=0,CONCAT('    Tax Code : ',a.tax_code_purchase),''))),'')) reg_sup,



                IF(a.ibt=1,'IBT Branch Copy',

                IF(a.ibt=2,'Inter Company Copy',

                'Customer Copy')) AS title_supcopy,





                IF(a.billstatus=0,'Posted on',CONCAT('Posted on ',DATE_FORMAT(a.postdatetime,'%d/%m/%y %H:%I:%S'))) AS doc_posted,

                CONCAT('Issued on ',DATE_FORMAT(a.issuestamp,'%d/%m/%y %H:%I:%S')) AS doc_created,



                IF(d.docdate IS NULL,'',CONCAT('Picking List Date  ',DATE_FORMAT(d.docdate,'%d/%m/%y'))) AS pick_date,

                a.refno AS si_refno,

                IF((SELECT set_enable FROM backend.`set_module_features` WHERE module_guid = '0E4FCA0540F211EBB1D2202107091348')=1,

                '***This document is computer generated. No signature is required.***','') AS no_signature





                FROM backend.simain a



                INNER JOIN backend.supcus c

                ON a.CODE=c.CODE



                INNER JOIN backend.location f

                ON a.deflocation=f.CODE



                LEFT JOIN backend.set_gst_table g

                ON a.tax_code_sales=g.gst_tax_code



                LEFT JOIN backend.pay_term b

                ON a.term=b.CODE



                LEFT JOIN backend.dc_pick d

                ON a.converted_from_guid=d.trans_guid



                LEFT JOIN backend.dc_req e

                ON d.trans_guid=e.converted_guid

                WHERE a.refno='$row->RefNo' AND TYPE='c') a



                INNER JOIN



                (SELECT /*IF(remark IS NULL OR remark='',IF(branch_name ='' OR branch_name IS NULL,companyname,branch_name),remark)*/

                IF(branch_name='' OR branch_name IS NULL,companyname,branch_name) AS companyname,

                (SELECT invremark1 FROM backend.xsetup) AS invremark1,

                (SELECT invremark2 FROM backend.xsetup) AS invremark2,

                (SELECT invremark3 FROM backend.xsetup) AS invremark3,

                IF(branch_add='' OR branch_add IS NULL,address1,'') AS address1,

                IF(branch_add='' OR branch_add IS NULL,address2,'') AS address2,

                IF(branch_add='' OR branch_add IS NULL,address3,'') AS address3,

                IF(branch_add='' OR branch_add IS NULL,CONCAT('Tel: ',c.tel,'    Fax: ',c.fax),CONCAT('Tel: ',branch_tel,'    Fax: ',branch_fax)) AS contactnumber,

                IF(branch_add='' OR branch_add IS NULL,'',branch_add) AS branch_add,



                CONCAT('Co Reg No : ',IF(reg_no='' OR reg_no IS NULL,comp_reg_no,reg_no),

                IF(invoicedate BETWEEN (SELECT gst_start_date FROM backend.companyprofile)

                AND (SELECT gst_end_date FROM backend.companyprofile),

                IF(branch_gst='' OR branch_gst IS NULL,

                IF(gst_no='','',CONCAT('    GST Reg No : ',gst_no)),

                CONCAT('    GST Reg No : ',branch_gst)),

                IF(invoicedate BETWEEN (SELECT sst_start_date FROM backend.companyprofile)

                AND (SELECT sst_end_date FROM backend.companyprofile),

                IF(branch_sst='' OR branch_sst IS NULL,

                IF(sst_no='','',CONCAT('    SST Reg No : ',sst_no)),

                CONCAT('    SST Reg No : ',branch_sst)),''))) reg_no,



                IF(invoicedate BETWEEN (SELECT gst_start_date FROM backend.companyprofile)

                AND (SELECT gst_end_date FROM backend.companyprofile),'Total Amount Exclude Tax',

                IF(invoicedate BETWEEN (SELECT sst_start_date FROM backend.companyprofile)

                AND (SELECT sst_end_date FROM backend.companyprofile),'Total Amount Exclude Tax',

                'Total Amount')) AS title_total,



                a.refno, 

                Branch_name

                FROM backend.simain a



                INNER JOIN backend.companyprofile c



                LEFT JOIN 

                (SELECT a.refno,reg_no,gst_no AS branch_gst,name_reg,branch_add,branch_name,branch_tel,branch_fax,

                SSTRegNo AS branch_sst  

                FROM backend.simain a

                INNER JOIN backend.cp_set_branch b

                ON a.loc_group=b.branch_code

                INNER JOIN backend.supcus c

                ON b.set_supplier_code=c.CODE

                WHERE refno='$row->RefNo') b



                ON a.refno=b.refno



                WHERE a.refno='$row->RefNo') b



                ON a.si_refno=b.refno ");

            $query2 = $this->db->query("SELECT a.itemcode,barcode,articleno,description,c.item_remark,packsize,bulkqty,unitprice,

                IF(disc1value=0,'',IF(disc1type='%',CONCAT(disc1value,disc1type),CONCAT(disc1type,disc1value))) AS disc1,

                IF(disc2value=0,'',IF(disc2type='%',CONCAT(disc2value,disc2type),CONCAT(disc2type,disc2value))) AS disc2,discamt,

                netunitprice,qty,totalprice,itemremark,IF(pricetype='foc','FOC','') AS pricetype,



                line,itemlink,a.refno,disc1type,disc2type,LOWER(um) AS um,

                IF(qty<bulkqty OR bulkqty=1,'',CONCAT('= ',IF(MOD(qty/bulkqty,1)=0,qty/bulkqty,ROUND(qty/bulkqty,1)),' ',umbulk)) AS ctn,

                IF(bqty=0,'',IF(bulkqty=packsize OR bulkqty<=1,'',CONCAT('[',Bqty,' ',LOWER(umbulk),IF(pqty=0,'',CONCAT(' ',Pqty)),']'))) AS b_qty,

                IF(disc1value=0,'',IF(disc1type='%',CONCAT(ROUND(disc1value,2),disc1type),CONCAT(disc1type,ROUND(disc1value,2)))) AS disc1value,

                IF(disc2value=0,'',IF(disc2type='%',CONCAT(ROUND(disc2value,2),disc2type),CONCAT(disc2type,ROUND(disc2value,2)))) AS disc2value,

                CONCAT(IF(disc1value=0,'',IF(disc1type='%',CONCAT(IF(MOD(disc1value,1)=0,ROUND(disc1value),ROUND(disc1value,2)),disc1type),

                CONCAT(disc1type,ROUND(disc1value,2)))),IF(disc2value=0,'',CONCAT(IF(disc1value=0,'',' + '),IF(disc2type='%',IF(MOD(disc2value,1)=0,

                ROUND(disc2value),ROUND(disc2value,2)),disc2type),ROUND(disc2value,2)))) AS disc_desc,





                IF(a.gst_tax_code IN ('zrl','sr'),UPPER(LEFT(gst_tax_code,1)),UPPER(gst_tax_code)) AS gst_unit_code,

                ROUND(gst_tax_amount/qty,4) AS gst_unit_tax,

                ROUND(IF(discvalue=0,netunitprice+(gst_tax_amount/qty),((totalprice-discvalue)+gst_tax_amount)/qty),4) AS gst_unit_cost,

                gst_tax_amount AS gst_child_tax,



                ROUND((totalprice-discvalue)+

                IF(invoicedate BETWEEN (SELECT gst_start_date FROM backend.companyprofile)

                AND (SELECT gst_end_date FROM backend.companyprofile),gst_tax_amount,

                IF(invoicedate BETWEEN (SELECT sst_start_date FROM backend.companyprofile)

                AND (SELECT sst_end_date FROM backend.companyprofile),taxamount,0)),2) AS gst_unit_total,



                gst_tax_sum AS gst_main_tax,

                ROUND(total+gst_tax_sum,2) AS gst_main_total,

                CONCAT(packsize,IF(bulkqty=1,'',CONCAT('/',bulkqty))) AS ps,



                IF(invoicedate BETWEEN (SELECT gst_start_date FROM backend.companyprofile)

                AND (SELECT gst_end_date FROM backend.companyprofile),gst_tax_code,

                IF(invoicedate BETWEEN (SELECT sst_start_date FROM backend.companyprofile)

                AND (SELECT sst_end_date FROM backend.companyprofile),taxcodemap,'')) AS gst_tax_code,



                a.gst_tax_rate,



                IF(invoicedate BETWEEN (SELECT gst_start_date FROM backend.companyprofile)

                AND (SELECT gst_end_date FROM backend.companyprofile),

                IF(LENGTH(MID(gst_tax_amount,POSITION('.' IN gst_tax_amount)+1,10))<=2,FORMAT(gst_tax_amount,2),

                FORMAT(gst_tax_amount,4)),

                IF(invoicedate BETWEEN (SELECT sst_start_date FROM backend.companyprofile)

                AND (SELECT sst_end_date FROM backend.companyprofile),

                FORMAT(taxamount,2),'0.00')) AS gst_tax_amount,



                ROUND(discvalue/qty,4) AS unit_disc_prorate,

                IF(discvalue=0,netunitprice,ROUND((totalprice-discvalue)/qty,4)) AS unit_price_bfr_tax,

                ROUND((totalprice-discvalue),4) AS total_price_bfr_tax



                FROM backend.sichild a



                INNER JOIN backend.simain b

                ON a.refno=b.refno



                LEFT JOIN (SELECT itemcode, remark AS item_remark FROM backend.itemmaster )c 

                ON a.`Itemcode` = c.`Itemcode`



                WHERE a.refno='$row->RefNo' AND qty<>0

                ORDER BY line");

            $query3 = $this->db->query("SELECT a.*,

                IF((SELECT gst_end_date FROM backend.companyprofile)>=invoicedate

                AND (SELECT country FROM backend.companyprofile)='malaysia','Tax @ 6%','Tax @ >0%') AS tax_sum_title FROM



                (SELECT a.refno,SUM(gst_zero) AS gst_zero,SUM(gst_std) AS gst_std FROM 



                (SELECT a.refno,ROUND(SUM(totalprice-discvalue),2) AS gst_zero,0 AS gst_std FROM backend.sichild a

                INNER JOIN backend.simain b

                ON a.refno=b.refno

                WHERE gst_tax_amount=0 AND a.refno='$row->RefNo'

                GROUP BY refno



                UNION ALL



                SELECT a.refno,0 AS gst_zero,ROUND(SUM(totalprice-discvalue),2) AS gst_std FROM backend.sichild a

                INNER JOIN backend.simain b

                ON a.refno=b.refno

                WHERE gst_tax_amount<>0 AND a.refno='$row->RefNo'

                GROUP BY refno



                UNION ALL



                SELECT a.refno,0 AS gst_zero,

                ROUND(SUM(ABS(value_calculated)),2) AS value_calculated

                FROM backend.trans_surcharge_discount a

                WHERE a.refno='$row->RefNo' AND dn=0 AND value_factor=1 AND gst_amt<>0

                GROUP BY refno) a



                GROUP BY refno) a



                INNER JOIN backend.simain b

                ON a.refno=b.refno");

            $query4 = $this->db->query("SELECT a.refno,'0' AS sort,'0' AS sequence,

                CONCAT('Total Amount') AS code_grn,

                0 AS value_grn,

                subtotal1 AS value_calculated FROM backend.simain a

                WHERE a.refno='$row->RefNo' 



                UNION ALL



                SELECT a.refno,'1' AS sort,'1' AS sequence,CONCAT(IF(discount1type='%',IF(discount1>0,'Discount %   ','Surchage %   '),

                IF(discount1>0,'Discount $   ','Surcharge $   '))) AS code_grn,

                discount1*-1 AS value_grn,

                ROUND(subtotal1*disc1percent/100,2) AS value_calculated FROM backend.simain a

                LEFT JOIN backend.trans_surcharge_discount b

                ON a.refno=b.refno

                WHERE a.refno='$row->RefNo' AND ROUND(subtotal1*disc1percent/100,2)<>0 AND b.refno IS NULL AND discount1<>0



                UNION ALL



                SELECT a.refno,'2' AS sort,'2' AS sequence,CONCAT(IF(discount2type='%',IF(discount2>0,'Discount %   ','Surchage %   '),

                IF(discount2>0,'Discount $   ','Surcharge $   '))) AS code_grn,

                discount2*-1 AS value_grn,

                ROUND(subtotal2*disc2percent/100,2) AS value_calculated FROM backend.simain a

                LEFT JOIN backend.trans_surcharge_discount b

                ON a.refno=b.refno

                WHERE a.refno='$row->RefNo' AND ROUND(subtotal2*disc2percent/100,2)<>0 AND b.refno IS NULL AND discount2<>0



                UNION ALL



                SELECT refno,'A1' AS sort,sequence,CONCAT(CODE,' (',surcharge_disc_type,')') AS code_grn,

                surcharge_disc_value*value_factor AS value_grn,

                ROUND(value_calculated,2) AS value_calculated

                FROM backend.trans_surcharge_discount 

                WHERE refno='$row->RefNo' AND dn=0



                UNION ALL



                SELECT refno,'A2' AS sort,'A2' AS sequence,'Total Include Surcharge/Disc' AS code_grn,0 AS value_grn,

                total AS value_calculated FROM backend.simain

                WHERE refno='$row->RefNo' AND discount1+discount2<>0 AND doc_type<>'EStore'



                UNION ALL



                SELECT refno,'B1' AS sort,'B1' AS sequence,'Total Tax Amount' AS code_grn,0 AS value_grn,

                ROUND(gst_tax_sum+surchg_tax_sum+totaltax,2) AS value_calculated FROM backend.simain

                WHERE refno='$row->RefNo' AND 

                (invoicedate BETWEEN (SELECT gst_start_date FROM backend.companyprofile)

                AND (SELECT gst_end_date FROM backend.companyprofile) OR

                invoicedate BETWEEN (SELECT sst_start_date FROM backend.companyprofile)

                AND (SELECT sst_end_date FROM backend.companyprofile)) 



                /*UNION ALL



                SELECT refno,'B2' AS sort,'B2' AS sequence,'Surcharge GST Amount' AS code_grn,0 AS value_grn,

                ROUND(surchg_tax_sum,2) AS value_calculated FROM backend.simain

                WHERE refno='$row->RefNo' and surchg_tax_sum<>0*/



                UNION ALL



                SELECT refno,'C1' AS sort,'C1' AS sequence,'GST Adjustment' AS code_grn,0 AS value_grn,

                ROUND(gst_adj,2) AS value_calculated FROM backend.simain

                WHERE refno='$row->RefNo' AND gst_adj<>0



                UNION ALL



                SELECT refno,'D1' AS sort,'D1' AS sequence,'Total Amount Include Tax' AS code_grn,0 AS value_grn,

                ROUND(total+gst_tax_sum+surchg_tax_sum+totaltax+gst_adj,2) AS value_calculated FROM backend.simain

                WHERE refno='$row->RefNo' AND 

                (invoicedate BETWEEN (SELECT gst_start_date FROM backend.companyprofile)

                AND (SELECT gst_end_date FROM backend.companyprofile) OR

                invoicedate BETWEEN (SELECT sst_start_date FROM backend.companyprofile)

                AND (SELECT sst_end_date FROM backend.companyprofile)) 





                UNION ALL



                SELECT refno,'E1' AS sort,'E1' AS sequence,'Rounding Adjustment' AS code_grn,0 AS value_grn,

                ROUND(rounding_adj,2) AS value_calculated FROM backend.simain

                WHERE refno='$row->RefNo' AND rounding_adj<>0



                UNION ALL



                SELECT refno,'F1' AS sort,'F1' AS sequence,'Total Nett Amount' AS code_grn,0 AS value_grn,

                ROUND(total+gst_tax_sum+surchg_tax_sum+rounding_adj+totaltax+gst_adj,2) AS value_calculated FROM backend.simain

                WHERE refno='$row->RefNo' 

                AND (ROUND(gst_tax_sum+surchg_tax_sum+rounding_adj+totaltax+gst_adj,2)<>0

                OR discount1<>0 OR discount2<>0) AND doc_type<>'EStore'



                UNION ALL



                SELECT refno,'A' AS sort,'G1' AS sequence,paytype AS code_grn,

                0 AS value_grn,

                ROUND(payamt*value_factor,2) AS value_calculated

                FROM backend.si_payment 

                WHERE refno='$row->RefNo' AND value_factor=-1



                UNION ALL



                SELECT a.refno,'F1' AS sort,'F1' AS sequence,'Total Nett Amount' AS code_grn,0 AS value_grn,

                ROUND(subtotal1+gst_tax_sum+surchg_tax_sum+totaltax+rounding_adj+b.amount,2) AS value_calculated

                FROM backend.simain a

                INNER JOIN 

                (

                SELECT refno,SUM(amount) AS amount FROM

                (

                SELECT refno,SUM(payamt*value_factor) AS amount FROM backend.si_payment

                WHERE value_factor = -1

                AND refno = '$row->RefNo'



                UNION ALL



                SELECT refno,SUM(value_calculated) AS amount FROM backend.`trans_surcharge_discount`

                WHERE refno = '$row->RefNo'

                )a

                GROUP BY refno

                )b

                ON a.refno = b.refno

                AND doc_type='EStore'



                /*

                SELECT a.refno,'F1' AS sort,'F1' AS sequence,'Total Nett Amount' AS code_grn,0 AS value_grn,

                ROUND(total+gst_tax_sum+surchg_tax_sum+totaltax+rounding_adj-SUM(payamt),2) AS value_calculated FROM backend.simain a

                INNER JOIN backend.si_payment b

                ON a.refno = b.refno

                WHERE a.refno='$row->RefNo' 

                #AND (ROUND(gst_tax_sum+surchg_tax_sum+rounding_adj+totaltax+gst_adj,2)<>0

                #OR discount1<>0 OR discount2<>0)

                AND value_factor=-1 AND doc_type='EStore'

                GROUP BY refno*/



                ORDER BY sort,sequence");

            

            $data3 = array(

                'data2' => $data2->result(),
		'query' => array(

		        'query1' => $query1->result(),

		        'query2' => $query2->result(),

		        'query3' => $query3->result(),

		        'query4' => $query4->result()
		)

            );         


		//print_r($data3);die;
            $username = 'admin'; //get from rest.php

            $password = '1234'; //get from rest.php


	
            // $url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/pomain2';

            //$url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/simain';
	    $url = 'http://office.panda-eco.com:18243/rest_api/index.php/Panda_b2b/simain';

             //echo $url;die;

            $ch = curl_init($url);



            curl_setopt($ch, CURLOPT_TIMEOUT, 0);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));

            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data3));



            $result = curl_exec($ch);

            //echo $result;die;

            $output =  json_decode($result);

            $status = $output->message;

            if($status == "true")

            {

                    //$run = $this->db->query("UPDATE backend.simain SET uploaded = '1' , uploaded_at = '$date' WHERE RefNo = '$row->RefNo'"); 

            }

            else

            {

                    //$run = $this->db->query("UPDATE backend.pomain SET hq_update = '3' WHERE RefNo = '$row->RefNo'");

            }

        }//close foreach



        $this->response(

            [

                'status' => TRUE,

                'message' => 'Success'

            ]

            // $this->Main_model->query_call('Api','login_validation_get', $data)

        ); 

    }

    public function pomain_info_get()

    {



        $query1 = $this->db->query("SELECT a.*

		FROM backend.`pomain` AS a

		INNER JOIN backend.`pochild` AS b

		ON a.`RefNo` = b.`RefNo`

		WHERE a.`SCode` = 'P189'

		AND a.`RefNo` = 'BERPO21101327'

		GROUP BY a.`RefNo`");



        $query2 = $this->db->query("SELECT b.*

		FROM backend.`pomain` AS a

		INNER JOIN backend.`pochild` AS b

		ON a.`RefNo` = b.`RefNo`

		WHERE a.`SCode` = 'P189'

		AND a.`RefNo` = 'BERPO21101327'");



        $json = array(

            'pomain' => $query1->result(),

            'pochild' => $query2->result(),

        );



        $this->response($json);

    }

     public function get_backend_info_post()
    {
        $refno = $this->input->post('refno');
        $type = $this->input->post('type');

        $refno_list = explode(',', $refno);

        $refno_in = implode("','", $refno_list);

        $refno_in = str_replace(' ', '', $refno_in);

        $refno_in = "'" . $refno_in . "'";

        if ($type == 'PO') {
            $result_main =  $this->db->query("SELECT a.`RefNo`,a.PODate,a.`postdatetime`,a.`BillStatus`,a.`ibt`,a.`in_kind`,a.`unpost`,a.`uploaded`,a.`uploaded_at`,a.`rejected`,a.`rejected_at`,a.`total_include_tax`
            FROM backend.pomain AS a WHERE a.refno IN($refno_in)")->result();

            $result_child =  $this->db->query("SELECT a.`RefNo`,a.`Line`,a.`TotalPrice`
            FROM backend.pochild AS a WHERE a.refno IN($refno_in)")->result();

            $result_variance_amount = $this->db->query("SELECT a.`total_include_tax`,SUM(b.`TotalPrice`) AS total_child,(a.`total_include_tax` - SUM(b.`TotalPrice`)) AS diffrence
            FROM backend.`pomain` AS a
            INNER JOIN backend.`pochild` AS b
            ON a.`RefNo` = b.`RefNo` WHERE a.refno IN($refno_in)")->result();
        } elseif ($type == 'GR') {
            $result_main =  $this->db->query("SELECT a.`RefNo`,a.`DocDate`,a.`GRDate`,a.`postdatetime`,a.`BillStatus`,a.`ibt`,a.`uploaded`,a.`uploaded_at`,a.`EXPORT_ACCOUNT`,a.`Total`,a.`Subtotal1` FROM backend.grmain AS a WHERE a.refno IN($refno_in)")->result();

            $result_child =  $this->db->query("SELECT a.`RefNo`,a.`Line`,a.`TotalPrice` FROM backend.grchild AS a WHERE a.refno IN($refno_in)")->result();

            $result_variance_amount = $this->db->query("SELECT a.`total_include_tax`,SUM(b.`TotalPrice`) AS total_child,(a.`total_include_tax` - SUM(b.`TotalPrice`)) AS diffrence
            FROM backend.`grmain` AS a
            INNER JOIN backend.`grchild` AS b
            ON a.`RefNo` = b.`RefNo` WHERE a.refno IN($refno_in)")->result();
        } elseif ($type == 'GRDA') {
            $result_main =  $this->db->query("SELECT a.`RefNo`,a.`sup_cn_no`,a.`VarianceAmt`,a.`EXPORT_ACCOUNT`,a.`uploaded`,a.`uploaded_at` FROM backend.grmain_dncn AS a WHERE a.refno IN($refno_in)")->result();
            $result_child = [];
            $result_variance_amount = [];
        } elseif ($type == 'PCI') {
            $result_main =  $this->db->query("SELECT a.`inv_refno`,a.`promo_refno`,a.`refno`,a.`posted`,a.`posted_at`,a.`uploaded`,a.`updated_at` FROM backend.promo_taxinv AS a WHERE a.inv_refno IN($refno_in)")->result();
            $result_child = [];
            $result_variance_amount = [];
        } elseif ($type == 'CN_Note') {
            $result_main =  $this->db->query("SELECT a.`RefNo`,a.`DocDate`,a.`BillStatus`,a.`postdatetime`,a.`ibt`,a.`uploaded`,a.`uploaded_at` FROM backend.cnnotemain AS a WHERE a.promo_refno IN($refno_in)")->result();
            $result_child =  $this->db->query("SELECT a.`RefNo`,a.`Line` FROM backend.cnnotechild AS a WHERE a.refno IN($refno_in)")->result();
            $result_variance_amount = [];
        } elseif ($type == 'DN_Note') {
            $result_main =  $this->db->query("SELECT a.`Type`,a.`RefNo`,a.`DocDate`,a.`postdatetime`,a.`ibt`,a.`uploaded`,a.`uploaded_at`,a.`EXPORT_ACCOUNT`,a.`Amount`,a.`unpostby`,a.`unpostdatetime` FROM backend.dbnotemain AS a WHERE a.refno IN($refno_in)")->result();
            $result_child =  $this->db->query("SELECT a.`RefNo`,a.`Line` FROM backend.dbnotechild AS a WHERE a.refno IN($refno_in)")->result();
            $result_variance_amount = [];
        } elseif ($type == 'PCNamt') {
            $result_main =  $this->db->query("SELECT a.trans_type,a.refno,a.docdate,a.posted,a.posted_at,a.ibt,a.uploaded,a.uploaded_at FROM backend.cndn_amt AS a WHERE a.refno IN($refno_in)")->result();
            $result_child = [];
            $result_variance_amount = [];
        } elseif ($type == 'PDNamt') {
            $result_main =  $this->db->query("SELECT a.trans_type,a.refno,a.docdate,a.posted,a.posted_at,a.ibt,a.uploaded,a.uploaded_at FROM backend.cndn_amt AS a WHERE a.refno IN($refno_in)")->result();
            $result_child = [];
            $result_variance_amount = [];
        } elseif ($type == 'DI') {
            $result_main =  $this->db->query("SELECT a.docdate,a.inv_refno,a.refno,a.posted,a.posted_at,a.uploaded,a.uploaded_at FROM backend.discheme_taxinv AS a WHERE a.refno IN($refno_in)")->result();
            $result_child = [];
            $result_variance_amount = [];
        } elseif ($type == 'SI') {
            $result_main =  $this->db->query("SELECT a.`RefNo`,a.`BillStatus`,a.`ibt`,a.`EXPORT_ACCOUNT`,a.`uploaded`,a.`uploaded_at` FROM backend.simain AS a WHERE a.refno IN($refno_in)")->result();
            $result_child = $this->db->query("SELECT a.`RefNo`,a.`Line`,a.`postdatetime_c` FROM backend.sichild AS a WHERE a.refno IN($refno_in)")->result();
            $result_variance_amount = [];
        }
        // elseif ($type == 'other_doc') {
        //     $result =  $this->db->query("SELECT * FROM backend.pomain AS a WHERE a.refno IN($refno_in)")->result();
        // } 
        else {
            $result_main = [];
            $result_child = [];
            $result_variance_amount = [];
        }



        $json = array(

            'result_main' => $result_main,
            'result_child' => $result_child,
            'result_variance_amount' => $result_variance_amount,

        );

        $this->response($json);
    }

}


