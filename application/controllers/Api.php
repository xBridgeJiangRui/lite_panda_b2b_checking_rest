<?php

require(APPPATH.'/libraries/REST_Controller.php');

class Api extends REST_Controller{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");

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
        
        $this->b2b_ip = $this->b2b_ip_https.$this->b2b_public_ip.$this->b2b_ip_port;
        // echo $this->b2b_ip;
        // die;

        // $this->b2b_ip = 'http://52.163.112.202';
        // $this->b2b_ip = 'http://127.0.0.1';
    }

    public function check_error_get() 
    {  
        $timenow = date("Y-m-d H:i:s");

        $runtime = $this->db->query("SELECT * FROM rest_api.troubleshoot_set_scheduler WHERE type = 'rest_check_error' AND active = 1");

        $document_date = $this->db->query("SELECT date_start FROM rest_api.run_once_config WHERE active = 1")->row('date_start');
        // echo $document_date;die;

        // print_r($result2->num_rows());
        // print_r($result->result());
        // echo $result->row('uploaded_at');
        $schedule_run_time = $runtime->row('next_run_datetime');
        // echo $schedule_run_time;die;

        if($timenow >= $schedule_run_time)
        {   
            $document_interval = $this->db->query("SELECT * FROM rest_api.troubleshoot_set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'document_interval'")->row('reason');

            // echo $document_interval;die;
            $result = $this->db->query("SELECT DATE_ADD(MAX(uploaded_at), INTERVAL $document_interval MINUTE) as uploaded_at FROM backend.grmain");
            // echo $result->row('uploaded_at');die;

            $result2 = $this->db->query("SELECT COUNT(a.Refno),a.RefNo FROM backend.grmain a INNER JOIN backend.`grchild` b ON a.`RefNo` = b.`RefNo` WHERE uploaded = 0 AND billstatus = 1 and export_account = 'Pending' and ibt = '0' AND grdate >= '$document_date' GROUP BY a.`RefNo`;");
            // print_r($result2->num_rows());die;

            if($timenow >= $result->row('uploaded_at') && $result2->num_rows() > 0)
            {
                $data = $this->db->query("SELECT COUNT(a.Refno) as count,a.RefNo,'Generating GRN PDF at Client Server' as message FROM backend.grmain a INNER JOIN backend.`grchild` b ON a.`RefNo` = b.`RefNo` WHERE uploaded = 0 AND billstatus = 1 and export_account = 'Pending' and ibt = '0' AND grdate >= '$document_date';")->result();
                // $this->load->view('main_view',$data);die;
            }
            else
            {
                $data = array();
            }             

            $result3 = $this->db->query("SELECT DATE_ADD(MAX(uploaded_at), INTERVAL $document_interval MINUTE) as uploaded_at FROM backend.pomain");
            // echo $result->row('uploaded_at');die;

            $result4 = $this->db->query("SELECT COUNT(a.Refno) FROM backend.pomain a INNER JOIN backend.`pochild` b ON a.`RefNo` = b.`RefNo` WHERE  uploaded = '0' and billstatus = 1 and completed = 0 and podate >= '$document_date' and ibt = '0' GROUP BY a.`RefNo`;");

            if($timenow >= $result3->row('uploaded_at') && $result4->num_rows() > 0)
            {
                $data2 = $this->db->query("SELECT COUNT(a.Refno) as count,a.RefNo,'Generating PO PDF at Client Server' as message FROM backend.pomain a INNER JOIN backend.`pochild` b ON a.`RefNo` = b.`RefNo` WHERE  uploaded = '0' and billstatus = 1 and completed = 0 and podate >= '$document_date' and ibt = '0';")->result();
                // $this->load->view('main_view',$data);die;
            }
            else
            {
                $data2 = array();
            }             


            $result5 = $this->db->query("SELECT DATE_ADD(MAX(uploaded_at), INTERVAL $document_interval MINUTE) as uploaded_at FROM backend.pomain");
            // echo $result->row('uploaded_at');die;

            $result6 = $this->db->query("SELECT COUNT(a.Refno) FROM backend.pomain a INNER JOIN backend.`pochild` b ON a.`RefNo` = b.`RefNo` WHERE  uploaded = '1' and billstatus = 1 and completed = 0 and podate >= '$document_date' and ibt = '0' GROUP BY a.`RefNo`;");

            if($timenow >= $result5->row('uploaded_at') && $result6->num_rows() > 0)
            {
                $data3 = $this->db->query("SELECT COUNT(a.Refno) as count,a.Refno,'Pending PO Data to Upload into Rexbridge' as message FROM backend.pomain a INNER JOIN backend.`pochild` b ON a.`RefNo` = b.`RefNo` WHERE  uploaded = '1' and billstatus = 1 and completed = 0 and podate >= '$document_date' and ibt = '0';")->result();
                // $this->load->view('main_view',$data);die;
            }
            else
            {
                $data3 = array();
            } 


            $result7 = $this->db->query("SELECT DATE_ADD(MAX(uploaded_at), INTERVAL $document_interval MINUTE) as uploaded_at FROM backend.grmain");
            // echo $result->row('uploaded_at');die;

            $result8 = $this->db->query("SELECT COUNT(a.Refno),a.RefNo FROM backend.grmain a INNER JOIN backend.`grchild` b ON a.`RefNo` = b.`RefNo` WHERE uploaded = 1 AND billstatus = 1 and export_account = 'Pending' and ibt = '0' AND grdate >= '$document_date' GROUP BY a.`RefNo`;");
            // print_r($result2->num_rows());die;

            if($timenow >= $result7->row('uploaded_at') && $result8->num_rows() > 0)
            {
                $data4 = $this->db->query("SELECT COUNT(a.Refno) as count,a.RefNo,'Pending GRN Data to Upload into Rexbridge' as message FROM backend.grmain a INNER JOIN backend.`grchild` b ON a.`RefNo` = b.`RefNo` WHERE uploaded = 1 AND billstatus = 1 and export_account = 'Pending' and ibt = '0' AND grdate >= '$document_date';")->result();
                // $this->load->view('main_view',$data);die;
            }  
            else
            {
                $data4 = array();
            }          

            // echo var_dump(array_merge($data,$data2));
            $xdata = array_merge($data2,$data,$data3,$data4);
            // $xdata = array();
            // print_r(count($xdata));
            // if(count($xdata) != 0 || count($xdata) > 0)
            // {
            //     echo 1;
            // }
            // else
            // {
            //     echo 2;
            // }
            // die;
            if(count($xdata) != 0 || count($xdata) > 0)
            {
                $programmer_email = $this->db->query("SELECT * FROM rest_api.troubleshoot_programmer_email_list WHERE isactive = 1 ORDER BY seq ASC");
                $programmer_email_num = $programmer_email->num_rows();
                $fail_email_status = '';
                $i = 0;
                foreach($programmer_email->result() as $row2)
                {


                    $xemail_send_interval = $this->db->query("SELECT * FROM rest_api.troubleshoot_set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'email_send_interval'")->row('reason');
                    $mail_status = $this->send_email($xemail_send_interval,'Error',$row2->programmer_email,$row2->programmer_name,'',$xdata);

                    if($mail_status == 'SMTP connect() failed.') 
                    {
                        $fail_email_status .= '1';
                    }
                    else if($mail_status == 'You must provide at least one recipient email address.')
                    {
                        $i++;
                        if($i == $programmer_email_num)
                        {
                            $fail_email_status .= '1';   
                        }
                    }
                    else 
                    {
                        $fail_email_status .= ''; 
                    }  

                    // echo $row2->programmer_email;
                }

                if($fail_email_status == '' || $fail_email_status == null)
                {
                    $email_send_interval = $this->db->query("SELECT * FROM rest_api.troubleshoot_set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'email_send_interval'")->row('reason');
                    $this->db->query("UPDATE rest_api.troubleshoot_set_scheduler SET next_run_datetime = DATE_ADD(NOW(), INTERVAL $email_send_interval MINUTE) WHERE type = 'rest_check_error'");
                    if($this->db->affected_rows() > 0)
                    {
                        $this->response(
                        [
                            'status' => TRUE,
                            'message' => "Got pending document, email sent, scheduler next run time updated\r\n"
                        ]
                        );
                    }
                    else
                    {
                        $this->response(
                        [
                            'status' => TRUE,
                            'message' => "Got pending document, email sent, scheduler next run time not update\r\n"
                        ]
                        );
                    }
                }
                else
                {
                    $this->response(
                    [
                        'status' => FALSE,
                        'message' => "Got pending document, email not sent\r\n"
                    ]
                    );
                }
            }
            else
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => "No pending document\r\n"
                    ]
                    );
            }
            
        }
        else
        {
            $this->response(
            [
                'status' => FALSE,
                'message' => "Scheduler next run time not reached\r\n"
            ]
            );
        }
    } 

    public function send_email($time,$filename,$recipient,$name,$description,$data2)
    {
        // require 'PHPMailerAutoload.php';
        $company_name = $this->db->query("SELECT acc_name FROM panda_b2b.acc LIMIT 1")->row('acc_name');
        $this->load->library('Panda_PHPMailer');
        $mail = new PHPMailer;

        $email_setup = $this->db->query("SELECT * FROM rest_api.troubleshoot_email_setup");

        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = $email_setup->row('smtp_server'); // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = $email_setup->row('username'); // SMTP username
        $mail->Password = $email_setup->row('password'); // SMTP password
        $mail->SMTPSecure = $email_setup->row('smtp_security');// Enable TLS encryption, `ssl` also accepted
        $mail->Port = $email_setup->row('smtp_port'); // TCP port to connect to

        $mail->setFrom($email_setup->row('username'), $email_setup->row('sender_email'));
        $mail->addReplyTo($email_setup->row('username'), $email_setup->row('sender_email'));
        $mail->addAddress($recipient, $recipient); // Add a recipient
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        $date = $this->db->query("SELECT NOW() as now")->row('now');

        $mail->isHTML(true);  // Set email format to HTML
        // $path= base_url('assets/img/new.png');
        $data = array(
            'list' => $data2,
            'acc_name' =>$company_name
        );
        $bodyContent = $this->load->view('main_view',$data,TRUE);
        // echo $bodyContent;die;
        
        $mail->Subject = $email_setup->row('subject').' ('.$company_name.') '.date("Y-m-d H:i:s");
        $mail->Body = $bodyContent;
        $mail->addAttachment($name, $filename);

        if(!$mail->send()) 
        {
           $data = array(
            'created_at' => $this->db->query("SELECT now() as now")->row('now'),
            'created_by' => 'daniel',
            'recipient' => $recipient,
            'sender' => $email_setup->row('sender_email'),
            'subject' => $email_setup->row('subject'),
            'status' => 'UNSUCCESS(TESTING)',
            'respond_message' => $mail->ErrorInfo,
            'smtp_server' => $email_setup->row('smtp_server'),
            'smtp_port' => $email_setup->row('smtp_port'),
            'smtp_security' => $email_setup->row('smtp_security'),
            );
            $this->db->insert('rest_api.troubleshoot_email_transaction', $data);
            return $mail->ErrorInfo;
            // $this->session->set_flashdata('message', 'Message has been sent');
        }
        else
        {
            return 'sucess';
        }    
    } 

    public function old_upload_grn_no_get()
    {
        $customer_guid = $this->db->query("SELECT customer_guid as acc_guid FROM rest_api.`run_once_config` LIMIT 1");

        $refno = $this->db->query("SELECT
            (SELECT customer_guid FROM rest_api.`run_once_config` LIMIT 1) AS customer_guid,
            RefNo,
            hq_update
            FROM backend.`pomain` AS a
            INNER JOIN backend.supcus AS b
            ON a.`SCode` = b.`Code`
            WHERE podate >= DATE_FORMAT((SELECT date_start FROM rest_api.`run_once_config` LIMIT 1),'%Y-%m-%d')
            AND billstatus = '1' 
            AND completed IN('1','2')
            -- AND hq_update = '3'
            -- AND a.uploaded = 2 
            AND a.uploaded = '2'
            AND a.send != '2'
            ORDER BY podate DESC LIMIT 300");
            
        if($refno->num_rows() > 0)
        {
            $child_row = 0;
            $gr_child_no = '';
            foreach($refno->result() as $row)
            {
                //echo $row->RefNo;die;
		        $date = $this->db->query("SELECT NOW() as now")->row('now');
                $gr_child = $this->db->query("SELECT a.*,b.total,b.grdate FROM (SELECT *,ROUND(SUM(totalprice)-SUM(discvalue),2) AS t_price FROM backend.grchild WHERE PORefNo = '$row->RefNo' GROUP BY PORefNo,RefNo) a INNER JOIN backend.grmain b ON a.RefNo = b.RefNo");
                //echo $this->db->last_query();
                //echo $row->RefNo.'-'.count($gr_child->num_rows());die;
        
                if($gr_child->num_rows() > 0)
                {
                    //echo 11;die;
                    foreach($gr_child->result() as $row2)
                    {
                        //echo 2;die;

                        $data = array(
                            'customer_guid' => $customer_guid->row('acc_guid'),
                            'po_refno' => $row2->PORefNo,
                            'grn_refno' => $row2->RefNo,
                            'inv_refno' => $row2->InvRefno,
                            'grdate' => $row2->grdate,
                        );

                        $username = 'admin'; //get from rest.php
                        $password = '1234'; //get from rest.php

                        // $url = 'http://192.168.10.29/rest_api/index.php/Panda_b2b/receive_gr_no';
                        $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/receive_gr_no';
                        // echo $url;die;
                        $ch = curl_init($url);

                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

                        $result = curl_exec($ch);

                        //echo $result;die;
                        $output = json_decode($result);
                        curl_close($ch);
                        // echo 'asdads'.$output->status;die;

                        if($output->status == 'success')
                        {
                            $this->db->query("UPDATE backend.pomain SET uploaded = 3 , uploaded_at = '$date' WHERE RefNo = '$row2->PORefNo'");
                        }
                        else
                        {
                            //$this->db->query("UPDATE backend.pomain SET uploaded = 5 WHERE RefNo = '$row2->PORefNo'");
                            // $this->db->query("INSERT INTO lite_b2b.rest_data_err_log (`guid`,`customer_guid`,`po_refno`,`gr_refno`,`inv_refno`,`created_at`,`type`) VALUES('$guid','$customer_guid','$po_refno','$grn_refno','$inv_refno',NOW(),'po_gr_inv_upload') ");
                        }

                        // die;
                    }//close foreach result
                }//close if count
                else
                {
                    $child_row++;
                    $gr_child_no .= $row->RefNo.',';
                    //$this->db->query("UPDATE backend.pomain SET uploaded = 4 , uploaded_at = '$date' WHERE RefNo = '$row->RefNo'");
                }

            }//close foreach
            //echo $child_row;die;
        if($child_row > 0)
        {
        $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'Sucess, but got grchild('.$child_row.') '.$gr_child_no.' not found'
                    ]
                );
        }
        else
        {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'Sucess upload'
                    ]
                );
        }
        }
        else
        {
            $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'No grn no found'
                    ]
            );
        }
    }  

    public function upload_grn_no_get()
    {
        $customer_guid = $this->db->query("SELECT customer_guid as acc_guid FROM rest_api.`run_once_config` LIMIT 1");

        $refno = $this->db->query("SELECT f.customer_guid, a.RefNo, d.refno AS gr_refno, SUM(c.balanceqty) AS sum_balanceqty, SUM(c.temprecvqty) AS sum_temprecvqty, IF( a.completed = 1, 'complete', IF( a.autoclosepo = 0 AND a.completed = 0 AND ( SUM(c.temprecvqty)<> 0 OR SUM(c.balanceqty)<> 0 ), 'complete', 'error' ) ) AS `status` FROM backend.`pomain` AS a INNER JOIN backend.pochild AS c ON a.refno = c.refno INNER JOIN backend.grchild AS d ON a.refno = d.porefno INNER JOIN backend.grmain AS e ON d.refno = e.refno JOIN rest_api.run_once_config AS f WHERE a.podate >= f.date_start AND a.laststamp BETWEEN DATE_FORMAT( DATE_ADD( CURDATE(), INTERVAL -1 YEAR ), '%Y-%m-%d 00:00:00' ) AND NOW() AND a.billstatus = '1' AND a.uploaded = '2' GROUP BY a.refno HAVING `status` <> 'error' LIMIT 100");

        //echo $refno->num_rows();die;

        if($refno->num_rows() > 0)
        {
            $child_row = 0;
            $gr_child_no = '';
            foreach($refno->result() as $row)
            {
                //echo $row->RefNo;die;
		        $date = $this->db->query("SELECT NOW() as now")->row('now');
                $gr_child = $this->db->query("SELECT a.*,b.total,b.grdate FROM (SELECT *,ROUND(SUM(totalprice)-SUM(discvalue),2) AS t_price FROM backend.grchild WHERE PORefNo = '$row->RefNo' GROUP BY PORefNo,RefNo) a INNER JOIN backend.grmain b ON a.RefNo = b.RefNo");
                //echo $this->db->last_query();
                //echo $row->RefNo.'-'.count($gr_child->num_rows());die;
        
                if($gr_child->num_rows() > 0)
                {
                    //echo 11;die;
                    foreach($gr_child->result() as $row2)
                    {
                        //echo 2;die;
                        $data = array(
                            'customer_guid' => $customer_guid->row('acc_guid'),
                            'po_refno' => $row2->PORefNo,
                            'grn_refno' => $row2->RefNo,
                            'inv_refno' => $row2->InvRefno,
                            'grdate' => $row2->grdate,
                        );

                        $username = 'admin'; //get from rest.php
                        $password = '1234'; //get from rest.php

                        // $url = 'http://192.168.10.29/rest_api/index.php/Panda_b2b/receive_gr_no';
                        $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/receive_gr_no';
                        // echo $url;die;
                        $ch = curl_init($url);

                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

                        $result = curl_exec($ch);

                        //echo $result;die;
                        $output = json_decode($result);
                        curl_close($ch);
                        // echo 'asdads'.$output->status;die;

                        if($output->status == 'success')
                        {
                            $this->db->query("UPDATE backend.pomain SET uploaded = 3 , uploaded_at = '$date' WHERE RefNo = '$row2->PORefNo'");

                            //$this->db->query("INSERT INTO b2b_hub.error_log(trans_guid,module,refno,`message`,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'gr_completed_module','$row2->PORefNo','Success to flag status to B2B','HQ_grab',NOW())");
                        }
                        else
                        {
                            $this->db->query("INSERT INTO b2b_hub.error_log(trans_guid,module,refno,`message`,created_by,created_at) VALUES (upper(replace(uuid(),'-','')),'gr_completed_module','$row2->PORefNo','Error to flag status to B2B','HQ_grab',NOW())");
                        }
                        // die;
                    }//close foreach result
                }//close if count
                else
                {
                    $child_row++;
                    $gr_child_no .= $row->RefNo.',';
                    //$this->db->query("UPDATE backend.pomain SET uploaded = 4 , uploaded_at = '$date' WHERE RefNo = '$row->RefNo'");
                }

            }//close foreach
            //echo $child_row;die;
            if($child_row > 0)
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'Success'
                    ]
                );
                
            }
            else
            {
                $this->response(
                    [
                        'status' => TRUE,
                        'message' => 'Success upload'
                    ]
                );
            }
        }
        else
        {
            $this->response(
                [
                    'status' => TRUE,
                    'message' => 'No grn no found'
                ]
            );
        }
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
        WHERE LEFT(laststamp,10) > DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-%d'), INTERVAL - 1 MONTH)");
        
        print_r($data->num_rows());die;

        $username = 'admin'; //get from rest.php
        $password = '1234'; //get from rest.php

        // $url = 'http://127.0.0.1/b2b_upload_data/index.php/severside/supcus';
        $url = $this->b2b_ip.'/rest_api/index.php/panda_b2b/supcus';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data->result()));

        $result = curl_exec($ch);
        // echo $result;
        if($result == "true")
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
}

