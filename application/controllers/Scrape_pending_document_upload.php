<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Scrape_pending_document_upload extends REST_controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");

        $check_table_existed = $this->db->query("SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = 'rest_api' AND table_name = 'b2b_config'");
        if ($check_table_existed->row('table_count') <= 0) {
            $response = array(
                'status' => "false",
                'message' => "Database or Table not setup"
            );

            echo json_encode($response);
            die;
        }

        $b2b_ip_https = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'HTTP' AND isactive = 1");
        if ($b2b_ip_https->num_rows() <= 0) {
            $this->b2b_ip_https = '';
            $response = array(
                'status' => "false",
                'message' => "Protocol not setup"
            );

            echo json_encode($response);
            die;
        } else {
            $this->b2b_ip_https = $b2b_ip_https->row('value');
        }

        $b2b_public_ip = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'IP' AND isactive = 1");
        if ($b2b_public_ip->num_rows() <= 0) {
            $this->b2b_public_ip = '';
            $response = array(
                'status' => "false",
                'message' => "IP not setup"
            );

            echo json_encode($response);
            die;
        } else {
            $this->b2b_public_ip = $b2b_public_ip->row('value');
        }

        $b2b_ip_port = $this->db->query("SELECT * FROM rest_api.b2b_config WHERE code = 'PORT' AND isactive = 1");
        if ($b2b_ip_port->num_rows() <= 0) {
            $this->b2b_ip_port = '';
            $response = array(
                'status' => "false",
                'message' => "Port not setup"
            );

            echo json_encode($response);
            die;
        } else {
            $this->b2b_ip_port = $b2b_ip_port->row('value');
        }

        // echo $this->b2b_ip_https.'--'.$this->b2b_public_ip.'--'.$this->b2b_ip_port;
        $this->b2b_ip = $this->b2b_ip_https . $this->b2b_public_ip . $this->b2b_ip_port;
        // echo $this->b2b_ip;
        // die;
        // die;
        // $this->b2b_ip = 'http://52.163.112.202';
        // $this->b2b_ip = 'http://127.0.0.1';
    }

    public function index_get()
    {
        $AppPOST = json_decode(file_get_contents('php://input'), true);
        $uploaded_status = $_REQUEST['uploaded_status'];
        $uploaded_status_strb = $_REQUEST['uploaded_status_strb'];

        $getinfo = $this->db->query("SELECT date_start,customer_guid FROM rest_api.`run_once_config` WHERE active = '1' LIMIT 1");
        $date_start = $getinfo->row('date_start');
        $customer_guid = $getinfo->row('customer_guid');

        $total_po_pending = 0;
        $total_grn_pending = 0;
        $total_grda_pending = 0;
        $total_strb_pending = 0;
        $total_prdn_pending = 0;
        $total_prcn_pending = 0;
        $total_pdn_pending = 0;
        $total_pcn_pending = 0;
        $total_pci_pending = 0;
        $total_di_pending = 0;


        // ninso
        if ($customer_guid == '599348EDCB2F11EA9A81000C29C6CEB2') {
            $locgroup_in = "AND a.locgroup IN ('NKWH','NJWH')";
            $loc_group_in = "AND a.loc_group IN ('NKWH','NJWH')";
        }
        // Everrise
        else if ($customer_guid == 'D361F8521E1211EAAD7CC8CBB8CC0C93') {
            $locgroup_in = "AND a.locgroup NOT IN ('FC','KR')";
            $loc_group_in = "AND a.loc_group NOT IN ('FC','KR')";
        } else {
            $locgroup_in = '';
            $loc_group_in = '';
        }

        // PO
        $total_po_pending = $this->db->query("SELECT COUNT(a.RefNo) as total_po_pending
        FROM backend.pomain AS a
        INNER JOIN (
        SELECT a.Refno, ROUND(SUM(totalprice),2) AS t_price FROM backend.pomain a 
        INNER JOIN  backend.pochild  b 
        ON a.`RefNo` = b.`RefNo` 
        WHERE uploaded = '$uploaded_status' 
        AND billstatus = 1  
        AND ibt = '0' 
        AND podate >=  '$date_start' 
        GROUP BY RefNo )aa 
        ON a.refno = aa.refno 
        AND ROUND(a.`SubTotal1`,2) = aa.t_price
        WHERE uploaded = '$uploaded_status' 
        AND billstatus = 1  
        AND ibt = '0' 
        AND podate >=  '$date_start'
        $loc_group_in")->row('total_po_pending');

        // GRN
        $total_grn_pending = $this->db->query("SELECT COUNT(a.RefNo) as total_grn_pending
        FROM backend.grmain AS a
        INNER JOIN (
        SELECT a.Refno,ROUND(SUM(totalprice),2) AS t_price,ROUND((ROUND(SUM(totalprice),2) - ROUND(a.subtotal1,2)),2) AS v_price ,c.grn_ignore_totalcostvariance_value  
        FROM backend.grmain a 
        INNER JOIN backend.grchild b   
        ON a.`RefNo` = b.`RefNo` 
        JOIN backend.`xsetup` c
        WHERE uploaded = '$uploaded_status'
        AND billstatus = 1 
        AND export_account NOT IN ('OK','ok','Ok') 
        AND ibt = '0' 
        AND grdate >= '$date_start'
        GROUP BY refno) aa
        ON a.refno = aa.refno 
        INNER JOIN (SELECT * FROM backend.supcus) consign
        ON a.code = consign.code
        WHERE uploaded = '$uploaded_status' 
        AND billstatus = 1 
        AND export_account NOT IN ('OK','ok','Ok') 
        AND ibt = '0' 
        AND grdate >= '$date_start'
        AND v_price <= grn_ignore_totalcostvariance_value 
        AND v_price >= '0.00'
        $loc_group_in")->row('total_grn_pending');

        // GRDA
        $total_grda_pending = $this->db->query("SELECT COUNT(a.Refno) AS total_grda_pending
        FROM backend.grmain a 
        INNER JOIN backend.`grmain_dncn` b 
        ON a.`RefNo` = b.`RefNo` 
        WHERE a.`uploaded` IN ('1','2')
        AND b.export_account NOT IN ('OK', 'ok', 'Ok') 
        AND b.`uploaded` = '$uploaded_status'
        AND billstatus = 1 
        AND grdate >= '$date_start'
        $loc_group_in")->row('total_grda_pending');

        // STRB
        $total_strb_pending = $this->db->query("SELECT COUNT(batch_no) AS total_strb_pending
        FROM backend.dbnote_batch 
        WHERE hq_update = '$uploaded_status_strb'")->row('total_strb_pending');

        // PRDN
        $total_prdn_pending = $this->db->query("SELECT COUNT(a.Refno) AS total_prdn_pending
        FROM backend.dbnotemain a 
        INNER JOIN backend.`dbnotechild` b 
        ON a.`RefNo` = b.`RefNo` 
        WHERE billstatus = 1 
        AND export_account NOT IN ('OK', 'ok', 'Ok') 
        AND a.`uploaded` = '$uploaded_status'
        AND docdate >= '$date_start'
        $locgroup_in")->row('total_prdn_pending');

        // PRCN
        $total_prcn_pending = $this->db->query("SELECT COUNT(a.Refno) AS total_prcn_pending
        FROM backend.cnnotemain a 
        INNER JOIN backend.`cnnotechild` b 
        ON a.`RefNo` = b.`RefNo` 
        WHERE billstatus = 1 
        AND export_account NOT IN ('OK', 'ok', 'Ok') 
        AND a.`uploaded` = '$uploaded_status'
        AND docdate >= '$date_start'
        $locgroup_in")->row('total_prcn_pending');

        // PDN
        $total_pdn_pending = $this->db->query("SELECT COUNT(a.cndn_guid) AS total_pdn_pending
        FROM backend.cndn_amt AS a
        INNER JOIN (
        SELECT a.cndn_guid
        FROM backend.cndn_amt a 
        INNER JOIN  backend.`cndn_amt_c`  b 
        ON a.`cndn_guid` = b.`cndn_guid` 
        WHERE a.uploaded = '$uploaded_status' 
        AND posted = 1  
        AND docdate >= '$date_start' 
        GROUP BY a.cndn_guid )aa 
        ON a.cndn_guid = aa.cndn_guid 
        WHERE uploaded = '$uploaded_status' 
        AND posted = 1 
        AND docdate >= '$date_start'
        AND `trans_type` = 'PDNAMT'
        $loc_group_in")->row('total_pdn_pending');

        // PCN
        $total_pcn_pending = $this->db->query("SELECT COUNT(a.cndn_guid) AS total_pcn_pending
        FROM backend.cndn_amt AS a
        INNER JOIN (
        SELECT a.cndn_guid
        FROM backend.cndn_amt a 
        INNER JOIN  backend.`cndn_amt_c`  b 
        ON a.`cndn_guid` = b.`cndn_guid` 
        WHERE a.uploaded = '$uploaded_status' 
        AND posted = 1  
        AND docdate >= '$date_start' 
        GROUP BY a.cndn_guid )aa 
        ON a.cndn_guid = aa.cndn_guid 
        WHERE uploaded = '$uploaded_status'  
        AND posted = 1 
        AND docdate >=  '$date_start'
        AND `trans_type` = 'PCNAMT'
        $loc_group_in")->row('total_pcn_pending');

        // PCI
        $total_pci_pending = $this->db->query("SELECT COUNT(aa.`inv_refno`) AS total_pci_pending
        FROM(
            SELECT a.`inv_refno`,SUM(ROUND(ROUND(e.sold_bare_supplier + (e.qtyclaim_manual * bear_supplier),2) + gst_tax_amount,2)) AS total_bear_gst,a.`total_net` 
        FROM
          backend.promo_taxinv AS a 
          INNER JOIN backend.promo_taxinv_c AS b 
            ON a.taxinv_guid = b.`taxinv_guid` 
          INNER JOIN backend.promo_supplier AS c 
            ON a.refno = c.refno 
          INNER JOIN backend.promo_supplier_c d 
            ON c.pvc_guid = d.pvc_guid 
          INNER JOIN backend.promo_supplier_result e 
            ON d.pvc_guid_c = e.pvc_guid_c 
            AND a.`loc_group` = e.loc_group 
            AND b.`taxinv_c_guid` = e.`taxinv_c_guid` 
        WHERE docdate >= '$date_start'
          AND a.posted = '1' 
          AND a.uploaded = '$uploaded_status' 
          HAVING a.total_net = total_bear_gst
          )aa;")->row('total_pci_pending');

        // DI
        $total_di_pending = $this->db->query("SELECT COUNT(a.taxinv_guid) AS total_di_pending
        FROM backend.discheme_taxinv AS a 
        INNER JOIN backend.discheme_taxinv_c AS b 
        ON a.taxinv_guid = b.`taxinv_guid` 
        INNER JOIN backend.dischememain c 
        ON a.refno = c.refno 
        INNER JOIN backend.dischemechild d 
        ON c.refno = d.refno 
        AND a.refno_line = d.line 
        WHERE a.docdate >= '$date_start'
        AND a.posted = '1' 
        AND a.uploaded = '$uploaded_status'
        $loc_group_in")->row('total_di_pending');

        $json = array(
            'customer_guid' => $customer_guid,
            'PO' => $total_po_pending,
            'GRN' => $total_grn_pending,
            'GRDA' => $total_grda_pending,
            'STRB' => $total_strb_pending,
            'PRDN' => $total_prdn_pending,
            'PRCN' => $total_prcn_pending,
            'PDN' => $total_pdn_pending,
            'PCN' => $total_pcn_pending,
            'PCI' => $total_pci_pending,
            'DI' => $total_di_pending,
        );
        $this->response($json);
    }

    public function scrape_variance_get()
    {
        // PO variance
        $po_variance = $this->db->query("SELECT a.RefNo,a.subtotal1, a.t_price AS total_amt_pochild FROM  
        (SELECT gg.RefNo, gg.scode, ROUND(gg.`SubTotal1`,2) AS subtotal1, aa.t_price FROM backend.pomain AS gg
        INNER JOIN (
        SELECT a.Refno, ROUND(SUM(totalprice),2) AS t_price FROM backend.pomain a 
        INNER JOIN  backend.pochild  b 
        ON a.`RefNo` = b.`RefNo` 
        WHERE uploaded = 0 AND billstatus = 1  AND ibt = '0' AND podate >=  
        (SELECT date_start FROM rest_api.`run_once_config` WHERE active = '1' LIMIT 1) GROUP BY RefNo )
        aa 
        ON gg.refno = aa.refno AND ROUND(gg.`SubTotal1`,2) != aa.t_price
        WHERE uploaded = 0 AND billstatus = 1  AND ibt = '0' AND podate >=  
        (SELECT date_start FROM rest_api.`run_once_config` WHERE active = '1' LIMIT 1) GROUP BY gg.RefNo) a
        INNER JOIN backend.supcus AS c
        ON a.scode = c.code ");

        $json = array(
            'customer_guid' => 'Emart',
	    'po_variance' => $po_variance->result_array()
        );
        $this->response($json);
    }
}
