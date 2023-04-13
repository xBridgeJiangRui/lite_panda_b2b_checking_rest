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
        WHERE LEFT(laststamp,10) > DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-%d'), INTERVAL - 3 MONTH)");
        
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
             -- WHERE updated_at >=  DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL - 100 MONTH)");
             print_r($data->num_rows());die;

                $username = 'admin'; //get from rest.php
                $password = '1234'; //get from rest.php

                $url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/cp_set_branch2';
                // $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/cp_set_branch2';
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
        and expiry_date > curdate()");

        print_r($data->num_rows());die;

        foreach($data->result() as $row)
        {
            // echo $row->RefNo;die; 
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

            $url = 'http://127.0.0.1/rest_api/index.php/panda_b2b/po_completed2';
            // $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/po_completed2';
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
                    $run = $this->db->query("UPDATE backend.pomain set hq_update = '999' WHERE RefNo = '$row->RefNo'");
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

     
}

