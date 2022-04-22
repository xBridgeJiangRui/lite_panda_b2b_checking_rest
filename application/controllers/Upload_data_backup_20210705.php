<?php

require(APPPATH.'/libraries/REST_Controller.php');

class Upload_data extends REST_Controller{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
    } 

    public function purchase_order_get()
    {
        $data = $this->db->query("SELECT
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
        WHERE podate >= DATE_FORMAT((SELECT date_start FROM rest_api.`run_once_config` LIMIT 1),'%Y-%m-%d')
        AND billstatus = '1' 
        AND hq_update < '3'
        AND uploaded = '1'  
        LIMIT 100");

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
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/pomain2';
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
                    $run = $this->db->query("UPDATE backend.pomain SET hq_update = '3', uploaded = '2' WHERE RefNo = '$row->RefNo'"); 
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
        $data = $this->db->query("SELECT
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
        WHERE LEFT(grdate,10) >= DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 3 MONTH) 
        AND billstatus = '1'
        AND uploaded = '1'
        AND EXPORT_ACCOUNT = 'PENDING'
        AND hq_update < '3' 
        LIMIT 100");

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
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/grmain2';
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
                    $run = $this->db->query("UPDATE backend.grmain SET hq_update = '3' WHERE RefNo = '$row->RefNo'"); 
            }
            else
            {
                    $run = $this->db->query("UPDATE backend.grmain SET hq_update = '3' WHERE RefNo = '$row->RefNo'");
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
        $data = $this->db->query("SELECT
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
        WHERE LEFT(a.Created_at,10) >= DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 3 MONTH)
        AND a.hq_update = '2'
        AND a.uploaded = '2' 
        LIMIT 100");

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
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/grda2';
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
                    $run = $this->db->query("UPDATE backend.grmain_dncn SET hq_update = '3' WHERE RefNo = '$row->RefNo' AND transtype = '$row->transtype'"); 
            }
            else
            {
                    $run = $this->db->query("UPDATE backend.grmain_dncn SET hq_update = '3' WHERE RefNo = '$row->RefNo' AND transtype = '$row->transtype'");
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
        $data = $this->db->query("SELECT 
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
        date_collected AS date_collected 
        FROM backend.dbnotemain
        WHERE docdate >= DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 3 MONTH)
        AND billstatus = '1'
        AND sctype = 'S'
        AND uploaded = '2'
        AND hq_update = '2' 
        LIMIT 100");

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

            date_collected AS date_collected  
            FROM backend.dbnotemain
            WHERE Type = '$row->TYPE' AND RefNo = '$row->RefNo'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            // $url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/dbnotemain2';
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/dbnotemain2';
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
                    $run = $this->db->query("UPDATE backend.dbnotemain set hq_update = '3' WHERE Type = '$row->TYPE' AND RefNo = '$row->RefNo'"); 
            }
            else
            {
                    $run = $this->db->query("UPDATE backend.dbnotemain set hq_update = '3' WHERE Type = '$row->TYPE' AND RefNo = '$row->RefNo'");
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
        $data = $this->db->query("SELECT 
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
        WHERE docdate >= DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 3 MONTH)
        AND billstatus = '1'
        and sctype = 'S'
        and hq_update = '2'
        and uploaded = '2' 
        LIMIT 100");

        //print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            // echo $row->RefNo;die; 
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
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/cnnotemain2';
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
                    $run = $this->db->query("UPDATE backend.cnnotemain SET hq_update = '3' WHERE Type = '$row->TYPE' AND RefNo = '$row->RefNo'"); 
            }
            else
            {
                    $run = $this->db->query("UPDATE backend.cnnotemain SET hq_update = '3' WHERE Type = '$row->TYPE' AND RefNo = '$row->RefNo'");
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
        WHERE docdate >= DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 3 MONTH)
        AND posted = '1'
        AND trans_type IN ('PCNAMT' , 'PDNAMT') 
        and hq_update = '2' 
        and uploaded = '2' 
        LIMIT 100");

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
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/cndnamt2';
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
                    $run = $this->db->query("UPDATE backend.cndn_amt SET hq_update = '3' WHERE cndn_guid = '$row->cndn_guid'"); 
            }
            else
            {
                    $run = $this->db->query("UPDATE backend.cndn_amt SET hq_update = '3' WHERE cndn_guid = '$row->cndn_guid'");
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
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/promo_taxinv2';
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
                    $run = $this->db->query("UPDATE backend.promo_taxinv SET hq_update = '3', uploaded = '2' WHERE taxinv_guid = '$row->taxinv_guid'");
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
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/discheme_taxinv2';
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
                    $run = $this->db->query("UPDATE backend.discheme_taxinv SET hq_update = '3', uploaded = '2' WHERE taxinv_guid = '$row->taxinv_guid'");
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
            posted_at as posted_at
            FROM backend.dbnote_batch 
            WHERE dbnote_guid = '$row->dbnote_guid'");

            $username = 'admin'; //get from rest.php
            $password = '1234'; //get from rest.php

            //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/dbnotebatch';
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/dbnotebatch';
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
                    $run = $this->db->query("UPDATE backend.dbnote_batch SET hq_update = '3' WHERE dbnote_guid = '$row->dbnote_guid'");
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
        WHERE LEFT(laststamp,10) > DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-%d'), INTERVAL - 15 DAY)");
        
        //print_r($data->num_rows());die;

        $username = 'admin'; //get from rest.php
        $password = '1234'; //get from rest.php

        //$url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/supcus2';
        $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/supcus2';
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
                $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/cp_set_branch2';
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
            $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/po_completed2';
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
                    $run = $this->db->query("UPDATE backend.pomain set hq_update = '3' WHERE RefNo = '$row->RefNo'");
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
        $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/receive_checking_uploaded_po';
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
                    $this->db->query("UPDATE backend.pomain SET hq_update = 3 WHERE refno = '$po_refno'");
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
     
}

