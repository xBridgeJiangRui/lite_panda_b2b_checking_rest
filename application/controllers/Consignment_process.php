<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Consignment_process extends REST_controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Main_model');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set("Asia/Kuala_Lumpur");
    }

    public function index_get()
    {
        $date_from = $_GET['date_from'];
	$date_to = $_GET['date_to'];

        $result = $this->db->query("SELECT 
s_diff
,s_cost_amt
, s_inv_amt
, outlet
, `code`
, acc_refno
, supplier
,amount
, cost
, periodcode
, inv_amt
, diff
, `period`
, trans_guid
, export_at
, total_row
, total_posted
, concat_code
, sort_posted_trans_guid  
,
     IFNULL(IF(LENGTH(concat_code) > LENGTH(sort_posted_trans_guid), 
     SUBSTRING(concat_code, LENGTH(sort_posted_trans_guid)+1), 
     SUBSTRING(sort_posted_trans_guid, LENGTH(concat_code)+1)), '') AS diff_outlet
, posted_trans_guid 
, no_posted_trans_guid
FROM
(
SELECT 
  ROUND( SUM(a.cost) - SUM(IF(inv_amt IS NULL, 0, inv_amt)), 2 ) AS s_diff,
  SUM(a.cost) AS s_cost_amt,
  SUM(IF(inv_amt IS NULL, 0, inv_amt)) AS s_inv_amt,
  a.*,
  SUM(IF(inv_amt IS NULL, 0, inv_amt)) AS inv_amt,
  SUM(cost) - SUM(IF(inv_amt IS NULL, 0, inv_amt)) AS diff,
  periodcode AS PERIOD,
  trans_guid,
  export_at,
  COUNT(1) AS total_row,
  SUM(IF(trans_guid IS NULL, 0, 1)) AS total_posted,
  GROUP_CONCAT(a.outlet  ORDER BY a.outlet) AS concat_code,
  GROUP_CONCAT(b.outlet ORDER BY b.outlet) AS sort_posted_trans_guid,
  GROUP_CONCAT(b.trans_guid ORDER BY b.trans_guid) AS posted_trans_guid, 
  # GROUP_CONCAT(a.outlet ORDER BY a.outlet) AS sort_concat_code, 
  # LEVENSHTEIN(GROUP_CONCAT(a.outlet  ORDER BY a.outlet), GROUP_CONCAT(b.outlet ORDER BY b.outlet)) as testttt,
  GROUP_CONCAT(no_approval_trans_guid) AS no_posted_trans_guid
FROM
  (SELECT 
    a.outlet,
    a.CODE,
    a.acc_refno,
    IF(  a.CODE = 'AA/NA', 'AA/NA - NOT APPLICABLE', CONCAT(a.CODE, ' - ', NAME)  ) AS supplier,
    ROUND((amount), 2) AS amount,
    ROUND((cost), 2) AS cost,
    periodcode 
  FROM
    (SELECT 
      location_group AS Outlet,
      acc_refno,
      a.itemcode,
      #a.dept,
      ROUND(SUM(Cost_CS), 2) AS cost,
      ROUND( SUM(  ABS( sales_pos_amt_cs + sales_si_amt_cs )  ),  2 ) AS amount,
      a.itemtype,
      IF(sup_code IS NULL, 'AA/NA', sup_code) AS CODE,
      a.periodcode 
    FROM  report_summary.`sku_cs_date` a 
    WHERE bizdate BETWEEN '$date_from'
      AND '$date_to'
      AND a.consign = 1 
    GROUP BY Outlet,
      CODE ORDER BY outlet) a 
    INNER JOIN 
      ( SELECT  'AA/NA' AS CODE,
        'Not Applicable' AS NAME 
        UNION ALL 
      SELECT 
        a.code,
        NAME 
      FROM backend.supcus a 
      INNER JOIN backend.supcus_branch c 
      ON a.supcus_guid = c.supcus_guid 
      WHERE set_active = 1 
      AND a.consign = 1 
      GROUP BY CODE  ) c 
      ON a.CODE = c.CODE 
  GROUP BY Outlet, CODE ORDER BY outlet) a 
  LEFT JOIN 
    (SELECT 
      a.refno, b.outlet, supcus_code,
      ROUND(SUM(b.amount), 2) AS inv_amt,
      a.trans_guid,
      IF( export_account = 'ok', DATE_FORMAT(export_at, '%d/%m/%y'), '' ) AS export_at 
    FROM
      backend.acc_trans a 
      INNER JOIN backend.acc_trans_c2 b 
        ON a.trans_guid = b.trans_guid 
    WHERE a.trans_type = 'inv-cs' 
      AND date_trans BETWEEN '$date_from' 
      AND '$date_to'
      AND approval = 1 
    GROUP BY supcus_code, b.outlet ORDER BY b.outlet) b 
    ON a.Outlet = b.outlet  AND a.CODE = b.supcus_code 
  LEFT JOIN 
    (SELECT 
      a.refno,
      b.outlet,
      supcus_code,
      ROUND(SUM(b.amount), 2) AS no_approval_inv_amt,
      a.trans_guid AS no_approval_trans_guid,
      IF( export_account = 'ok', DATE_FORMAT(export_at, '%d/%m/%y'), '' ) AS no_approval_export_at 
    FROM
      backend.acc_trans a 
      INNER JOIN backend.acc_trans_c2 b 
        ON a.trans_guid = b.trans_guid 
    WHERE a.trans_type = 'inv-cs' 
      AND date_trans BETWEEN '$date_from' 
      AND '$date_to'
      AND approval = 0 
    GROUP BY supcus_code,
      b.outlet ORDER BY b.outlet) c 
    ON a.Outlet = c.outlet 
    AND a.CODE = c.supcus_code 
GROUP BY CODE  
) a 
where abs(s_diff) >= 1 ")->result();

        $json = array(
            'query_data' => $result,
        );

        $this->response($json);
    }

    public function reupload_sku_cs_date_post(){

	$refno_list = $_POST['refno'];

	$this->db->query("UPDATE b2b_hub.`acc_trans_code_sku_cs_date` SET exported = 0 WHERE refno IN ($refno_list) AND exported <> 0;");
    }
}
