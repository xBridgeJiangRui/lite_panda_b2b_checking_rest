<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Return_json extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        
        // $this->load->model('Panda_partner_model');
        
        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['account_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
    }


    function index_get()
    { 
      $refno  = $_REQUEST['refno'];
      $posts = $this->db->query("SELECT line,
        IF(pricetype='RTV','Item Not in PO',barcode) AS barcode,
        itemcode,itemlink,description,qty,netunitprice,packsize,totalprice,
        itemremark,a.refno,groupno,
        IF(purtolerance_std_plus=0 AND purtolerance_std_minus=0,'','Tolerance:') AS tolerance_desc,
        IF(purtolerance_std_plus=0 AND purtolerance_std_minus=0,'',CONCAT('/',um)) AS tolerance_desc1,
        IF(purtolerance_std_plus=0,'',purtolerance_std_plus) AS tolerance_plus,IF(purtolerance_std_minus=0,'',purtolerance_std_minus) AS tolerance_minus,
        disc1type,disc2type,IF(pricetype<>'FOC','','foc') AS pricetype,LOWER(um) AS um,
        discamt,
        IF(qty<bulkqty OR bulkqty=1,'',CONCAT('= ',IF(MOD(qty/bulkqty,1)=0,qty/bulkqty,ROUND(qty/bulkqty,1)),' ',umbulk)) AS ctn,
        IF(disc1value=0,'',IF(disc1type='%',CONCAT(ROUND(disc1value,2),disc1type),CONCAT(disc1type,ROUND(disc1value,2)))) AS disc1value,
        IF(disc2value=0,'',IF(disc2type='%',CONCAT(ROUND(disc2value,2),disc2type),CONCAT(disc2type,ROUND(disc2value,2)))) AS disc2value,

        CONCAT(IF(disc1value=0,'',IF(disc1type='%',CONCAT(IF(MOD(disc1value,1)=0,ROUND(disc1value),ROUND(disc1value,2)),disc1type),
        CONCAT(disc1type,ROUND(disc1value,2)))),IF(disc2value=0,'',IF(disc2type='%',CONCAT(' + ',IF(MOD(disc1value,2)=0,
        ROUND(disc2value),ROUND(disc2value,2)),disc2type),CONCAT(disc2type,ROUND(disc2value,2))))) AS disc_desc,
        IF(a.gst_tax_rate=0,'Z','S') AS gst_unit_code,
        ROUND(gst_tax_amount/qty,4) AS gst_unit_tax,

        ROUND(IF(hcost_gr=0,netunitprice+ROUND(gst_tax_amount/qty,4),((totalprice-ROUND(hcost_gr,2))+gst_tax_amount)/qty),4) AS gst_unit_cost,
        /*ROUND(IF(discvalue=0 AND surchgvalue=0,netunitprice+ROUND(gst_tax_amount/qty,4),((totalprice-ROUND(discvalue+surchgvalue,2))+gst_tax_amount)/qty),4) AS gst_unit_cost,*/

        gst_tax_amount AS gst_child_tax,

        ROUND(((totalprice-ROUND((hcost_gr),2))+gst_tax_amount),2) AS gst_unit_total,
        /*ROUND(((totalprice-ROUND((discvalue+surchgvalue),2))+gst_tax_amount),2) AS gst_unit_total,*/

        gst_tax_sum AS gst_main_tax,
        ROUND(total+gst_tax_sum,2) AS gst_main_total,
        CONCAT(packsize,IF(bulkqty=1,'',CONCAT('/',bulkqty))) AS ps,

        unitprice,bulkqty,articleno,
        IF(in_kind=1,'Total Stock In-Kind','Total Before Tax') AS total_desc,
        gst_tax_code,a.gst_tax_rate,

        IF(LENGTH(MID(gst_tax_amount,POSITION('.' IN gst_tax_amount)+1,10))<=2,FORMAT(gst_tax_amount,2),
        FORMAT(gst_tax_amount,4)) AS gst_tax_amount,

        ROUND((hcost_gr)/qty,4) AS unit_disc_prorate,
        ROUND(IF(hcost_gr=0,netunitprice,(totalprice-(hcost_gr))/qty),4) AS unit_price_bfr_tax,
        /*ROUND((discvalue+surchgvalue)/qty,4) AS unit_disc_prorate,
        ROUND(IF(discvalue=0 AND surchgvalue=0,netunitprice,(totalprice-(discvalue+surchgvalue))/qty),4) AS unit_price_bfr_tax,*/

        porefno,poqty,pounitprice,pototalprice,poactcost

        FROM backend.grchild a
        INNER JOIN backend.grmain b
        ON a.refno=b.refno
        WHERE a.refno='$refno' AND qty<>0 /* if(pricetype='RTV',qty=0,qty<>0) AND billstatus=1 AND pay_by_invoice=0 */
        ORDER BY groupno,pricetype,line");

      if($posts->num_rows() > 0)
      {
        echo json_encode($posts->result()); 
      }
      else
      {
        $result = $this->db->query("SELECT 'No Records Found' as line");
        echo json_encode($result->result()); 
      }


    }

    function pochild_get()
    {
      $refno = $_REQUEST['refno'];

      $child_data = $this->db->query("SELECT pc.refno , pm.podate , pm.deliverdate , pc.itemcode , pc.description , pc.barcode ,pc.articleno , pc.qty , pc.um, pc.netunitprice , pc.gst_tax_code, pc.gst_tax_rate, pc.gst_tax_amount, pc.price_include_tax , pc.totalprice_include_tax
        , pm.location,
        pm.scode,
        pm.sname,
        pc.line,
        pm.expiry_date,
        pm.issuedby
        FROM backend.pochild AS pc
  
        INNER JOIN backend.pomain AS pm
        ON pc.refno = pm.refno
        WHERE pm.refno = '".$refno."'");

        foreach($child_data->result() as $value)
            {
               
                $data[] = array(
                   'refno' => $value->refno,           
                   'podate' => $value->podate,      
                   'deliverdate' => $value->deliverdate,  
                   'itemcode' => $value->itemcode,  
                   'description' => $value->description,
                   'barcode' => $value->barcode, 
                    'articleno' => $value->articleno,             
                    'qty' => $value->qty,  
                    'um' => $value->um,
                    'netunitprice' => $value->netunitprice,
                    'gst_tax_code' => $value->gst_tax_code,
                    'gst_tax_rate' => $value-> gst_tax_rate,
                    'gst_tax_amount'=> $value->gst_tax_amount,
                    'totalprice_include_tax' => $value->totalprice_include_tax,
                    'location' => $value->location, 
                    'scode' => $value->scode, 
                    'sname' => $value->sname, 
                    'line' => $value->line,  
                    'expiry_date' => $value->expiry_date,
                    'issuedby' => $value->issuedby,
 
                 );

                
                $json_data = json_encode($data);
                $json_data = json_encode(array('pochild' => $data));
            }

        echo $json_data;  
    }

    function childdata_get()
    {
        $refno = $_REQUEST['refno'];

        /*@@@@ GET Collection Batch, dbnotebatch_child @@@@@*/
        if($_REQUEST['table'] == 'dbnotebatch_child')
        {
            $set_row = $this->db->query("SET @line=0;");
            $child_data =  $this->db->query("  
                SELECT batch_no 
                , @line := @line +1 as line
                , itemcode
                , description
                , packsize
                , lastcost
		        , input_cost
                , averagecost
                , sellingprice
                , qty
                , um
                , ifnull(reason, '') as reason
                , scan_barcode as barcode
                from backend.dbnote_batch_c  as a inner join backend.dbnote_batch as b on a.dbnote_guid = b.dbnote_guid where 
                batch_no = '$refno' order by description asc ");
        }
        /*@@@@ GET GRCHILD @@@@@*/
        if($_REQUEST['table'] == 'grchild')
        {
          //$child_data = $this->db->query("SELECT * from backend.grchild where refno = '$refno'");
          $child_data = $this->db->query("SELECT line,
            IF(pricetype='RTV','Item Not in PO',barcode) AS barcode,
            itemcode,itemlink,description,qty,netunitprice,packsize,totalprice,
            itemremark,a.refno,groupno,
            IF(purtolerance_std_plus=0 AND purtolerance_std_minus=0,'','Tolerance:') AS tolerance_desc,
            IF(purtolerance_std_plus=0 AND purtolerance_std_minus=0,'',CONCAT('/',um)) AS tolerance_desc1,
            IF(purtolerance_std_plus=0,'',purtolerance_std_plus) AS tolerance_plus,IF(purtolerance_std_minus=0,'',purtolerance_std_minus) AS tolerance_minus,
            disc1type,disc2type,IF(pricetype<>'FOC','','foc') AS pricetype,LOWER(um) AS um,
            discamt,
            IF(qty<bulkqty OR bulkqty=1,'',CONCAT('= ',IF(MOD(qty/bulkqty,1)=0,qty/bulkqty,ROUND(qty/bulkqty,1)),' ',umbulk)) AS ctn,
            IF(disc1value=0,'',IF(disc1type='%',CONCAT(ROUND(disc1value,2),disc1type),CONCAT(disc1type,ROUND(disc1value,2)))) AS disc1value,
            IF(disc2value=0,'',IF(disc2type='%',CONCAT(ROUND(disc2value,2),disc2type),CONCAT(disc2type,ROUND(disc2value,2)))) AS disc2value,

            CONCAT(IF(disc1value=0,'',IF(disc1type='%',CONCAT(IF(MOD(disc1value,1)=0,ROUND(disc1value),ROUND(disc1value,2)),disc1type),
            CONCAT(disc1type,ROUND(disc1value,2)))),IF(disc2value=0,'',IF(disc2type='%',CONCAT(' + ',IF(MOD(disc1value,2)=0,
            ROUND(disc2value),ROUND(disc2value,2)),disc2type),CONCAT(disc2type,ROUND(disc2value,2))))) AS disc_desc,
            IF(a.gst_tax_rate=0,'Z','S') AS gst_unit_code,
            ROUND(gst_tax_amount/qty,4) AS gst_unit_tax,

            ROUND(IF(hcost_gr=0,netunitprice+ROUND(gst_tax_amount/qty,4),((totalprice-ROUND(hcost_gr,2))+gst_tax_amount)/qty),4) AS gst_unit_cost,
            /*ROUND(IF(discvalue=0 AND surchgvalue=0,netunitprice+ROUND(gst_tax_amount/qty,4),((totalprice-ROUND(discvalue+surchgvalue,2))+gst_tax_amount)/qty),4) AS gst_unit_cost,*/ gst_tax_amount AS gst_child_tax,

            ROUND(((totalprice-ROUND((hcost_gr),2))+gst_tax_amount),2) AS gst_unit_total,
            /*ROUND(((totalprice-ROUND((discvalue+surchgvalue),2))+gst_tax_amount),2) AS gst_unit_total,*/

   
            CONCAT(packsize,IF(bulkqty=1,'',CONCAT('/',bulkqty))) AS ps, 
            unitprice,bulkqty,articleno,
            /*IF(in_kind=1,'Total Stock In-Kind','Total Before Tax') AS total_desc,*/
            gst_tax_code,a.gst_tax_rate,
    
            IF(LENGTH(MID(gst_tax_amount,POSITION('.' IN gst_tax_amount)+1,10))<=2,FORMAT(gst_tax_amount,2),
            FORMAT(gst_tax_amount,4)) AS gst_tax_amount,

            ROUND((hcost_gr)/qty,4) AS unit_disc_prorate,
            ROUND(IF(hcost_gr=0,netunitprice,(totalprice-(hcost_gr))/qty),4) AS unit_price_bfr_tax,
            /*ROUND((discvalue+surchgvalue)/qty,4) AS unit_disc_prorate,
            ROUND(IF(discvalue=0 AND surchgvalue=0,netunitprice,(totalprice-(discvalue+surchgvalue))/qty),4) AS unit_price_bfr_tax,*/

            porefno,poqty,pounitprice,pototalprice,poactcost,inv_qty,inv_unitprice

            FROM backend.grchild a
            /*INNER JOIN backend.grmain b
            ON a.refno=b.refno*/
            WHERE a.refno='$refno' /*AND qty<>0 
            /* if(pricetype='RTV',qty=0,qty<>0) AND billstatus=1 AND pay_by_invoice=0 */
            ORDER BY line,groupno,pricetype");
        }// end grchild

        /*@@@@@ GET GRDN CN @@@@@*/
        if($_REQUEST['table'] == 'grdncn')
        {
          $child_data = $this->db->query("SELECT a.refno
            , a.refno_dn
            , a.line
            , a.transtype
            , a.location
            , a.itemcode
            , a.description
            , a.qty
            , a.inv_qty
            , a.inv_netunitprice
            , a.inv_totalprice
            , a.supplier
            , a.invno
            , a.dono
            , a.porefno
            , a.title2
            , a.grdn_note AS notes
            , a.pounitprice
            , a.invactcost
            , a.netunitprice
            , a.pototal
            , a.porefno
            , a.poqty
            , a.articleno
            , a.packsize
            , a.variance_amt
            , a.reason
            , b.gst_zero
            , gst_std
            , IF(pricetype='RTV','',barcode1) AS barcode,
            IF(LENGTH(MID(gst_tax_total,POSITION('.' IN gst_tax_total)+1,10))<=2,FORMAT(gst_tax_total,2),
            FORMAT(gst_tax_total,4)) AS gst_tax_total_1,
            'Total Amount Exclude Tax' AS title7,
            ROUND(variance_amt-var_total_disc+0.000001,2) AS total_gross
            
            FROM
            
            (
            SELECT '1' AS sort,'1' AS sort1,a.refno,groupno,line,itemcode,description,qty,inv_qty,inv_netunitprice,inv_totalprice,
            IF(pricetype='FOC' AND inv_netunitprice=0,CONCAT('PO Qty: ',IF(MOD(poqty_expected,1)=0,ROUND(poqty_expected),ROUND(poqty_expected,1))),'') AS pricetype_vendor,
            grdate,CONCAT(b.CODE,'-',b.NAME) AS supplier,
            ROUND(pounitprice,4) AS pounitprice,totalprice AS invactcost,
            netunitprice,ROUND(variance_qty/(poqty_expected-qty),4) AS factor,pototalprice AS pototal,
            invno,dono,
            IF(pricetype='RTV','Item Not in PO',porefno) AS porefno,
            
            poqty,barcode AS barcode1,articleno,packsize,b.remark,loc_desc AS location,
            IF(MOD(poqty_expected-qty,1)=0,poqty_expected-qty,ROUND(poqty_expected-qty,2)) AS qtyvar,
            IF(variance_qty>0,'x','') AS chk1,'' AS chk2,
            ROUND(variance_qty,2) AS variance_amt,IF(pricetype='FOC','FOC','') AS pricetype,
            receivedby,
            IF(reason='' OR reason IS NULL,IF(pricetype='foc','FOC Short Supplied',
            IF(pricetype='RTV','Wrong Item','Qty Short Supplied')),reason) AS reason,
            group_code,postby,
            CONCAT(b.refno,'-',IF(transtype='ghv','IAV',transtype)) AS refno_dn,
            'Goods Received Difference Advice' AS title1,
            'Quantity Short Supplied' AS title2,
            IF(billstatus=0,'Document Not Posted','Document Posted') AS doc_status,
            'Unit Price After Tax' AS title3,'Quantity' AS title4,'Unit Price Before Tax' AS title5,
            IF(billstatus=0,'',CONCAT('Document posted on ',DATE_FORMAT(b.postdatetime,'%d/%m/%y %h:%i:%s'),' by ',b.postby)) AS posted_on,
            CONCAT('Debit Note - Goods Received Difference Advice for ','Quantity Short Supplied') title_gst,
            CONCAT('Important Note : This Debit Advice is to notify your Company that qty received by us does not tallied with the qty specified in your Tax Invoice No ',invno,
            '.  Kindly issued us a credit note within 7 days from the date hereof failure which we will not proceed with payment of this invoice.') AS grdn_note,
            IF(a.gst_tax_rate=0,'Z','S') AS gst_unit_code,
            ROUND(gst_var_qty/IF(MOD(poqty_expected-qty,1)=0,poqty_expected-qty,ROUND(poqty_expected-qty,2)),4) AS gst_unit_tax,
            ROUND((variance_qty+gst_var_qty)/IF(MOD(poqty_expected-qty,1)=0,poqty_expected-qty,ROUND(poqty_expected-qty,2)),4) AS gst_unit_cost,
            ROUND((variance_qty+gst_var_qty),2) AS gst_unit_total,
            gst_var_qty AS gst_tax_total,
            ROUND(variance_qty+ROUND(gst_var_qty,4),2) AS gst_amt_total,
            transtype,'' AS title_inv,
            'GRDA Refno' AS title_refno,
            IF(tax_invoice=1,'Refno #2','') AS title_refno_2,
            IF(tax_invoice=1,'',b.refno) AS refno_barcode,
            IF(tax_invoice=1,c.refno2,'') AS refno_2,
            IF(tax_invoice=1,'Tax Invoice issued by',
            IF(transtype IN ('GQV','IAV'),'Goods Received Difference Advice issued by',IF(transtype='GRV','Purchase Rebate Incentive Debit Advice issued by',
            'Goods Received Debit Note issued by'))) AS title_grda,
            CONCAT('Supplier CN No: ',IF(c.sup_cn_no IS NULL,'',c.sup_cn_no)) AS sup_cn_no,
            CONCAT('CN Date: ',IF(c.sup_cn_date IS NULL,'',DATE_FORMAT(c.sup_cn_date,'%d/%m/%y'))) AS sup_cn_date,
            c.rounding_adj AS rounding_dncn,
            c.gst_adjust AS rounding_dncn_gst,
            a.gst_tax_code,
            ROUND(hcost_iv+0.000001,2)*-1 AS var_total_disc

            FROM backend.grchild a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            INNER JOIN backend.grmain_dncn c
            ON a.refno=c.refno
            LEFT JOIN backend.set_group_dept e
            ON a.dept=e.dept_code
            LEFT JOIN 
            (SELECT b.CODE,CONCAT(b.CODE,' - ',c.description) AS loc_desc FROM backend.location b
            INNER JOIN backend.locationgroup c
            ON b.locgroup=c.CODE
            WHERE b.CODE=(SELECT location FROM backend.grmain WHERE refno='$refno')
            GROUP BY b.CODE) d
            ON b.location=d.CODE
            WHERE a.refno='$refno' AND transtype='gqv'
            AND IF(pricetype='foc',qty<>poqty_expected,inv_qty<>qty)
            /* amend on 180421 due to everrise case 4mg1841343 IF(pricetype<>'foc',variance_qty<>0,qty<>poqty_expected) */


            UNION ALL
            
            SELECT '2' AS sort,'2' AS sort1,a.refno,groupno,line,itemcode,description,qty,inv_qty,inv_netunitprice,inv_totalprice,
            IF(pricetype='FOC' AND inv_netunitprice=0,pricetype,'') AS pricetype_vendor,grdate,CONCAT(b.CODE,'-',b.NAME) AS supplier,
            ROUND(pounitprice,4) AS pounitprice,totalprice AS invactcost,
            netunitprice,ROUND(variance_cost/inv_qty,4) AS factor,pototalprice AS pototal,
            invno,dono,porefno,poqty,barcode AS barcode1,articleno,packsize,b.remark,loc_desc AS location,inv_qty AS qtyvar,
            '' AS chk1,IF(variance_cost>0,'x','') AS chk2,
            ROUND(variance_cost,2) AS variance_amt,IF(pricetype='FOC','FOC','') AS pricetype,
            receivedby,'Price Overcharged' AS reason,group_code,postby,
            CONCAT(b.refno,'-','IAV') AS refno_dn,'Goods Received Difference Advice' AS title1,'Price Overcharged' AS title2,
            IF(billstatus=0,'Document Not Posted','Document Posted') AS doc_status,
            'Unit Price After Tax' AS title3,'Quantity' AS title4,'Unit Price Before Tax' AS title5,
            IF(billstatus=0,'',CONCAT('Document posted on ',DATE_FORMAT(b.postdatetime,'%d/%m/%y %h:%i:%s'),' by ',b.postby)) AS posted_on,
            CONCAT('Debit Note - Goods Received Difference Advice for ','Price Overcharged') title_gst,
            CONCAT('Important Note : This Debit Advice is to notify your Company that the price charged in your Tax Invoice No ',invno,
            ' is higher than our PO Price.  Kindly issued us a credit note within 7 days from the date hereof failure which we will not proceed with payment of this invoice.') AS grdn_note,
            IF(a.gst_tax_rate=0,'Z','S') AS gst_unit_code,
            ROUND(gst_var_cost/inv_qty,4) AS gst_unit_tax,
            ROUND((variance_cost+gst_var_cost)/inv_qty,4) AS gst_unit_cost,
            ROUND((variance_cost+gst_var_cost),2) AS gst_unit_total,
            gst_var_cost AS gst_tax_total,
            ROUND(variance_cost+ROUND(gst_var_cost,4),2) AS gst_amt_total,
            transtype,'' AS title_inv,
            'GRDA Refno' AS title_refno,
            IF(tax_invoice=1,'Refno #2','') AS title_refno_2,
            IF(tax_invoice=1,'',b.refno) AS refno_barcode,
            IF(tax_invoice=1,c.refno2,'') AS refno_2,
            IF(tax_invoice=1,'Tax Invoice issued by',
            IF(transtype IN ('GQV','IAV'),'Goods Received Difference Advice issued by',IF(transtype='GRV','Purchase Rebate Incentive Debit Advice issued by',
            'Goods Received Debit Note issued by'))) AS title_grda,
            CONCAT('Supplier CN No: ',IF(c.sup_cn_no IS NULL,'',c.sup_cn_no)) AS sup_cn_no,
            CONCAT('CN Date: ',IF(c.sup_cn_date IS NULL,'',DATE_FORMAT(c.sup_cn_date,'%d/%m/%y'))) AS sup_cn_date,
            c.rounding_adj AS rounding_dncn,
            c.gst_adjust AS rounding_dncn_gst,
            a.gst_tax_code,
            ROUND(hcost_iv+0.000001,2)*-1 AS var_total_disc


            FROM backend.grchild a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            INNER JOIN backend.grmain_dncn c
            ON a.refno=c.refno
            LEFT JOIN backend.set_group_dept e
            ON a.dept=e.dept_code
            LEFT JOIN 
            (SELECT b.CODE,CONCAT(b.CODE,' - ',c.description) AS loc_desc FROM backend.location b
            INNER JOIN backend.locationgroup c
            ON b.locgroup=c.CODE
            WHERE b.CODE=(SELECT location FROM backend.grmain WHERE refno='$refno')
            GROUP BY b.CODE) d
            ON b.location=d.CODE
            WHERE a.refno='$refno' AND variance_cost<>0 AND transtype='iav'


            UNION ALL 
            
            SELECT '3' AS sort,IF(rebate_value=0,'4','3') AS sort1,a.refno,a.groupno,a.line,itemcode,description,qty,inv_qty,inv_netunitprice,inv_totalprice,
            IF(pricetype='FOC' AND inv_netunitprice=0,pricetype,'') AS pricetype_vendor,grdate,CONCAT(b.CODE,'-',b.NAME) AS supplier,
            ROUND(pounitprice,4) AS pounitprice,totalprice AS invactcost,
            netunitprice,rebate_value AS factor,pototalprice AS pototal,
            invno,dono,porefno,poqty,barcode AS barcode1,articleno,packsize,b.remark,loc_desc AS location,1 AS qtyvar,
            '' AS chk1,'' AS chk2,
            ROUND(rebate_value,2) AS variance_amt,IF(pricetype='FOC','FOC','') AS pricetype,
            receivedby,IF(rebate_value=0,'','Rebate Incentive') AS reason,group_code,postby,
            CONCAT(b.refno,'-','GRV') AS refno_dn,'PO/GRN Debit Advice' AS title1,'By Invoice Item' AS title2,
            IF(billstatus=0,'Document Not Posted','Document Posted') AS doc_status,
            'Rebate Amt After Tax' AS title3,'Quantity' AS title4,'Rebate Amt Before Tax' AS title5,
            IF(billstatus=0,'',CONCAT('Document posted on ',DATE_FORMAT(b.postdatetime,'%d/%m/%y %h:%i:%s'),' by ',b.postby)) AS posted_on,
            
            IF(tax_invoice=1,'Tax Invoice',
            CONCAT('PO/GRN Debit Note for ','Rebate Incentive')) title_gst,
            
            IF(tax_invoice=1,'',
            CONCAT('Important Note : This Debit Advice is to notify your Company that your company has agreed to issue a Rebate CN for the above item purchased.  Kindly issued us a credit note within 7 days from the date hereof failure which we will not proceed with payment of this invoice.'))
            AS grdn_note,

            IF(a.gst_tax_rate=0,'Z','S') AS gst_unit_code,
            ROUND(gst_rebate_amt,2) AS gst_unit_tax,
            ROUND((rebate_value+gst_rebate_amt),2) AS gst_unit_cost,
            ROUND((rebate_value+gst_rebate_amt),2) AS gst_unit_total,
            gst_rebate_amt AS gst_tax_total,
            ROUND(rebate_value+ROUND(gst_rebate_amt,4),2) AS gst_amt_total,
            transtype,
            IF(tax_invoice=1,'Rebate Incentive','') AS title_inv,
            IF(tax_invoice=1,'Refno #1','GRDN Refno') AS title_refno,
            IF(tax_invoice=1,'Refno #2','') AS title_refno_2,
            IF(tax_invoice=1,'',b.refno) AS refno_barcode,
            IF(tax_invoice=1,h.refno2,'') AS refno_2,
            IF(tax_invoice=1,'Tax Invoice issued by',
            IF(transtype IN ('GQV','IAV'),'Goods Received Difference Advice issued by',IF(transtype='GRV','Purchase Rebate Incentive Debit Advice issued by',
            'Goods Received Debit Note issued by'))) AS title_grda,
            CONCAT('Supplier CN No: ',IF(h.sup_cn_no IS NULL,'',h.sup_cn_no)) AS sup_cn_no,
            CONCAT('CN Date: ',IF(h.sup_cn_date IS NULL,'',DATE_FORMAT(h.sup_cn_date,'%d/%m/%y'))) AS sup_cn_date,
            h.rounding_adj AS rounding_dncn,
            h.gst_adjust AS rounding_dncn_gst,
            a.gst_tax_code,
            ROUND(hcost_iv+0.000001,2)*-1 AS var_total_disc

            FROM backend.grchild a
            
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            
            INNER JOIN backend.grmain_dncn h
            ON b.refno=h.refno
            
            INNER JOIN 
            (SELECT a.refno,a.groupno,IF(a.groupno=0,a.line,b.line) AS line1,b.line AS line FROM 
            (SELECT refno,groupno,line FROM backend.grchild 
            WHERE rebate_value<>0 AND refno='$refno'
            GROUP BY groupno) a
            INNER JOIN backend.grchild b
            ON a.refno=b.refno AND a.groupno=b.groupno
            WHERE IF(b.groupno=0,rebate_value<>0,rebate_value>=0)
            GROUP BY groupno,line) c
            ON a.refno=c.refno AND a.line=c.line
            
            LEFT JOIN backend.set_group_dept e
            ON a.dept=e.dept_code
            
            LEFT JOIN 
            (SELECT b.CODE,CONCAT(b.CODE,' - ',c.description) AS loc_desc FROM backend.location b
            INNER JOIN backend.locationgroup c
            ON b.locgroup=c.CODE
            WHERE b.CODE=(SELECT location FROM backend.grmain WHERE refno='$refno')
            GROUP BY b.CODE) d
            ON b.location=d.CODE
            
            WHERE a.refno='$refno' AND transtype='GRV'
            
            UNION ALL
            
            SELECT '6' AS sort,'5' AS sort1,a.refno,0 AS groupno,0 AS line,'' AS itemcode,
            CONCAT(code_type,' ',
            IF(surcharge_disc_type='%',CONCAT(surcharge_disc_value,surcharge_disc_type),
            CONCAT(surcharge_disc_type,surcharge_disc_value)),' - by Debit Note') AS description,
            0 AS qty,0 AS inv_qty,0 AS inv_netunitprice,invnetamt_vendor AS inv_totalprice,
            '' AS pricetype_vendor,grdate,CONCAT(a.CODE,' - ',a.NAME) AS supplier,
            0 AS pounitprice,a.total AS invactcost,
            0 AS netunitprice,ROUND(ABS(value_calculated)/**value_factor*/,2) AS factor,pototal,
            invno,dono,porefno,0 AS poqty,'' AS barcode1,'' AS articleno,0 AS packsize,a.remark,
            loc_desc AS location,1 AS qtyvar,
            '' AS chk1,'' AS chk2,ROUND(ABS(value_calculated)/**value_factor*/,2) AS variance_amt,'' AS pricetype,
            receivedby,IF(share_cost=0,'Discount Income','Reduce Purchase Cost') AS reason,group_code,postby,
            CONCAT(a.refno,'-','GDV') AS refno_dn,'PO/GRN Debit Note' AS title1,'By Total Invoice' AS title2,
            IF(billstatus=0,'Document Not Posted','Document Posted') AS doc_status,
            'Amount After Tax' AS title3,'Quantity' AS title4,'Amount Debit Before Tax' AS title5,
            IF(billstatus=0,'',CONCAT('Document posted on ',DATE_FORMAT(a.postdatetime,'%d/%m/%y %h:%i:%s'),' by ',a.postby)) AS posted_on,
            
            IF(tax_invoice=1,'Tax Invoice',
            CONCAT('PO/GRN Debit Note for ','Total Invoice (Discount Income)')) title_gst,
            
            IF(tax_invoice=1,'',
            CONCAT('Important Note : This Debit Advice is to notify your Company that your company has agreed to issue a Credit Note for our PO refno ', porefno,'.  Kindly issued us a credit note within 7 days from the date hereof failure which we will not proceed with payment of this invoice.')) AS grdn_note,

            IF(b.gst_amt=0,'Z','S') AS gst_unit_code,
            ROUND(b.gst_amt,2) AS gst_unit_tax,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_unit_cost,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_unit_total,
            ROUND(b.gst_amt,4) AS gst_tax_total,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_amt_total,
            transtype,
            IF(tax_invoice=1,'GRDN Discount Income','') AS title_inv,
            IF(tax_invoice=1,'Refno #1','GRDN Refno') AS title_refno,
            IF(tax_invoice=1,'Refno #2','') AS title_refno_2,
            IF(tax_invoice=1,'',a.refno) AS refno_barcode,
            IF(tax_invoice=1,e.refno2,'') AS refno_2,
            IF(tax_invoice=1,'Tax Invoice issued by',
            IF(transtype IN ('GQV','IAV'),'Goods Received Difference Advice issued by',IF(transtype='GRV','Purchase Rebate Incentive Debit Advice issued by',
            'Goods Received Debit Note issued by'))) AS title_grda,
            CONCAT('Supplier CN No: ',IF(e.sup_cn_no IS NULL,'',e.sup_cn_no)) AS sup_cn_no,
            CONCAT('CN Date: ',IF(e.sup_cn_date IS NULL,'',DATE_FORMAT(e.sup_cn_date,'%d/%m/%y'))) AS sup_cn_date,
            e.rounding_adj AS rounding_dncn,
            e.gst_adjust AS rounding_dncn_gst,
            b.gst_tax_code,
            0 AS var_total_disc

            FROM backend.grmain a
            
            INNER JOIN backend.trans_surcharge_discount b
            ON a.refno=b.refno
            
            INNER JOIN backend.grmain_dncn e
            ON a.refno=e.refno
            
            INNER JOIN
            (SELECT a.refno,group_code,b.total AS pototal,porefno FROM backend.grchild a
            
            INNER JOIN backend.pomain b
            ON a.porefno=b.refno
            
            LEFT JOIN 
            backend.set_group_dept e
            ON a.dept=e.dept_code
            WHERE a.refno='$refno'
            GROUP BY refno) c
            ON a.refno=c.refno
            
            LEFT JOIN 
            (SELECT b.CODE,CONCAT(b.CODE,' - ',c.description) AS loc_desc FROM backend.location b
            INNER JOIN backend.locationgroup c
            ON b.locgroup=c.CODE
            WHERE b.CODE=(SELECT location FROM backend.grmain WHERE refno='$refno')
            GROUP BY b.CODE) d
            ON a.location=d.CODE
            
            WHERE a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND transtype='gdv' AND build_into_cost=0 AND value_factor<0


            UNION ALL
            
            SELECT '5' AS sort,'6' AS sort1,a.refno,0 AS groupno,0 AS line,'' AS itemcode,
            CONCAT(code_type,' ',
            IF(surcharge_disc_type='%',CONCAT(surcharge_disc_value,surcharge_disc_type),
            CONCAT(surcharge_disc_type,surcharge_disc_value)),' - by Debit Note') AS description,
            0 AS qty,0 AS inv_qty,0 AS inv_netunitprice,invnetamt_vendor AS inv_totalprice,
            '' AS pricetype_vendor,grdate,CONCAT(a.CODE,' - ',a.NAME) AS supplier,
            0 AS pounitprice,a.total AS invactcost,
            0 AS netunitprice,
            ROUND(ABS(value_calculated),2) AS factor,
            
            /*ROUND(value_calculated/* amended on 16-08-03 due to tf bergr16061826 *value_factor,2) AS factor, - amended on 16-08-17 due to KMNGR16080234*/
            pototal,
            invno,dono,porefno,0 AS poqty,'' AS barcode1,'' AS articleno,0 AS packsize,a.remark,
            loc_desc AS location,1 AS qtyvar,
            '' AS chk1,'' AS chk2,ROUND(ABS(value_calculated)/**value_factor*/,2) AS variance_amt,'' AS pricetype,
            receivedby,IF(share_cost=0,'Discount Income','Reduce Purchase Cost') AS reason,group_code,postby,
            CONCAT(a.refno,'-','GDS') AS refno_dn,'PO/GRN Debit Note' AS title1,'By Total Invoice' AS title2,
            IF(billstatus=0,'Document Not Posted','Document Posted') AS doc_status,
            'Amount After Tax' AS title3,'Quantity' AS title4,'Amount Debit Before Tax' AS title5,
            IF(billstatus=0,'',CONCAT('Document posted on ',DATE_FORMAT(a.postdatetime,'%d/%m/%y %h:%i:%s'),' by ',a.postby)) AS posted_on,
            
            IF(tax_invoice=1,'Tax Invoice',
            CONCAT('PO/GRN Debit Note for ','Total Invoice (Reduce Purchase Cost)')) title_gst,
            
            IF(tax_invoice=1,'',
            CONCAT('Important Note : This Debit Advice is to notify your Company that your company has agreed to issue a Credit Note for our PO refno ',
            porefno,'.  Kindly issued us a credit note within 7 days from the date hereof failure which we will not proceed with payment of this invoice.'))
            AS grdn_note,

            IF(b.gst_amt=0,'Z','S') AS gst_unit_code,
            ROUND(b.gst_amt,2) AS gst_unit_tax,
            ROUND((ABS(value_calculated)+ROUND(b.gst_amt,2)),2) AS gst_unit_cost,
            ROUND((ABS(value_calculated)+ROUND(b.gst_amt,2)),2) AS gst_unit_total,
            ROUND(b.gst_amt,4) AS gst_tax_total,
            ROUND((ABS(value_calculated)+ROUND(b.gst_amt,2)),2) AS gst_amt_total,
            transtype,
            IF(tax_invoice=1,'GRDN Purchase Cost Reduction','') AS title_inv,
            IF(tax_invoice=1,'Refno #1','GRDA Refno') AS title_refno,
            IF(tax_invoice=1,'Refno #2','') AS title_refno_2,
            IF(tax_invoice=1,'',a.refno) AS refno_barcode,
            IF(tax_invoice=1,e.refno2,'') AS refno_2,
            IF(tax_invoice=1,'Tax Invoice issued by',
            IF(transtype IN ('GQV','IAV'),'Goods Received Difference Advice issued by',IF(transtype='GRV','Purchase Rebate Incentive Debit Advice issued by',
            'Goods Received Debit Note issued by'))) AS title_grda,
            CONCAT('Supplier CN No: ',IF(e.sup_cn_no IS NULL,'',e.sup_cn_no)) AS sup_cn_no,
            CONCAT('CN Date: ',IF(e.sup_cn_date IS NULL,'',DATE_FORMAT(e.sup_cn_date,'%d/%m/%y'))) AS sup_cn_date,
            e.rounding_adj AS rounding_dncn,
            e.gst_adjust AS rounding_dncn_gst,
            b.gst_tax_code,
            0 AS var_total_disc
            
            FROM backend.grmain a
            
            INNER JOIN backend.trans_surcharge_discount b
            ON a.refno=b.refno
            
            INNER JOIN backend.grmain_dncn e
            ON a.refno=e.refno
            
            INNER JOIN
            (SELECT a.refno,group_code,b.total AS pototal,porefno FROM backend.grchild a
            
            INNER JOIN backend.pomain b
            ON a.porefno=b.refno
            
            LEFT JOIN 
            backend.set_group_dept e
            ON a.dept=e.dept_code
            WHERE a.refno='$refno'
            GROUP BY refno) c
            ON a.refno=c.refno
            
            LEFT JOIN 
            (SELECT b.CODE,CONCAT(b.CODE,' - ',c.description) AS loc_desc FROM backend.location b
            INNER JOIN backend.locationgroup c
            ON b.locgroup=c.CODE
            WHERE b.CODE=(SELECT location FROM backend.grmain WHERE refno='$refno')
            GROUP BY b.CODE) d
            ON a.location=d.CODE
            
            WHERE a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND transtype='gds' AND build_into_cost=1 AND value_factor<0

            UNION ALL
            
            SELECT '7' AS sort,'7' AS sort1,a.refno,0 AS groupno,0 AS line,'' AS itemcode,
            CONCAT(code_type,' ',
            IF(surcharge_disc_type='%',CONCAT(surcharge_disc_value,surcharge_disc_type),
            CONCAT(surcharge_disc_type,surcharge_disc_value)),' - by Credit Note') AS description,
            0 AS qty,0 AS inv_qty,0 AS inv_netunitprice,invnetamt_vendor AS inv_totalprice,
            '' AS pricetype_vendor,grdate,CONCAT(a.CODE,' - ',a.NAME) AS supplier,
            0 AS pounitprice,a.total AS invactcost,
            0 AS netunitprice,
            ROUND(ABS(value_calculated)/**value_factor*/,2) AS factor,
            pototal,
            invno,dono,porefno,0 AS poqty,'' AS barcode1,'' AS articleno,0 AS packsize,a.remark,
            loc_desc AS location,1 AS qtyvar,
            '' AS chk1,'' AS chk2,ROUND(ABS(value_calculated)/**value_factor*/,2) AS variance_amt,'' AS pricetype,
            receivedby,IF(share_cost=0,'Other Expenses','Purchase Cost') AS reason,group_code,postby,
            CONCAT(a.refno,'-','IVS') AS refno_dn,'PO/GRN Credit Note' AS title1,'By Total Invoice' AS title2,
            IF(billstatus=0,'Document Not Posted','Document Posted') AS doc_status,
            'Amount After Tax' AS title3,'Quantity' AS title4,'Amount Credit Before Tax' AS title5,
            IF(billstatus=0,'',CONCAT('Document posted on ',DATE_FORMAT(a.postdatetime,'%d/%m/%y %h:%i:%s'),' by ',a.postby)) AS posted_on,
            
            IF(tax_invoice=1,'Tax Invoice',
            CONCAT('PO/GRN Credit Note for ','Debit Note or Invoice Received')) title_gst,
            
            IF(tax_invoice=1,'',
            CONCAT('Important Note : This Credit Advice is to notify your Company to issue a Tax Invoice or Debit Note for our Purchase ',
            porefno,'.  Kindly issued us a Tax Invoice or Debit Note note within 7 days from the date hereof failure which we will not proceed with payment.'))
            AS grdn_note,

            IF(b.gst_amt=0,'Z','S') AS gst_unit_code,
            ROUND(b.gst_amt,2) AS gst_unit_tax,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_unit_cost,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_unit_total,
            ROUND(b.gst_amt,4) AS gst_tax_total,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_amt_total,
            transtype,
            IF(tax_invoice=1,'GRCN Purchase Cost','') AS title_inv,
            IF(tax_invoice=1,'Refno #1','GRCN Refno') AS title_refno,
            IF(tax_invoice=1,'Refno #2','') AS title_refno_2,
            IF(tax_invoice=1,'',a.refno) AS refno_barcode,
            IF(tax_invoice=1,e.refno2,'') AS refno_2,
            IF(tax_invoice=1,'Tax Invoice issued by',
            IF(transtype IN ('GQV','IAV'),'Goods Received Difference Advice issued by',IF(transtype='GRV','Purchase Rebate Incentive Debit Advice issued by',
            'Goods Received Credit Note issued by'))) AS title_grda,
            CONCAT('Supplier DN/Inv No: ',IF(e.sup_cn_no IS NULL,'',e.sup_cn_no)) AS sup_cn_no,
            CONCAT('DN/Inv Date: ',IF(e.sup_cn_date IS NULL,'',DATE_FORMAT(e.sup_cn_date,'%d/%m/%y'))) AS sup_cn_date,
            e.rounding_adj AS rounding_dncn,
            e.gst_adjust AS rounding_dncn_gst,
            b.gst_tax_code,
            0 AS var_total_disc
            
            FROM backend.grmain a
            
            INNER JOIN backend.trans_surcharge_discount b
            ON a.refno=b.refno
            
            INNER JOIN backend.grmain_dncn e
            ON a.refno=e.refno
            
            INNER JOIN
            (SELECT a.refno,group_code,b.total AS pototal,porefno FROM backend.grchild a
            
            INNER JOIN backend.pomain b
            ON a.porefno=b.refno
            
            LEFT JOIN 
            backend.set_group_dept e
            ON a.dept=e.dept_code
            WHERE a.refno='$refno'
            GROUP BY refno) c
            ON a.refno=c.refno
            
            LEFT JOIN 
            (SELECT b.CODE,CONCAT(b.CODE,' - ',c.description) AS loc_desc FROM backend.location b
            INNER JOIN backend.locationgroup c
            ON b.locgroup=c.CODE
            WHERE b.CODE=(SELECT location FROM backend.grmain WHERE refno='$refno')
            GROUP BY b.CODE) d
            ON a.location=d.CODE
            
            WHERE a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND transtype='ivs' AND build_into_cost=1 AND value_factor>0

            UNION ALL
            
            SELECT '8' AS sort,'8' AS sort1,a.refno,0 AS groupno,0 AS line,'' AS itemcode,
            CONCAT(code_type,' ',
            IF(surcharge_disc_type='%',CONCAT(surcharge_disc_value,surcharge_disc_type),
            CONCAT(surcharge_disc_type,surcharge_disc_value)),' - by Credit Note') AS description,
            0 AS qty,0 AS inv_qty,0 AS inv_netunitprice,invnetamt_vendor AS inv_totalprice,
            '' AS pricetype_vendor,grdate,CONCAT(a.CODE,' - ',a.NAME) AS supplier,
            0 AS pounitprice,a.total AS invactcost,
            0 AS netunitprice,
            ROUND(ABS(value_calculated)/**value_factor*/,2) AS factor,
            pototal,
            invno,dono,porefno,0 AS poqty,'' AS barcode1,'' AS articleno,0 AS packsize,a.remark,
            loc_desc AS location,1 AS qtyvar,
            '' AS chk1,'' AS chk2,ROUND(ABS(value_calculated)/**value_factor*/,2) AS variance_amt,'' AS pricetype,
            receivedby,IF(share_cost=0,'Other Expenses','Purchase Cost') AS reason,group_code,postby,
            CONCAT(a.refno,'-','IVN') AS refno_dn,'PO/GRN Credit Note' AS title1,'By Total Invoice' AS title2,
            IF(billstatus=0,'Document Not Posted','Document Posted') AS doc_status,
            'Amount After Tax' AS title3,'Quantity' AS title4,'Amount Credit Before Tax' AS title5,
            IF(billstatus=0,'',CONCAT('Document posted on ',DATE_FORMAT(a.postdatetime,'%d/%m/%y %h:%i:%s'),' by ',a.postby)) AS posted_on,
            
            IF(tax_invoice=1,'Tax Invoice',
            CONCAT('PO/GRN Credit Note for ','Debit Note or Invoice Received')) title_gst,
            
            IF(tax_invoice=1,'',
            CONCAT('Important Note : This Credit Advice is to notify your Company to issue a Tax Invoice or Debit Note for our Purchase ',
            porefno,'.  Kindly issued us a Tax Invoice or Debit Note note within 7 days from the date hereof failure which we will not proceed with payment.'))
            AS grdn_note,
            
            IF(b.gst_amt=0,'Z','S') AS gst_unit_code,
            ROUND(b.gst_amt,2) AS gst_unit_tax,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_unit_cost,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_unit_total,
            ROUND(b.gst_amt,4) AS gst_tax_total,
            ROUND((ABS(value_calculated)/**value_factor*/+ROUND(b.gst_amt,2)),2) AS gst_amt_total,
            transtype,
            IF(tax_invoice=1,'GRCN Purchase Cost','') AS title_inv,
            IF(tax_invoice=1,'Refno #1','GRCN Refno') AS title_refno,
            IF(tax_invoice=1,'Refno #2','') AS title_refno_2,
            IF(tax_invoice=1,'',a.refno) AS refno_barcode,
            IF(tax_invoice=1,e.refno2,'') AS refno_2,
            IF(tax_invoice=1,'Tax Invoice issued by',
            IF(transtype IN ('GQV','IAV'),'Goods Received Difference Advice issued by',IF(transtype='GRV','Purchase Rebate Incentive Debit Advice issued by',
            'Goods Received Credit Note issued by'))) AS title_grda,
            CONCAT('Supplier DN/Inv No: ',IF(e.sup_cn_no IS NULL,'',e.sup_cn_no)) AS sup_cn_no,
            CONCAT('DN/Inv Date: ',IF(e.sup_cn_date IS NULL,'',DATE_FORMAT(e.sup_cn_date,'%d/%m/%y'))) AS sup_cn_date,
            e.rounding_adj AS rounding_dncn,
            e.gst_adjust AS rounding_dncn_gst,
            b.gst_tax_code,
            0 AS var_total_disc


            FROM backend.grmain a
            
            INNER JOIN backend.trans_surcharge_discount b
            ON a.refno=b.refno
            
            INNER JOIN backend.grmain_dncn e
            ON a.refno=e.refno
            
            INNER JOIN
            (SELECT a.refno,group_code,b.total AS pototal,porefno FROM backend.grchild a
            
            INNER JOIN backend.pomain b
            ON a.porefno=b.refno
            
            LEFT JOIN 
            backend.set_group_dept e
            ON a.dept=e.dept_code
            WHERE a.refno='$refno'
            GROUP BY refno) c
            ON a.refno=c.refno
            
            LEFT JOIN 
            (SELECT b.CODE,CONCAT(b.CODE,' - ',c.description) AS loc_desc FROM backend.location b
            INNER JOIN backend.locationgroup c
            ON b.locgroup=c.CODE
            WHERE b.CODE=(SELECT location FROM backend.grmain WHERE refno='$refno')
            GROUP BY b.CODE) d
            ON a.location=d.CODE

            WHERE a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND transtype='IVN' AND build_into_cost=0 AND value_factor>0) a

            LEFT JOIN
            
            (SELECT sort,refno,SUM(gst_zero) AS gst_zero,SUM(gst_std) AS gst_std FROM 
            
            (SELECT '3' AS sort,a.refno,ROUND(SUM(rebate_value),2) AS gst_zero,0 AS gst_std FROM backend.grchild a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_rebate_amt=0 AND a.refno='$refno' AND rebate_value<>0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '3' AS sort,a.refno,0 AS gst_zero,ROUND(SUM(rebate_value),2) AS gst_std FROM backend.grchild a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_rebate_amt<>0 AND a.refno='$refno' AND rebate_value<>0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '1' AS sort,a.refno,ROUND(SUM(variance_qty),2) AS gst_zero,0 AS gst_std FROM backend.grchild a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_var_qty=0 AND a.refno='$refno' AND variance_qty<>0
            GROUP BY refno

            UNION ALL
            
            SELECT '1' AS sort,a.refno,0 AS gst_zero,ROUND(SUM(variance_qty),2) AS gst_std FROM backend.grchild a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_var_qty<>0 AND a.refno='$refno' AND variance_qty<>0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '2' AS sort,a.refno,ROUND(SUM(variance_cost),2) AS gst_zero,0 AS gst_std FROM backend.grchild a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_var_cost=0 AND a.refno='$refno' AND variance_cost<>0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '2' AS sort,a.refno,0 AS gst_zero,ROUND(SUM(variance_cost),2) AS gst_std FROM backend.grchild a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_var_cost<>0 AND a.refno='$refno' AND variance_cost<>0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '6' AS sort,a.refno,ROUND(SUM(ABS(value_calculated)/**value_factor*/),2) AS gst_zero,0 AS gst_std FROM backend.trans_surcharge_discount a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_amt=0 AND a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND build_into_cost=0 AND value_factor<0
            GROUP BY refno

            UNION ALL
            
            SELECT '6' AS sort,a.refno,0 AS gst_zero,ROUND(SUM(ABS(value_calculated)/**value_factor*/),2) AS gst_std FROM backend.trans_surcharge_discount a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_amt<>0 AND a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND build_into_cost=0 AND value_factor<0
            GROUP BY refno
            /**value_factor*/
            UNION ALL
            
            SELECT '5' AS sort,a.refno,ROUND(SUM(ABS(value_calculated)/**value_factor*/),2) AS gst_zero,0 AS gst_std FROM backend.trans_surcharge_discount a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_amt=0 AND a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND build_into_cost=1 AND value_factor<0
            GROUP BY refno
            
            UNION ALL/**value_factor*/
            
            SELECT '5' AS sort,a.refno,0 AS gst_zero,ROUND(SUM(ABS(value_calculated)/**value_factor*/),2) AS gst_std FROM backend.trans_surcharge_discount a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_amt<>0 AND a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND build_into_cost=1 AND value_factor<0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '7' AS sort,a.refno,ROUND(SUM(value_calculated/**value_factor*/),2) AS gst_zero,0 AS gst_std FROM backend.trans_surcharge_discount a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_amt=0 AND a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND build_into_cost=1 AND value_factor>0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '7' AS sort,a.refno,0 AS gst_zero,ROUND(SUM(value_calculated/**value_factor*/),2) AS gst_std FROM backend.trans_surcharge_discount a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_amt<>0 AND a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND build_into_cost=1 AND value_factor>0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '8' AS sort,a.refno,ROUND(SUM(value_calculated/**value_factor*/),2) AS gst_zero,0 AS gst_std FROM backend.trans_surcharge_discount a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_amt=0 AND a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND build_into_cost=0 AND value_factor>0
            GROUP BY refno
            
            UNION ALL
            
            SELECT '8' AS sort,a.refno,0 AS gst_zero,ROUND(SUM(value_calculated/**value_factor*/),2) AS gst_std FROM backend.trans_surcharge_discount a
            INNER JOIN backend.grmain b
            ON a.refno=b.refno
            WHERE gst_amt<>0 AND a.refno='$refno' AND trans_type=IF(pay_by_invoice=0,'grn','grninv') AND dn=1 AND build_into_cost=0 AND value_factor>0
            GROUP BY refno) a
            
            GROUP BY sort) b
            
            ON a.refno=b.refno AND a.sort=b.sort
            HAVING transtype = '".$_REQUEST['transtype']."'
            
            ORDER BY a.sort,sort1,groupno,pricetype,description,reason");
        } // end grdncn



         if($child_data->num_rows() > 0)
            {
              echo json_encode($child_data->result()); 
            }
            else
            {
              $result = $this->db->query("SELECT 'No Records Found' as line");
              echo json_encode($result->result()); 
            }
        

    }

    function postatus_get()
    {
       $refno = $_REQUEST['refno'];
       $child_data = $this->db->query("SELECT refno, uploaded, hq_update, billstatus, completed ,cancel FROM backend.pomain where refno = '$refno'");
        if($child_data->num_rows() == '0')
        {
        $status = array(
                  'status' => 'FALSE',
                  'message' => 'Ref No Does Not Exist',
                  );
                echo (json_encode($status)) ;
        }
        elseif($child_data->row('uploaded') == '2' 
        && $child_data->row('hq_update') == '3' 
        && $child_data->row('billstatus') == '1' 
        && $child_data->row('completed') == '0' 
        && $child_data->row('cancel') == '0' 
          )
        {
           $status = array(
           'status' => 'TRUE',
           'message' => 'Data Already Uploaded to Rexbridge',
           );
         echo (json_encode($status)) ;
        }
        elseif($child_data->row('uploaded') == '1' 
        && $child_data->row('hq_update') == '3' 
        && $child_data->row('billstatus') == '1' 
        && $child_data->row('completed') == '1' 
        && $child_data->row('cancel') == '0' 
          )
        {
          $status = array(
          'status' => 'TRUE',
          'message' => 'Data Already Uploaded to Rexbridge',
          );
        echo (json_encode($status)) ; 
        }
        elseif($child_data->row('uploaded') == '1' 
        && $child_data->row('hq_update') <= '0' 
        && $child_data->row('billstatus') == '1' 
        && $child_data->row('completed') == '0' 
        && $child_data->row('cancel') == '0' 
          )
        {
          $status = array(
          'status' => 'FALSE',
          'message' => 'Pending Data to Upload into Rexbridge',
          );
        echo (json_encode($status)) ;
        }
        elseif($child_data->row('uploaded') == '0' 
        && $child_data->row('hq_update') <= '0' 
        && $child_data->row('billstatus') == '1' 
        && $child_data->row('completed') == '0' 
        && $child_data->row('cancel') == '0' 
          )
        {
          $status = array(
          'status' => 'FALSE',
          'message' => 'Generating PDF at Client Server',
          );
        echo (json_encode($status)) ;
        }
        elseif($child_data->row('uploaded') >= '0' 
        && $child_data->row('hq_update') <= '0' 
        && $child_data->row('billstatus') == '1' 
        && $child_data->row('completed') == '1' 
        && $child_data->row('cancel') == '0' 
          )
        {
          $status = array(
          'status' => 'FALSE',
          'message' => 'Document will not be uploaded. Outlet already finish GRN process',
          );
            echo (json_encode($status)) ;
        }
        elseif($child_data->row('uploaded') == '0' 
        && $child_data->row('hq_update') == '0' 
        && $child_data->row('billstatus') == '0' 
        && $child_data->row('completed') == '0' 
        && $child_data->row('cancel') == '0' 
          )
        {
          $status = array(
          'status' => 'FALSE',
          'message' => 'Document not posted',
          );
            echo (json_encode($status)) ;
        }
        elseif($child_data->row('uploaded') == '0' 
        && $child_data->row('hq_update') == '3' 
        && $child_data->row('billstatus') == '1' 
        && $child_data->row('completed') == '1' 
        && $child_data->row('cancel') == '0' 
          )
        {
          $status = array(
          'status' => 'FALSE',
          'message' => 'GR Completed',
          );
            echo (json_encode($status)) ;
        }
       else
       {
        $status = array(
          'status' => 'FALSE',
          'message' => 'Status Not Found.Please contact Rexbridge with PO No.',
          );
        echo (json_encode($status)) ;
       }
    }

    public function checkreport_post()
    {
        // $RefNo = '';
        $report_type = $this->post('report_types');
        $supplier_name = $this->post('supplier_name');
        $table_column = $this->post('table_column');
        $where_column = $this->post('where_column');
        $location = $this->post('location');
        $start_date = $this->post('start_date');
        $end_date = $this->post('end_date');
        $date_type = $this->post('date_type');
        $RefNo = $this->post('RefNo');

        $table = $table_column;
        $where_condition = $where_column;

        $xlocation = rtrim($location,',');
        $xsupplier_name = rtrim($supplier_name,',');
        // echo $RefNo;die;

            if($RefNo != null || $RefNo != '')
            {

                $RefNo_array = explode(",",$RefNo);

                $RefNo_string = '';
                foreach($RefNo_array as $row)
                {
                    $RefNo_string .= "'".$row."'".",";
                }

                $xRefNo = rtrim($RefNo_string, ",");

            }
            else
            {
                $xRefNo = '';
            }
        // $result = $this->db->query("SELECT $table FROM b2b_summary.$report_type WHERE $where_condition = '$supplier_name' AND location IN($location)")->result();
        // $location = 'BER,SGS';
        // $start_date = '2018-02-01';
        // $end_date = '2018-02-05';
        if($report_type != 'supcus')
        {
        $this->db->query("SET @location = '$xlocation';");
        $this->db->query("SET @start_date = '$start_date';");
        $this->db->query("SET @end_date = '$end_date';");
        $this->db->query("SET @supplier_name = '$xsupplier_name';");
        $this->db->query('SET @RefNo = "$RefNo"');

        if($RefNo != '' || $RefNo != null)
        {
            $result = $this->db->query("SELECT $table_column FROM backend.$report_type WHERE
                CASE WHEN @supplier_name = '' THEN $where_column = $where_column ELSE FIND_IN_SET($where_column,@supplier_name)END 
                AND CASE WHEN @location = '' THEN location = location ELSE FIND_IN_SET(location,@location)END
                AND CASE WHEN @start_date != '' AND @end_date != '' THEN $date_type BETWEEN @start_date AND @end_date ELSE $date_type = $date_type END
                AND CASE WHEN @start_date != '' AND @end_date = '' THEN $date_type = @start_date ELSE $date_type = $date_type END
                AND CASE WHEN @Refno = '' THEN RefNo = RefNo ELSE RefNo IN($xRefNo) END
                ORDER BY $date_type DESC;")->result();
        }
        else
        {
                $result = $this->db->query("SELECT $table_column FROM backend.$report_type WHERE
                CASE WHEN @supplier_name = '' THEN $where_column = $where_column ELSE FIND_IN_SET($where_column,@supplier_name)END 
                AND CASE WHEN @location = '' THEN location = location ELSE FIND_IN_SET(location,@location)END
                AND CASE WHEN @start_date != '' AND @end_date != '' THEN $date_type BETWEEN @start_date AND @end_date ELSE $date_type = $date_type END
                AND CASE WHEN @start_date != '' AND @end_date = '' THEN $date_type = @start_date ELSE $date_type = $date_type END
                ORDER BY $date_type DESC;")->result();
        }

        }
        else
        {
        $this->db->query("SET @location = '$xlocation';");
        $this->db->query("SET @start_date = '$start_date';");
        $this->db->query("SET @end_date = '$end_date';");
        $this->db->query("SET @supplier_name = '$xsupplier_name';");

        $result = $this->db->query("SELECT $table_column FROM backend.$report_type WHERE CASE WHEN @supplier_name = '' THEN $where_column = $where_column ELSE FIND_IN_SET($where_column,@supplier_name)END;")->result();         
        }
        // echo $this->db->last_query();
        // echo 'haha'.$report_type.$supplier_name;
        
        $this->response(
            [
                'status' => true,
                'message' => 'success',
                'rresult' => $result,
                'last_query'=> $this->db->last_query()
            ]
        );
    }

    function po_grn_no_get()
    {
       $po_check_grn_refno = $_REQUEST['po_check_grn_refno'];
       $child_data = $this->db->query("SELECT * FROM backend.grchild WHERE PORefNo = '$po_check_grn_refno' GROUP BY RefNo");

       if($child_data->num_rows() > 0)
       {
          $status = array(
              'count'   => $child_data->num_rows(),
              'status' => 'TRUE',
              'message' => $child_data->result(),
          );
          echo (json_encode($status)) ;
      }
      else
      {
          $status = array(
              'count'   => 0,
              'status' => 'FALSE',
              'message' => 'GRN No Doesn'."'".'t exists',
          );
          echo (json_encode($status)) ;
      }

    } 

    function grn_po_no_get()
    {
       $grn_check_po_refno = $_REQUEST['grn_check_po_refno'];
       $child_data = $this->db->query("SELECT * FROM backend.grchild WHERE RefNo = '$grn_check_po_refno' GROUP BY RefNo");

       if($child_data->num_rows() > 0)
       {
          $status = array(
              'count'   => $child_data->num_rows(),
              'status' => 'TRUE',
              'message' => $child_data->result(),
          );
          echo (json_encode($status)) ;
      }
      else
      {
          $status = array(
              'count'   => 0,
              'status' => 'FALSE',
              'message' => 'PO No Doesn'."'".'t exists',
          );
          echo (json_encode($status)) ;
      }

    }

    function temp_data_post()
    {
        $json_data = file_get_contents("php://input");
        $result_array = json_decode($json_data, true);

        $this->db->query("DELETE FROM rest_api.b2b_temp_data where user_guid = '".$result_array[0]['user_guid']."'");

        $check_data = $this->db->query("SELECT * FROM rest_api.b2b_temp_data WHERE created_at <= DATE_ADD(NOW(), INTERVAL 1 HOUR)");
        
        if($check_data->num_rows() > 0)
        {

            $this->db->query("DELETE FROM rest_api.b2b_temp_data WHERE created_at <= DATE_ADD(NOW(), INTERVAL 1 HOUR)");
        }

        foreach($result_array as $row => $value)
        {

             $data[] = array(
                  'session_guid' => $value['session_guid'],
                  'user_guid' => $value['user_guid'],
                  'field' => $value['field'],
                  'value' => $value['value'],
                  'created_at' => $this->db->query("SELECT NOW() as now")->row("now"),
              );
              $insertqr = $this->db->replace_batch('rest_api.b2b_temp_data', $data);
        }
         $afrows = $this->db->affected_rows();
          if($afrows > 0)
          {
            $message = $this->db->query("SELECT 'true' as message;")->result();
            echo json_encode($message);
          }
          else
          {
            $message = $this->db->query("SELECT 'false' as message;")->result();
            echo json_encode($message);
            //echo "false";
          }
         //print_r($daily_array);die;
    }

    public function flag_invoice_generated_post()
    {
        $refno = $this->input->post('refno');
        $status = $this->input->post('status');
        $table = $this->input->post('table');
        $column = $this->input->post('column');
        $einvno = $this->input->post('einvno');
        $inv_date = $this->input->post('inv_date');

        // echo $refno.'--'.$status.'--'.$table;

        $this->db->query("UPDATE backend.$table SET $column = 'COMPLETED',b2b_sup_doc_no = '$einvno',ext_doc_date = '$inv_date' WHERE RefNo = '$refno'");


        if($this->db->affected_rows() > 0)
        {
            $this->response(
                [
                    'status' => 'success',
                    'message' => 'success',
                ]
            );
        }
        else
        {
            $this->response(
                [
                    'status' => 'failed',
                    'message' => 'failed',
                ]
            );
        }

        exit;

    }  

    //daniel add get prdn e-cn
    function batch_e_cn_get()
    {

            $refno = $_REQUEST['refno'];

            // echo $refno;die;

            $set_row = $this->db->query("SET @line=0;");

            $child_data =  $this->db->query("SELECT @line := @line +1 as line, itemcode,barcode,articleno,description,packsize,unitprice,
                unitprice AS netunitprice,qty,totalprice,IF(pricetype='foc','FOC','') AS pricetype,
                line,itemlink,a.refno,LOWER(um) AS um,subtotal1,

                IF(a.gst_tax_rate=0,'Z','S') AS gst_unit_code,
                ROUND(gst_tax_amount/qty,4) AS gst_unit_tax,

                ROUND(IF(surchg_disc_gst=0,unitprice+(gst_tax_amount/qty),((totalprice-surchg_disc_gst)+gst_tax_amount)/qty),4) AS gst_unit_cost,
                ROUND(IF(discvalue=0 AND surchg_value=0,unitprice+(gst_tax_amount/qty),((totalprice-discvalue)+gst_tax_amount)/qty),4) AS gst_unit_cost,gst_tax_amount AS gst_child_tax,
                ROUND((totalprice-surchg_disc_gst)+gst_tax_amount,2) AS gst_unit_total,
                ROUND((totalprice-discvalue)+gst_tax_amount,2) AS gst_unit_total,gst_tax_sum AS gst_main_tax,
                ROUND(amount+gst_tax_sum,2) AS gst_main_total,
                packsize AS ps,

                gst_tax_code,a.gst_tax_rate,gst_tax_amount,
                ROUND(surchg_disc_gst/qty,4) AS unit_disc_prorate,
                IF(surchg_disc_gst=0,unitprice,ROUND((totalprice-surchg_disc_gst)/qty,4)) AS unit_price_bfr_tax,
                ROUND(totalprice-surchg_disc_gst+0.000001,2) AS total_price_bfr_tax,

                /*ROUND(discvalue/qty,4) AS unit_disc_prorate,
                IF(discvalue=0 AND surchg_value=0,unitprice,ROUND((totalprice-discvalue)/qty,4)) AS unit_price_bfr_tax,
                ROUND(totalprice-discvalue+0.000001,2) AS total_price_bfr_tax,*/

                ori_inv_no,ori_inv_date,itemremark,reason

                FROM backend.dbnotechild a

                INNER JOIN backend.dbnotemain b
                ON a.refno=b.refno

                WHERE a.refno='$refno'
                ORDER BY line;");

                $header_data = $this->db->query("SELECT a.*,b.*,c.division FROM

                (SELECT CONCAT(a.CODE,' - ',a.NAME) AS supplier,
                location,
                refno,
                DATE_FORMAT(docdate,'%d/%m/%y %a') AS docdate,
                amount AS total,
                issuestamp,issuedby,
                IF(billstatus=0,'',DATE_FORMAT(postdatetime,'%d/%m/%y %H:%i:%s')) AS postdatetime,
                postby,a.laststamp,a.remark,
                c.add1,c.add2,c.add3,c.city,c.state,c.postcode,c.country,
                CONCAT(c.tel,IF(c.fax='' OR c.fax IS NULL,'',CONCAT('  Fax : ',c.fax))) AS contact,
                CONCAT('Doc Status : ',IF(billstatus=0,'Unpost','Posted')) AS doc_status,
                a.docno,a.pono,sup_cn_no,
                IF(sup_cn_no='' OR sup_cn_no IS NULL,'',DATE_FORMAT(sup_cn_date,'%d/%m/%y %a')) AS sup_cn_date,

                IF(ibt=1,'IBT CN No',
                IF(sctype='S','Supplier CN No','Customer CN No')) AS sup_cn_title,
                IF(ibt=1,'IBT CN Date',
                IF(sctype='S','Supplier CN Date','Customer CN Date')) AS sup_cn_date_title,

                IF(a.billstatus=1,'','XXX') AS chk,
                IF(a.billstatus=1,'','Document Not Posted') AS chk_1,


                IF(ibt=1,IF(sctype='s',CONCAT('Inter Branch Stock Return Outwards',IF(a.consign=0,' - Consignment','')),
                CONCAT('Inter Branch Stock Return Inwards',IF(a.consign=0,' - Consignment',''))),'') AS title_ibt,

                IF(ibt=0,IF(sctype='s',IF(a.consign=1,'Consignment Return Note to Supplier','Purchase Return Debit Note to Supplier'),
                IF(a.consign=1,'Debit Note to Customer - Consignment','Debit Note to Customer - Outright')),
                IF(ibt=2,IF(sctype='s',IF(a.consign=1,'Consignment Return Note to Supplier','Purchase Return Debit Note to Supplier'),
                IF(a.consign=1,'Debit Note to Customer - Consignment','Debit Note to Customer - Outright')),
                IF(a.consign=0,'DN - Inter Branch Transfer Outwards','Consignment Note DN - Inter Branch Tranfer Outwards'))) AS title,

                IF(billstatus=0,'Draft Copy','') AS draft,

                IF(ibt=0 AND sctype='s' AND a.consign=0,'Supplier Credit Note','') AS title_match_cn,

                /*IF(ibt=1,IF(ibt_gst=0,'Inter Branch Stock Transfer Outwards to','Inter Branch Stock Transfer Outwards with GST to'),
                IF(sctype='S',IF(ibt=2,IF(ibt_gst=0,'Debit to Inter Company Supplier','Debit to Inter Company Supplier with GST'),
                IF(a.Tax_code_purchase='NR','Debit to Non Registered GST Supplier',
                'Debit to Registered GST Supplier')),
                IF(ibt=2,IF(ibt_gst=0,'Debit to Inter Company Customer','Debit to Inter Company Customer with GST'),
                IF(a.tax_code_purchase='ES','Debit to Exempted Customer entitled to 0% Tax',
                'Debit to Customer with GST')))) AS title_gst,*/

                IF(ibt=1,IF(ibt_gst=0,'Inter Branch Stock Transfer Outwards to','Inter Branch Stock Transfer Outwards to'),
                IF(sctype='S',IF(ibt=2,IF(ibt_gst=0,'Debit to Inter Company Supplier','Debit to Inter Company Supplier'),
                IF(a.Tax_code_purchase='NR','Debit to Supplier',
                'Debit to Supplier')),
                IF(ibt=2,IF(ibt_gst=0,'Debit to Inter Company Customer','Debit to Inter Company Customer'),
                IF(a.tax_code_purchase='ES','Debit to Customer',
                'Debit to Customer')))) AS title_gst,

                IF(ibt=1,'Inter Branch Transfer Outwards Note Issued By',
                IF(ibt=2,'Inter Company Debit Note Issued By',
                'Debit Note Issued Ny')) AS title_issue,

                CONCAT(a.location,' - ',f.description) AS loc_desc,

                CONCAT('Co Reg No: ',reg_no,IF(gst_no='','',CONCAT('    GST Reg No: ',gst_no,
                IF((SELECT COUNT(DISTINCT(gst_tax_code)) AS gst_count FROM backend.dbnotechild a
                INNER JOIN backend.dbnotemain b
                ON a.refno=b.refno
                WHERE a.refno='$refno'
                GROUP BY a.refno)=1,CONCAT('    Tax Code: ',tax_code_purchase),'')))) reg_sup,

                doc_name_reg,
                IF(ibt=1,'Transfer Note No','Debit Note No') AS title_invno,
                IF(ibt=1,'IBT Branch Copy',IF(sctype='s',IF(ibt=2,'Inter Co Supplier Copy','Suppier Copy'),IF(ibt='2','Inter Co Customer Copy','Customer Copy'))) AS title_supcopy,
                IF(sctype='S','Sup Code','Cus Code') AS title_supcode,
                subdeptcode



                FROM backend.dbnotemain a

                INNER JOIN 
                (SELECT * FROM backend.supcus WHERE
                TYPE=(SELECT sctype FROM backend.dbnotemain WHERE refno='$refno')) c
                ON a.CODE=c.CODE

                INNER JOIN backend.location f
                ON a.location=f.CODE

                LEFT JOIN backend.set_gst_table g
                ON a.tax_code_purchase=g.gst_tax_code

                WHERE a.refno='$refno') a

                INNER JOIN

                (SELECT IF(remark IS NULL OR remark='',IF(branch_name ='' OR branch_name IS NULL,companyname,branch_name),remark)/
                IF(branch_name='' OR branch_name IS NULL,companyname,branch_name) AS companyname,
                (SELECT dnremark1 FROM backend.xsetup) AS dnremark1,
                (SELECT dnremark2 FROM backend.xsetup) AS dnremark2,
                (SELECT dnremark3 FROM backend.xsetup) AS dnremark3,
                IF(branch_add='' OR branch_add IS NULL,address1,'') AS address1,
                IF(branch_add='' OR branch_add IS NULL,address2,'') AS address2,
                IF(branch_add='' OR branch_add IS NULL,address3,'') AS address3,
                IF(branch_add='' OR branch_add IS NULL,CONCAT('Tel: ',tel,'    Fax: ',fax),CONCAT('Tel: ',branch_tel,'    Fax: ',branch_fax)) AS contactnumber,
                IF(branch_add='' OR branch_add IS NULL,'',branch_add) AS branch_add,
                CONCAT('Co Reg No: ',IF(reg_no='' OR reg_no IS NULL,comp_reg_no,reg_no),IF(branch_gst='' OR branch_gst IS NULL,IF(gst_no='','',CONCAT('    GST Reg No: ',gst_no)),
                CONCAT('    GST Reg No: ',branch_gst))) reg_no,
                a.refno, 
                Branch_name as branch_name
                FROM backend.dbnotemain a

                INNER JOIN backend.companyprofile

                LEFT JOIN 
                (SELECT a.refno,reg_no,gst_no AS branch_gst,name_reg,branch_add,branch_name,branch_tel,branch_fax 
                FROM backend.dbnotemain a
                INNER JOIN backend.cp_set_branch b
                ON a.locgroup=b.branch_code
                INNER JOIN backend.supcus c
                ON b.set_supplier_code=c.CODE
                WHERE refno='$refno') b

                ON a.refno=b.refno

                WHERE a.refno='$refno') b

                ON a.refno=b.refno

                LEFT JOIN
                (SELECT a.CODE,IF(c.group_code IS NULL,'Not Applicable',c.group_code) AS division FROM backend.subdept a
                INNER JOIN backend.department b
                ON a.mcode=b.CODE
                LEFT JOIN backend.set_group_dept c
                ON b.CODE=c.dept_code) c
                ON a.subdeptcode=c.CODE
                ;");



            if($child_data->num_rows() > 0)
            {
              // echo json_encode($child_data->result());
              $data2 = array(
                    'child' => $child_data->result(),
                    'header' => $header_data->result()
              ); 
              echo json_encode($data2);

            }
            else
            {
                $result = $this->db->query("SELECT 'No Records Found' as line");
                $data2 = array(
                    'child' => $result->result(),
                    'header' => $result->result(),
                ); 
              // $result = $this->db->query("SELECT 'No Records Found' as line");
                echo json_encode($data2); 
            }        

    }     

    public function po_status_to_hq_post()
    {
        $refno = $this->input->post('refno');
        $status = $this->input->post('status');
        $table = $this->input->post('table');
        $column = $this->input->post('column');

        // print_r($this->input->post());die;
        // echo $refno.'--'.$status.'--'.$table;die;

        $this->db->query("UPDATE backend.$table SET $column = '$status' WHERE RefNo = '$refno'");


        if($this->db->affected_rows() > 0)
        {
            $this->response(
                [
                    'status' => 'success',
                    'message' => 'success',
                ]
            );
        }
        else
        {
            $this->response(
                [
                    'status' => 'failed',
                    'message' => 'failed',
                ]
            );
        }

        exit;

    }

    public function stock_return_batch_to_hq_post()//daniel add 20200131
    {
        //$refno = $this->input->post('refno');
        //$status = $this->input->post('status');
        //$table = $this->input->post('table');
        //$column = $this->input->post('column');
	
	    //jiangrui add 20220524
	    $refno = $this->input->post('refno');
        $status = $this->input->post('status');
        $table = $this->input->post('table');
        $column = $this->input->post('column');
	    $column2 = $this->input->post('column2');
	    $action_date = $this->input->post('action_date');
        $accepted_by = $this->input->post('accepted_by');

        // print_r($this->input->post());die;
        // echo $refno.'--'.$status.'--'.$table;die;

        $this->db->query("UPDATE backend.$table SET $column = '$status' , $column2 = '$action_date' ,accepted_by = '$accepted_by' WHERE batch_no = '$refno'");

        //$this->db->query("UPDATE backend.$table SET $column = '$status' WHERE batch_no = '$refno'");


        if($this->db->affected_rows() > 0)
        {
            $this->response(
                [
                    'status' => 'success',
                    'message' => 'success',
                ]
            );
        }
        else
        {
            $this->response(
                [
                    'status' => 'failed',
                    'message' => 'failed',
                ]
            );
        }

        exit;

    }

    public function Document_get()
    {
        $doctype = $_REQUEST['doctype'];
        $doctime = $_REQUEST['doctime'];
        $supcode = $_REQUEST['supcode'];
        $refno = $_REQUEST['refno'];
        $file_format = $this->db->query("SELECT value FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' and type = 'file_format'")->row('value');
        $time_format_column = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'time_format_column'")->row('value');
        $time_format = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'time_format'")->row('value');
        $to_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'to_location'")->row('value');
        $to_location2 = $this->db->query("SELECT DATE_FORMAT('$doctime','%Y-%m') as value")->row('value');
        $cut = explode('_',$file_format);
        $file_name = '';
        $i = 1;
        foreach($cut as $row)
        {
            if($i == $time_format_column)
            {
                $time = $$row;
                $row = $this->db->query("SELECT DATE_FORMAT('$time','$time_format') as xdate")->row('xdate');
                $file_name .= $row.'_';
                // echo $this->db->last_query().'asd'.$row;die;
            }
            else
            {
                $file_name .= $$row.'_';
            }
            
            $i++;
        }
        $filename = rtrim($file_name,'_');
        // echo $file_name;die;
        // echo $file_format.$doctype.$doctime.$supcode.$refno;die;
        $file = $to_location.'/'.$to_location2.'/'.$filename.'.pdf'; 
        // echo $file;die;
        $filename =$filename.'.pdf'; 
          // header('Location: http://www.example.com/');
        // Header content type 
        header('Content-type: application/pdf'); 
          
        header('Content-Disposition: inline; filename="' . $filename . '"'); 
          
        header('Content-Transfer-Encoding: binary'); 
          
        header('Accept-Ranges: bytes'); 
          
        // Read the file 
        readfile($file);
        die;
    }   

     public function po_child_preview_post()
     {
        $refno = $this->input->post('refno');
        // $refno = '';
        $database = 'backend';
        $table = 'pochild';
        $result = $this->db->query("SELECT * FROM $database.$table WHERE refno = '$refno'");

        if($result->num_rows() > 0)
        {
            $this->response(
                    [
                        'status' => "true",
                        'message' => "success",
                        'result' => $result->result()
                    ]
            );
        }
        else
        {
            $this->response(
                    [
                        'status' => "false",
                        'message' => "unsuccess",
                        'result' => $result->result()
                    ]
            );            
        }
     } 

    public function Document_download_get()
    {
        $doctype = $_REQUEST['doctype'];
        $doctime = $_REQUEST['doctime'];
        $supcode = $_REQUEST['supcode'];
        $refno = $_REQUEST['refno'];
        $file_format = $this->db->query("SELECT value FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' and type = 'file_format'")->row('value');
        $time_format_column = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'time_format_column'")->row('value');
        $time_format = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'time_format'")->row('value');
        $to_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'navition' AND type = 'to_location'")->row('value');
        $to_location2 = $this->db->query("SELECT DATE_FORMAT('$doctime','%Y-%m') as value")->row('value');
        $cut = explode('_',$file_format);
        $file_name = '';
        $i = 1;
        foreach($cut as $row)
        {
            if($i == $time_format_column)
            {
                $time = $$row;
                $row = $this->db->query("SELECT DATE_FORMAT('$time','$time_format') as xdate")->row('xdate');
                $file_name .= $row.'_';
                // echo $this->db->last_query().'asd'.$row;die;
            }
            else
            {
                $file_name .= $$row.'_';
            }
            
            $i++;
        }
        $filename = rtrim($file_name,'_');
        // echo $file_name;die;
        // echo $file_format.$doctype.$doctime.$supcode.$refno;die;
        $file = $to_location.'/'.$to_location2.'/'.$filename.'.pdf'; 
        // echo $file;die;
        $filename =$filename.'.pdf'; 
          // header('Location: http://www.example.com/');
        // Header content type 
        $b64Doc = chunk_split(base64_encode(file_get_contents($file)));
        echo $b64Doc;die;
    } 

    public function Document_autocount_get()
    {
        $doctype = $_REQUEST['doctype'];
        $doctime = $_REQUEST['doctime'];
        $supcode = $_REQUEST['supcode'];
        $refno = $_REQUEST['refno'];

        $input_array = $this->db->query("SELECT * FROM b2b_doc.other_doc_mapping WHERE file_refno = '$refno' AND file_supcode = '$supcode'");

        $supcode = $input_array->row('file_supcode');
        $refno = $input_array->row('file_refno');

        $file_format = $this->db->query("SELECT value FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' and type = 'file_format'")->row('value');
        $time_format_column = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'time_format_column'")->row('value');
        $time_format = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'time_format'")->row('value');
        $to_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'to_location'")->row('value');
        $to_location2 = $this->db->query("SELECT DATE_FORMAT('$doctime','%Y-%m') as value")->row('value');
        $cut = explode('_',$file_format);
        $file_name = '';
        $i = 1;
        foreach($cut as $row)
        {
            if($i == $time_format_column)
            {
                $time = $$row;
                $row = $this->db->query("SELECT DATE_FORMAT('$time','$time_format') as xdate")->row('xdate');
                $file_name .= $row.'_';
                // echo $this->db->last_query().'asd'.$row;die;
            }
            else
            {
                $file_name .= $$row.'_';
                // echo $file_name;
            }
            
            $i++;
        }
        $filename = rtrim($file_name,'_');
        // echo $file_name;die;
        // echo $file_format.$doctype.$doctime.$supcode.$refno;die;
        $file = $to_location.'/'.$to_location2.'/'.$filename.'.pdf'; 
        // echo $file;die;
        $filename =$filename.'.pdf'; 
          // header('Location: http://www.example.com/');
        // Header content type 
        header('Content-type: application/pdf'); 
          
        header('Content-Disposition: inline; filename="' . $filename . '"'); 
          
        header('Content-Transfer-Encoding: binary'); 
          
        header('Accept-Ranges: bytes'); 
          
        // Read the file 
        readfile($file);
        die;
    }   

    public function Document_autocount_download_get()
    {
        $doctype = $_REQUEST['doctype'];
        $doctime = $_REQUEST['doctime'];
        $supcode = $_REQUEST['supcode'];
        $refno = $_REQUEST['refno'];

        $input_array = $this->db->query("SELECT * FROM b2b_doc.other_doc_mapping WHERE file_refno = '$refno' AND file_supcode = '$supcode'");

        $supcode = $input_array->row('file_supcode');
        $refno = $input_array->row('file_refno');

        $file_format = $this->db->query("SELECT value FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' and type = 'file_format'")->row('value');
        $time_format_column = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'time_format_column'")->row('value');
        $time_format = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'time_format'")->row('value');
        $to_location = $this->db->query("SELECT * FROM b2b_doc.b2b_setting_parameter WHERE module = 'autocount' AND type = 'to_location'")->row('value');
        $to_location2 = $this->db->query("SELECT DATE_FORMAT('$doctime','%Y-%m') as value")->row('value');
        $cut = explode('_',$file_format);
        $file_name = '';
        $i = 1;
        foreach($cut as $row)
        {
            if($i == $time_format_column)
            {
                $time = $$row;
                $row = $this->db->query("SELECT DATE_FORMAT('$time','$time_format') as xdate")->row('xdate');
                $file_name .= $row.'_';
                // echo $this->db->last_query().'asd'.$row;die;
            }
            else
            {
                $file_name .= $$row.'_';
            }
            
            $i++;
        }
        $filename = rtrim($file_name,'_');
        // echo $file_name;die;
        // echo $file_format.$doctype.$doctime.$supcode.$refno;die;
        $file = $to_location.'/'.$to_location2.'/'.$filename.'.pdf'; 
        // echo $file;die;
        $filename =$filename.'.pdf'; 
        $b64Doc = chunk_split(base64_encode(file_get_contents($file)));
        echo $b64Doc;die;
    }

    public function flag_prdn_ecn_post()
    {
        $refno = $this->input->post('refno');
        $ecn_sup_no = $this->input->post('ecn_sup_no');
        $ecn_sup_date = $this->input->post('ecn_sup_date');

        // echo $refno.'--'.$status.'--'.$table;
        $database = 'backend';
        $table = 'dbnotemain';
        // print_r($this->input->post());die;
        // $this->db->query("INSERT INTO daniel.test1 (refno,ecn_refno,ecn_date) VALUES ('$refno','$ecn_sup_no','$ecn_sup_date')");
        $this->db->query("UPDATE $database.$table SET b2b_sup_doc_no = '$ecn_sup_no',ext_doc_date = '$ecn_sup_date',b2b_status = 'COMPLETED' WHERE RefNo = '$refno'");


        if($this->db->affected_rows() > 0)
        {
            $this->response(
                [
                    'status' => 'success',
                    'message' => 'success',
                ]
            );
        }
        else
        {
            $this->response(
                [
                    'status' => 'failed',
                    'message' => 'failed',
                ]
            );
        }

        exit;

    }

    public function flag_grmain_ecn_post()
    {
        $refno = $this->input->post('refno');
        $ecn_sup_no = $this->input->post('ecn_sup_no');
        $ecn_sup_date = $this->input->post('ecn_sup_date');
        $transtype = $this->input->post('transtype');

        // echo $refno.'--'.$status.'--'.$table;
        $database = 'backend';
        $table = 'grmain_dncn';
        // print_r($this->input->post());die;
        // $this->db->query("INSERT INTO daniel.test1 (refno,ecn_refno,ecn_date) VALUES ('$refno','$ecn_sup_no','$ecn_sup_date')");
        // $result = $this->db->query("SELECT * FROM $database.$table WHERE RefNo = '$refno' AND transtype = '$transtype'");
        // echo $this->db->last_query();die;
        // print_r($result->result());die;
        $this->db->query("UPDATE $database.$table SET ext_doc_no = '$ecn_sup_no',ext_doc_date = '$ecn_sup_date' WHERE RefNo = '$refno' AND transtype = '$transtype'");


        if($this->db->affected_rows() > 0)
        {
            $this->response(
                [
                    'status' => 'success',
                    'message' => 'success',
                ]
            );
        }
        else
        {
            $this->response(
                [
                    'status' => 'failed',
                    'message' => 'failed',
                ]
            );
        }

        exit;

    }      
}
