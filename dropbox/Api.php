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

    public function upload_grn_no_get()
    {
        $customer_guid = $this->db->query("SELECT * FROM panda_b2b.acc WHERE acc_name = 'MY MYDIN SDN BHD' LIMIT 1");

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
            AND hq_update <= '3' 
            AND a.send = 0
            ORDER BY podate DESC LIMIT 300");

        if($refno->num_rows() > 0)
        {
        $child_row = 0;
        $gr_child_no = '';
            foreach($refno->result() as $row)
            {
                //echo $row->RefNo;die;
                $gr_child = $this->db->query("SELECT * FROM backend.grchild WHERE PORefNo = '$row->RefNo' GROUP BY PORefNo,RefNo");
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
                        );

                        $username = 'admin'; //get from rest.php
                        $password = '1234'; //get from rest.php

                        // $url = 'http://192.168.10.29/rest_api/index.php/Panda_b2b/receive_gr_no';
                        $url = 'http://52.163.112.202/rest_api/index.php/panda_b2b/receive_gr_no';
                        $ch = curl_init($url);

                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-KEY: 123456"));
                        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

                        $result = curl_exec($ch);

                        // echo $result;die;
                        $output = json_decode($result);
                        curl_close($ch);
                        // echo 'asdads'.$output->status;die;

                        if($output->status == 'success')
                        {
                            $this->db->query("UPDATE backend.pomain SET send = 2,send_at = NOW() WHERE RefNo = '$row2->PORefNo'");
                        }
                        else
                        {
                            $this->db->query("UPDATE backend.pomain SET send = 999,send_at = NOW() WHERE RefNo = '$row2->PORefNo'");
                            $this->db->query("INSERT INTO lite_b2b.rest_data_err_log (`guid`,`customer_guid`,`po_refno`,`gr_refno`,`inv_refno`,`created_at`,`type`) VALUES('$guid','$customer_guid','$po_refno','$grn_refno','$inv_refno',NOW(),'po_gr_inv_upload') ");
                        }

                        // die;
                    }//close foreach result
                }//close if count
        else
        {
            $child_row++;
            $gr_child_no .= $row->RefNo.',';
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
}

