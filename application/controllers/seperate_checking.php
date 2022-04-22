<?php

require(APPPATH.'/libraries/REST_Controller.php');

class Severside extends REST_Controller{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
    }

    public function check_po_generating_pdf_get() 
    {  
        // $this->response([print_r($result->result())]);die;
        $timenow = date("Y-m-d H:i:s");

        $runtime = $this->db->query("SELECT * FROM lite_b2b.set_scheduler WHERE type = 'rest_check_po_pdf' AND active = 1");

        // print_r($result2->num_rows());
        // print_r($result->result());
        // echo $result->row('uploaded_at');
        $schedule_run_time = $runtime->row('next_run_datetime');

        if($timenow >= $schedule_run_time)
        {   
            $document_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'document_interval'")->row('reason');

            // echo $document_interval;die;
            $result = $this->db->query("SELECT DATE_ADD(MAX(uploaded_at), INTERVAL $document_interval HOUR) as uploaded_at FROM backend.pomain");
            // echo $result->row('uploaded_at');die;

            $result2 = $this->db->query("SELECT COUNT(a.Refno) FROM backend.pomain a INNER JOIN backend.`pochild` b ON a.`RefNo` = b.`RefNo` WHERE  uploaded = '0' and billstatus = 1 and completed = 0 and podate >= '2019-07-01' and ibt = '0' GROUP BY a.`RefNo`;");

                if($timenow >= $result->row('uploaded_at') && $result2->num_rows() > 0)
                {
                    // echo $timenow.'----'.$result->row('uploaded_at');
                    // echo 'reach one hour';
                    $programmer_email = $this->db->query("SELECT * FROM lite_b2b.programmer_email_list WHERE isactive = 1 ORDER BY seq ASC");
                    // echo $programmer_email->num_rows();
                    $fail_email_status = '';
                    foreach($programmer_email->result() as $row)
                    {
                        // echo $row->programmer_email;die;
                        $xemail_send_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'email_send_interval'")->row('reason');
                        $mail_status = $this->send_email($xemail_send_interval,'PO',$row->programmer_email,$row->programmer_name,'PO Pdf is not upload to rexbridge ');
                        // echo $mail_status;

                            if($mail_status == 'SMTP connect() failed.') 
                            {
                                $fail_email_status .= '1';
                            }
                            else
                            {
                                $fail_email_status .= ''; 
                            }  
                    }
                        
                    if($fail_email_status == '' || $fail_email_status == null)
                    {
                        $email_send_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'email_send_interval'")->row('reason');
                        $this->db->query("UPDATE lite_b2b.set_scheduler SET next_run_datetime = DATE_ADD(NOW(), INTERVAL $email_send_interval HOUR) WHERE type = 'rest_check_po_pdf'");
                        // echo $this->db->affected_rows();

                            if($this->db->affected_rows() > 0)
                            {
                                $this->response(
                                [
                                    'status' => TRUE,
                                    'message' => "Got pending PO generating pdf,email sent, scheduler next run time updated sucessfully\r\n"
                                ]
                                );
                            }
                            else
                            {
                                $this->response(
                                [
                                    'status' => FALSE,
                                    'message' => "Got pending PO generating pdf,email sent, but scheduler next run time not updated\r\n"
                                ]
                                );
                            }
                    }
                    else
                    {
                        $this->response(
                        [
                            'status' => FALSE,
                            'message' => "Got pending PO generating pdf,email not sent\r\n"
                        ]
                        );
                    }

                    // $this->db->query("UPDATE lite_b2b.set_scheduler SET next_run_datetime WHERE type = 'rest_check_po_pdf'");
                }
                else
                {
                    $this->response(
                    [
                        'status' => FALSE,
                        'message' => "No pending PO generating pdf\r\n"
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

    public function check_po_pending_data_upload_get() 
    {  
        // $this->response([print_r($result->result())]);die;
        $timenow = date("Y-m-d H:i:s");

        $runtime = $this->db->query("SELECT * FROM lite_b2b.set_scheduler WHERE type = 'rest_check_pending_data_upload' AND active = 1");

        // print_r($result2->num_rows());
        // print_r($result->result());
        // echo $result->row('uploaded_at');
        $schedule_run_time = $runtime->row('next_run_datetime');
        // echo $schedule_run_time;die;

        if($timenow >= $schedule_run_time)
        {   
            $document_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'document_interval'")->row('reason');

            // echo $document_interval;die;
            $result = $this->db->query("SELECT DATE_ADD(MAX(uploaded_at), INTERVAL $document_interval HOUR) as uploaded_at FROM backend.pomain");
            // echo $result->row('uploaded_at');die;

            $result2 = $this->db->query("SELECT COUNT(a.Refno) FROM backend.pomain a INNER JOIN backend.`pochild` b ON a.`RefNo` = b.`RefNo` WHERE  uploaded = '1' and billstatus = 1 and completed = 0 and podate >= '2019-07-01' and ibt = '0' GROUP BY a.`RefNo`;");
            // echo $result2->num_rows();die;

                if($timenow >= $result->row('uploaded_at') && $result2->num_rows() > 0)
                {
                    // echo $timenow.'----'.$result->row('uploaded_at');
                    // echo 'reach one hour';
                    $programmer_email = $this->db->query("SELECT * FROM lite_b2b.programmer_email_list WHERE isactive = 1 ORDER BY seq ASC");
                    // echo $programmer_email->num_rows();
                    $fail_email_status = '';
                    foreach($programmer_email->result() as $row)
                    {
                        // echo $row->programmer_email;die;
                        $xemail_send_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'email_send_interval'")->row('reason');
                        $mail_status = $this->send_email($xemail_send_interval,'PO',$row->programmer_email,$row->programmer_name,'PO data are not upload to rexbridge ');
                        // echo $mail_status;

                            if($mail_status == 'SMTP connect() failed.') 
                            {
                                $fail_email_status .= '1';
                            }
                            else
                            {
                                $fail_email_status .= ''; 
                            }  
                    }
                        
                    if($fail_email_status == '' || $fail_email_status == null)
                    {
                        $email_send_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'email_send_interval'")->row('reason');
                        $this->db->query("UPDATE lite_b2b.set_scheduler SET next_run_datetime = DATE_ADD(NOW(), INTERVAL $email_send_interval HOUR) WHERE type = 'rest_check_pending_data_upload'");
                        // echo $this->db->affected_rows();

                            if($this->db->affected_rows() > 0)
                            {
                                $this->response(
                                [
                                    'status' => TRUE,
                                    'message' => "Got pending PO to upload data to rexbridge,email sent, scheduler next run time updated sucessfully\r\n"
                                ]
                                );
                            }
                            else
                            {
                                $this->response(
                                [
                                    'status' => FALSE,
                                    'message' => "Got pending PO to upload data to rexbridge,email sent, but scheduler next run time not updated\r\n"
                                ]
                                );
                            }
                    }
                    else
                    {
                        $this->response(
                        [
                            'status' => FALSE,
                            'message' => "Got pending PO to upload data to rexbridge,email not sent\r\n"
                        ]
                        );
                    }

                    // $this->db->query("UPDATE lite_b2b.set_scheduler SET next_run_datetime WHERE type = 'rest_check_po_pdf'");
                }
                else
                {
                    $this->response(
                    [
                        'status' => FALSE,
                        'message' => "No pending PO data to upload to rexbridge\r\n"
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

    public function check_grn_generating_pdf_get() 
    {  
        // $this->response([print_r($result->result())]);die;
        $timenow = date("Y-m-d H:i:s");

        $runtime = $this->db->query("SELECT * FROM lite_b2b.set_scheduler WHERE type = 'rest_check_grn_pdf' AND active = 1");

        // print_r($result2->num_rows());
        // print_r($result->result());
        // echo $result->row('uploaded_at');
        $schedule_run_time = $runtime->row('next_run_datetime');
        // echo $schedule_run_time;die;

        if($timenow >= $schedule_run_time)
        {   
            $document_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'document_interval'")->row('reason');

            // echo $document_interval;die;
            $result = $this->db->query("SELECT DATE_ADD(MAX(uploaded_at), INTERVAL $document_interval HOUR) as uploaded_at FROM backend.grmain");
            // echo $result->row('uploaded_at');die;

            $result2 = $this->db->query("SELECT COUNT(a.Refno) FROM backend.grmain a INNER JOIN backend.`grchild` b ON a.`RefNo` = b.`RefNo` WHERE uploaded = 0 AND billstatus = 1 and export_account = 'Pending' and ibt = '0' AND grdate >= '2019-07-01' GROUP BY a.`RefNo`;");
            // print_r($result2->num_rows());die;

                if($timenow >= $result->row('uploaded_at') && $result2->num_rows() > 0)
                {
                    // echo $timenow.'----'.$result->row('uploaded_at');
                    // echo 'reach one hour';
                    $programmer_email = $this->db->query("SELECT * FROM lite_b2b.programmer_email_list WHERE isactive = 1 ORDER BY seq ASC");
                    // echo $programmer_email->num_rows();
                    $fail_email_status = '';
                    foreach($programmer_email->result() as $row)
                    {
                        // echo $row->programmer_email;die;
                        $xemail_send_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'email_send_interval'")->row('reason');
                        $mail_status = $this->send_email($xemail_send_interval,'GRN',$row->programmer_email,$row->programmer_name,'GRN Pdf is not upload to rexbridge ');
                        // echo $mail_status;

                            if($mail_status == 'SMTP connect() failed.') 
                            {
                                $fail_email_status .= '1';
                            }
                            else
                            {
                                $fail_email_status .= ''; 
                            }  
                    }
                        
                    if($fail_email_status == '' || $fail_email_status == null)
                    {
                        $email_send_interval = $this->db->query("SELECT * FROM lite_b2b.set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code = 'email_send_interval'")->row('reason');
                        $this->db->query("UPDATE lite_b2b.set_scheduler SET next_run_datetime = DATE_ADD(NOW(), INTERVAL $email_send_interval HOUR) WHERE type = 'rest_check_grn_pdf'");
                        // echo $this->db->affected_rows();

                            if($this->db->affected_rows() > 0)
                            {
                                $this->response(
                                [
                                    'status' => TRUE,
                                    'message' => "Got pending GRN generating pdf,email sent, scheduler next run time updated sucessfully\r\n"
                                ]
                                );
                            }
                            else
                            {
                                $this->response(
                                [
                                    'status' => FALSE,
                                    'message' => "Got pending GRN generating pdf,email sent, but scheduler next run time not updated\r\n"
                                ]
                                );
                            }
                    }
                    else
                    {
                        $this->response(
                        [
                            'status' => FALSE,
                            'message' => "Got pending GRN generating pdf,email not sent\r\n"
                        ]
                        );
                    }

                    // $this->db->query("UPDATE lite_b2b.set_scheduler SET next_run_datetime WHERE type = 'rest_check_po_pdf'");
                }
                else
                {
                    $this->response(
                    [
                        'status' => FALSE,
                        'message' => "No pending GRN generating pdf\r\n"
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

}
