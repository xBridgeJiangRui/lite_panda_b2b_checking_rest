/*
SQLyog Community v13.1.9 (64 bit)
MySQL - 5.7.31-34 : Database - b2b_hub
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`b2b_hub` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `b2b_hub`;

/*Table structure for table `acc_trans` */

CREATE TABLE `acc_trans` (
  `refno` varchar(32) NOT NULL,
  `b2b_status` smallint(6) DEFAULT '0',
  `date_trans` date DEFAULT NULL,
  `supcus_code` varchar(20) NOT NULL,
  `operation` varchar(10) DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`refno`),
  KEY ```supcus_code``` (`supcus_code`),
  KEY ```date_trans``` (`date_trans`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `acc_trans_c2` */

CREATE TABLE `acc_trans_c2` (
  `refno` varchar(32) NOT NULL,
  `line` varchar(5) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `operation` varchar(10) DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`refno`,`line`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `acc_trans_code_sku_cs_date` */

CREATE TABLE `acc_trans_code_sku_cs_date` (
  `refno` varchar(32) NOT NULL,
  `b2b_status` smallint(6) DEFAULT '0',
  `date_trans` date DEFAULT NULL,
  `supcus_code` varchar(20) NOT NULL,
  `operation` varchar(10) DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `acc_trans_last_imported_time` datetime DEFAULT NULL,
  PRIMARY KEY (`refno`),
  KEY `supcus_code` (`supcus_code`),
  KEY `date_trans` (`date_trans`),
  KEY `acc_trans_last_imported_time` (`acc_trans_last_imported_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `client_setting` */

CREATE TABLE `client_setting` (
  `guid` varchar(32) NOT NULL,
  `client_code` varchar(32) NOT NULL,
  `client_name` varchar(32) DEFAULT NULL,
  `client_url_link` varchar(128) DEFAULT NULL,
  `client_api_key` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`guid`,`client_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `companyprofile` */

CREATE TABLE `companyprofile` (
  `CompanyName` varchar(60) NOT NULL DEFAULT '',
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '0000-00-00 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`CompanyName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `consignment_e_invoice_main` */

CREATE TABLE `consignment_e_invoice_main` (
  `einv_guid` varchar(32) NOT NULL,
  `supcus_code` varchar(20) DEFAULT NULL,
  `unique_key` varchar(20) DEFAULT NULL,
  `einv_date` date DEFAULT '1000-01-01',
  `b2b_inv_no` varchar(100) DEFAULT NULL,
  `b2b_inv_date` date DEFAULT '1000-01-01',
  `gen_doc_date` date DEFAULT '1000-01-01',
  `total_amt` decimal(14,2) DEFAULT NULL,
  `total_incl_tax` decimal(14,2) DEFAULT NULL,
  `total_child_count` int(10) DEFAULT '0',
  `created_at` datetime DEFAULT '1000-01-01 00:00:00',
  `created_by` varchar(32) DEFAULT NULL,
  `updated_at` datetime DEFAULT '1000-01-01 00:00:00',
  `updated_by` varchar(32) DEFAULT NULL,
  `export_account` varchar(20) DEFAULT 'Pending',
  `exported_at` datetime DEFAULT NULL,
  `exported_by` varchar(20) DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT NULL,
  `exported_to_hq` smallint(6) DEFAULT '0',
  `exported_to_hq_at` datetime DEFAULT NULL,
  `exported_to_hq_by` varchar(32) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  PRIMARY KEY (`einv_guid`),
  KEY `unique_key` (`unique_key`),
  KEY `b2b_inv_no` (`b2b_inv_no`),
  KEY `exported_to_hq` (`exported_to_hq`),
  KEY `imported` (`imported`),
  KEY `supcus_code` (`supcus_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `consignment_e_invoices` */

CREATE TABLE `consignment_e_invoices` (
  `trans_guid` varchar(32) NOT NULL,
  `refno` varchar(32) DEFAULT NULL,
  `date_trans` date DEFAULT NULL,
  `supcus_code` varchar(20) DEFAULT NULL,
  `amount` double DEFAULT '0',
  `total_inc_tax` double(12,2) DEFAULT '0.00',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(32) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(32) DEFAULT NULL,
  `b2b_inv_no` varchar(100) NOT NULL,
  `b2b_inv_date` date DEFAULT '1001-01-01',
  `b2b_status` smallint(6) DEFAULT '0',
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `einv_guid` char(32) DEFAULT NULL,
  PRIMARY KEY (`trans_guid`),
  KEY `date_trans` (`date_trans`),
  KEY `supcus_code` (`supcus_code`),
  KEY `amount` (`amount`),
  KEY `created_at` (`created_at`),
  KEY `refno` (`refno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `cp_set_branch` */

CREATE TABLE `cp_set_branch` (
  `branch_guid` varchar(32) NOT NULL,
  `updated_at` datetime DEFAULT '0000-00-00 00:00:00',
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '0000-00-00 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`branch_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `doc_data_missing` */

CREATE TABLE `doc_data_missing` (
  `guid` char(32) NOT NULL,
  `Refno` varchar(32) NOT NULL,
  `type` varchar(10) NOT NULL,
  `postdatetime` datetime DEFAULT NULL,
  `reason` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` char(10) NOT NULL,
  `ticket_created` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `RefNo` (`Refno`),
  KEY `ticket_created` (`ticket_created`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `ecn_child` */

CREATE TABLE `ecn_child` (
  `trans_guid` varchar(32) DEFAULT NULL,
  `child_guid` varchar(32) DEFAULT NULL,
  `customer_guid` varchar(32) NOT NULL,
  `ecn_guid` varchar(32) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `refno` varchar(20) NOT NULL,
  `refno_dn` varchar(20) NOT NULL,
  `transtype` varchar(12) DEFAULT NULL,
  `location` varchar(32) DEFAULT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `itemcode` varchar(20) DEFAULT NULL,
  `barcode` varchar(32) DEFAULT NULL,
  `description` varchar(120) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `inv_qty` double DEFAULT NULL,
  `inv_netunitprice` double DEFAULT NULL,
  `inv_totalprice` double DEFAULT NULL,
  `supplier` varchar(120) DEFAULT NULL,
  `invno` varchar(32) DEFAULT NULL,
  `dono` varchar(32) DEFAULT NULL,
  `porefno` varchar(32) DEFAULT NULL,
  `title2` varchar(32) DEFAULT NULL,
  `notes` text,
  `pounitprice` double DEFAULT NULL,
  `invactcost` double DEFAULT NULL,
  `netunitprice` double DEFAULT NULL,
  `pototal` double DEFAULT NULL,
  `articleno` varchar(20) DEFAULT NULL,
  `packsize` double DEFAULT NULL,
  `variance_amt` double DEFAULT NULL,
  `reason` varchar(32) DEFAULT NULL,
  `tax_amount` double DEFAULT NULL,
  `total_gross` double DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT NULL,
  `posted` smallint(6) DEFAULT '0',
  `posted_at` datetime DEFAULT NULL,
  `posted_by` varchar(32) DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT NULL,
  PRIMARY KEY (`customer_guid`,`refno`,`refno_dn`,`line`),
  UNIQUE KEY `trans_guid` (`trans_guid`),
  KEY `exported` (`exported`),
  KEY `exported_at` (`exported_at`),
  KEY `ecn_guid` (`ecn_guid`),
  KEY `child_guid` (`child_guid`),
  KEY `posted` (`posted`),
  KEY `posted_at` (`posted_at`),
  KEY `posted_by` (`posted_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `ecn_main` */

CREATE TABLE `ecn_main` (
  `trans_guid` varchar(32) DEFAULT NULL,
  `customer_guid` varchar(32) NOT NULL,
  `ecn_guid` varchar(32) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `refno` varchar(20) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `ext_doc1` varchar(50) DEFAULT NULL,
  `ext_date1` date NOT NULL,
  `ecn_generated_date` date DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `tax_rate` double DEFAULT NULL,
  `tax_amount` double DEFAULT NULL,
  `total_incl_tax` double DEFAULT NULL,
  `revision` int(6) DEFAULT '0',
  `posted` smallint(6) DEFAULT '0',
  `posted_at` datetime DEFAULT NULL,
  `posted_by` varchar(32) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT NULL,
  PRIMARY KEY (`customer_guid`,`ecn_guid`),
  UNIQUE KEY `trans_guid` (`trans_guid`),
  KEY `type` (`type`),
  KEY `customer_guid` (`customer_guid`),
  KEY `posted` (`posted`),
  KEY `refno` (`refno`),
  KEY `ecn_guid` (`ecn_guid`),
  KEY `exported` (`exported`),
  KEY `exported_at` (`exported_at`),
  KEY `ecn_generated_date` (`ecn_generated_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `einv_child` */

CREATE TABLE `einv_child` (
  `trans_guid` varchar(32) DEFAULT NULL,
  `child_guid` varchar(32) NOT NULL,
  `einv_guid` varchar(32) DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `itemtype` varchar(20) DEFAULT NULL,
  `itemlink` varchar(20) DEFAULT NULL,
  `itemcode` varchar(30) DEFAULT NULL,
  `barcode` varchar(32) DEFAULT NULL,
  `description` varchar(120) DEFAULT NULL,
  `packsize` double DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `uom` char(10) DEFAULT NULL,
  `unit_price_before_disc` double DEFAULT NULL,
  `item_discount_description` varchar(120) DEFAULT NULL,
  `item_disc_amt` double DEFAULT NULL,
  `total_bill_disc_prorated` double DEFAULT NULL,
  `total_amt_excl_tax` double DEFAULT NULL,
  `total_tax_amt` double DEFAULT NULL,
  `total_amt_incl_tax` double DEFAULT NULL,
  `checked` smallint(6) DEFAULT '0',
  `checked_at` datetime DEFAULT NULL,
  `checked_by` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT NULL,
  `posted` smallint(6) DEFAULT '0',
  `posted_at` datetime DEFAULT NULL,
  `posted_by` varchar(32) DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT NULL,
  PRIMARY KEY (`child_guid`),
  UNIQUE KEY `trans_guid` (`trans_guid`),
  KEY `einv_guid` (`einv_guid`),
  KEY `line` (`line`),
  KEY `checked` (`checked`),
  KEY `exported` (`exported`),
  KEY `exported_at` (`exported_at`),
  KEY `child_guid` (`child_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `einv_main` */

CREATE TABLE `einv_main` (
  `trans_guid` varchar(32) DEFAULT NULL,
  `einv_guid` varchar(32) NOT NULL,
  `customer_guid` varchar(32) NOT NULL,
  `refno` varchar(20) DEFAULT NULL,
  `einvno` varchar(50) DEFAULT NULL,
  `invno` varchar(20) DEFAULT NULL,
  `dono` varchar(20) DEFAULT NULL,
  `einv_generated_date` date DEFAULT NULL,
  `inv_date` date DEFAULT NULL,
  `gr_date` date DEFAULT NULL,
  `revision` int(6) DEFAULT '0',
  `total_excl_tax` double DEFAULT NULL,
  `tax_amount` double DEFAULT NULL,
  `total_incl_tax` double DEFAULT NULL,
  `posted` smallint(6) DEFAULT '0',
  `posted_at` datetime DEFAULT NULL,
  `posted_by` varchar(20) DEFAULT NULL,
  `converted` smallint(6) DEFAULT '0',
  `converted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT NULL,
  PRIMARY KEY (`einv_guid`,`customer_guid`),
  UNIQUE KEY `trans_guid` (`trans_guid`),
  KEY `customer_guid` (`customer_guid`),
  KEY `refno` (`refno`),
  KEY `exported` (`exported`),
  KEY `exported_at` (`exported_at`),
  KEY `einv_guid` (`einv_guid`),
  KEY `einv_generated_date` (`einv_generated_date`),
  KEY `posted` (`posted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `error_log` */

CREATE TABLE `error_log` (
  `trans_guid` varchar(32) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `refno` varchar(100) DEFAULT NULL,
  `message` text,
  `created_by` varchar(32) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`trans_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `grmain` */

CREATE TABLE `grmain` (
  `refno` varchar(32) NOT NULL,
  `operation` varchar(10) DEFAULT NULL,
  `laststamp` datetime DEFAULT '1001-01-01 00:00:00',
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`refno`),
  KEY `laststamp` (`laststamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `location` */

CREATE TABLE `location` (
  `code` varchar(32) NOT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `locationgroup` */

CREATE TABLE `locationgroup` (
  `code` char(10) NOT NULL DEFAULT '',
  `description` char(50) DEFAULT NULL,
  `remark` char(50) DEFAULT NULL,
  `set_active` smallint(6) DEFAULT '1',
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '0000-00-00 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `post_log` */

CREATE TABLE `post_log` (
  `whs_code` varchar(10) DEFAULT NULL,
  `owner_code` varchar(10) DEFAULT NULL,
  `guid` varchar(32) NOT NULL,
  `module` varchar(30) NOT NULL,
  `type` varchar(30) DEFAULT NULL,
  `refno` varchar(50) NOT NULL,
  `panda_refno` varchar(30) DEFAULT NULL,
  `status` varchar(15) NOT NULL,
  `message` text,
  `session` varchar(32) NOT NULL,
  `date` date DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `post_data` text,
  `return_data` text,
  PRIMARY KEY (`guid`,`session`),
  KEY `whs_code` (`whs_code`,`owner_code`,`type`,`refno`,`panda_refno`,`status`,`session`,`date`,`datetime`),
  FULLTEXT KEY `post_data` (`post_data`,`return_data`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `post_log_c` */

CREATE TABLE `post_log_c` (
  `line_guid` varchar(32) NOT NULL,
  `guid` varchar(32) NOT NULL,
  `panda_refno` varchar(30) DEFAULT NULL,
  `refno` varchar(50) DEFAULT NULL,
  `line` double DEFAULT NULL,
  `itemcode` varchar(30) NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `message` text,
  `session` varchar(32) NOT NULL,
  PRIMARY KEY (`line_guid`,`guid`,`itemcode`,`session`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `set_gst_table` */

CREATE TABLE `set_gst_table` (
  `gst_guid` varchar(32) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`gst_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `sku_cs_date` */

CREATE TABLE `sku_cs_date` (
  `periodcode` varchar(20) NOT NULL DEFAULT '',
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:01',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:01',
  `log_created` smallint(6) DEFAULT '0',
  `log_exported_at` datetime DEFAULT '1001-01-01 00:00:01',
  PRIMARY KEY (`periodcode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `supcus` */

CREATE TABLE `supcus` (
  `type` char(1) NOT NULL,
  `code` varchar(15) NOT NULL,
  `LastStamp` datetime DEFAULT '0000-00-00 00:00:00',
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '0000-00-00 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`type`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `trans_logs` */

CREATE TABLE `trans_logs` (
  `guid` varchar(32) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `post_data` text,
  `response` text,
  `resp_flag` tinyint(2) DEFAULT '0',
  `date` date DEFAULT NULL,
  `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `resp_datetime` timestamp NULL DEFAULT NULL,
  `status` varchar(25) DEFAULT 'PENDING',
  PRIMARY KEY (`guid`),
  KEY `module` (`module`),
  KEY `status` (`url`,`resp_flag`,`date`,`status`),
  FULLTEXT KEY `message` (`response`,`post_data`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `trans_surcharge_discount` */

CREATE TABLE `trans_surcharge_discount` (
  `surcharge_disc_guid` varchar(32) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `operation` varchar(10) DEFAULT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`surcharge_disc_guid`),
  KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `transfer_log` */

CREATE TABLE `transfer_log` (
  `guid` varchar(32) NOT NULL,
  `module` varchar(30) NOT NULL,
  `period_code` varchar(50) NOT NULL,
  `doc_date` date NOT NULL DEFAULT '1001-01-01',
  `total_data` int(20) DEFAULT '0',
  `current_complete` int(20) DEFAULT '0',
  `transfer_status` smallint(6) DEFAULT '0',
  `created_by` varchar(30) DEFAULT '',
  `created_at` datetime DEFAULT '1001-01-01 00:00:00',
  `updated_by` varchar(30) DEFAULT '',
  `updated_at` datetime DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `xsetup` */

CREATE TABLE `xsetup` (
  `compname` varchar(32) NOT NULL,
  `imported` smallint(6) DEFAULT '0',
  `imported_at` datetime DEFAULT '1001-01-01 00:00:00',
  `exported` smallint(6) DEFAULT '0',
  `exported_at` datetime DEFAULT '1001-01-01 00:00:00',
  PRIMARY KEY (`compname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!50106 set global event_scheduler = 1*/;

/* Event structure for event `housekeeping_log` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `housekeeping_log` ON SCHEDULE EVERY 1 DAY STARTS '2023-07-15 17:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DELETE FROM b2b_hub.trans_logs WHERE `date` < DATE_SUB(CURDATE(),INTERVAL 2 DAY);
    -- housekeeping
    CALL b2b_hub.DeleteTableRecords('b2b_hub.post_log',2,'date','b2b_hub.post_log_c','guid');
  END */$$
DELIMITER ;

/* Event structure for event `insert_acc_trans_sku_cs_date_to_hub` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `insert_acc_trans_sku_cs_date_to_hub` ON SCHEDULE EVERY 5 MINUTE STARTS '2023-08-01 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
INSERT INTO b2b_hub.`acc_trans_code_sku_cs_date`
SELECT 
  a.refno,
  a.b2b_status,
  a.date_trans,
  a.supcus_code,
  'INSERT' AS operation,
  1 AS imported,
  NOW() AS imported_at ,0 AS exported,'1001-01-01 00:00:00' AS exported_at,
  a.`imported_at` AS acc_trans_last_imported_time
FROM
  b2b_hub.acc_trans a 
  LEFT JOIN b2b_hub.`acc_trans_code_sku_cs_date` b 
    ON a.refno = b.refno 
WHERE b.refno IS NULL ;
/*SELECT *
FROM
  b2b_hub.acc_trans a 
  INNER JOIN b2b_hub.`acc_trans_code_sku_cs_date` b 
    ON a.refno = b.refno WHERE a.imported_at <> b.acc_trans_last_imported_time;*/
/*UPDATE 
  b2b_hub.acc_trans a 
  INNER JOIN b2b_hub.`acc_trans_code_sku_cs_date` b 
    ON a.refno = b.refno SET b.operation = a.`operation`,
  b.imported = 1,
  b.imported_at = NOW(),
  b.exported = 0,
  b.exported_at = '1001-01-01 00:00:00',
  b.acc_trans_last_imported_time = a.imported_at 
WHERE a.imported_at <> b.acc_trans_last_imported_time;*/
UPDATE 
  b2b_hub.`acc_trans_code_sku_cs_date` SET exported = 0
WHERE exported = 9;
	END */$$
DELIMITER ;

/* Event structure for event `insert_acc_trans_to_hub` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `insert_acc_trans_to_hub` ON SCHEDULE EVERY 15 MINUTE STARTS '2023-08-01 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- SET @date_start = (SELECT date_start FROM rest_api.`run_once_config` WHERE active = '1' ORDER BY date_start DESC LIMIT 1);
    SET @initial_date = (SELECT date_start FROM rest_api.`run_once_config` WHERE active = '1' LIMIT 1);
    SET @date_start = (SELECT IF(DATE_SUB(CURDATE(),INTERVAL 2 MONTH) > @initial_date,DATE_SUB(CURDATE(),INTERVAL 2 MONTH),@initial_date) AS date_start);
    INSERT INTO b2b_hub.acc_trans
    SELECT bb.refno AS refno,0 AS b2b_status,bb.date_trans AS date_trans,bb.supcus_code AS supcus_code,'INSERT' AS operation, 1 
    AS imported,NOW() AS imported_at,0 AS exported,'1001-01-01 00:00:00' AS exported_at FROM (SELECT company_name,company_id,ROUND(SUM(a.cost)-SUM(IF(inv_amt IS NULL,0,inv_amt)),2) AS s_diff,SUM(a.cost) AS s_cost_amt,SUM(IF(inv_amt IS NULL,0,inv_amt)) AS s_inv_amt,a.*,SUM(IF(inv_amt IS NULL,0,inv_amt)) 
    AS inv_amt,SUM(cost)-SUM(IF(inv_amt IS NULL,0,inv_amt)) AS diff, periodcode  AS PERIOD,
    trans_guid,export_at,COUNT(1) AS total_row,SUM(IF(trans_guid IS NULL,0,1)) AS total_posted FROM
    (SELECT a.outlet,a.CODE,a.acc_refno,IF(a.CODE='AA/NA','AA/NA - NOT APPLICABLE',CONCAT(a.CODE,' - ',NAME)) AS supplier,
    ROUND((amount),2) AS amount,
    ROUND((cost),2) AS cost,periodcode FROM
    (SELECT location_group AS Outlet,
    acc_refno,
    a.itemcode,
    #a.dept,
    ROUND(SUM(Cost_CS),2) AS cost,
    ROUND(SUM(ABS(sales_pos_amt_cs+sales_si_amt_cs)),2) AS amount,
    a.itemtype,IF(sup_code IS NULL,'AA/NA',sup_code) AS CODE,
    a.periodcode
    FROM report_summary.`sku_cs_date` a
    WHERE bizdate BETWEEN DATE_FORMAT(DATE_ADD(NOW(),INTERVAL 0 MONTH),'%Y-%m-01') AND LAST_DAY(DATE_ADD(NOW(),INTERVAL 0 MONTH)) AND a.consign=1 
GROUP BY Outlet,CODE
    ) a
    INNER JOIN 
    (SELECT 'AA/NA' AS CODE,
    'Not Applicable' AS NAME
    UNION ALL
    SELECT a.code,NAME
    FROM backend.supcus a
    INNER JOIN backend.supcus_branch c
    ON a.supcus_guid=c.supcus_guid
    WHERE set_active=1 
    AND a.consign=1
    GROUP BY CODE) c
    ON a.CODE=c.CODE 
    GROUP BY Outlet,CODE
    ) a
    LEFT JOIN 
    (SELECT a.refno,b.outlet,supcus_code,ROUND(SUM(b.amount),2) AS inv_amt,
    a.trans_guid,IF(export_account='ok',DATE_FORMAT(export_at,'%d/%m/%y'),'') AS export_at
    ,a.company_name,a.company_id
    FROM backend.acc_trans a
    INNER JOIN 
    backend.acc_trans_c2 b
    ON a.trans_guid = b.trans_guid
    INNER JOIN backend.cp_set_branch c
    ON a.company_id = c.company_id
    WHERE a.trans_type='inv-cs' AND date_trans BETWEEN DATE_FORMAT(DATE_ADD(NOW(),INTERVAL 0 MONTH),'%Y-%m-01') AND LAST_DAY(DATE_ADD(NOW(),INTERVAL 0 MONTH)) 
    AND approval = 1
    GROUP BY supcus_code,b.outlet,a.company_id) b
    ON a.Outlet=b.outlet AND a.CODE=b.supcus_code
    GROUP BY CODE,b.company_id
    HAVING s_diff <= 1.00
    )aa
    INNER JOIN
    backend.acc_trans bb ON aa.code = bb.supcus_code AND aa.period = DATE_FORMAT(date_trans,'%Y-%m') AND aa.company_id = bb.company_id INNER JOIN backend.acc_trans_c2 cc 
    ON bb.`trans_guid` = cc.`trans_guid` LEFT JOIN b2b_hub.acc_trans dd ON bb.refno = dd.`refno` WHERE dd.refno IS NULL GROUP BY bb.refno;
    -- IF GOT AMENDMENT
    UPDATE backend.acc_trans a INNER JOIN b2b_hub.acc_trans b ON a.refno = b.refno SET a.b2b_status = '1',b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW() 
    WHERE b.exported = '1' AND a.b2b_status = '0';
    UPDATE b2b_hub.`acc_trans` a LEFT JOIN backend.`acc_trans` b ON a.`refno`= b.`refno` SET operation = 'DELETE', exported = '0', exported_at = '1001-01-01 00:00:00', imported_at = NOW()
    WHERE b.`refno` IS NULL; 
    UPDATE b2b_hub.`acc_trans` a LEFT JOIN backend.`acc_trans` b ON a.`refno`= b.`refno` SET operation = 'INSERT', exported = '0', exported_at = '1001-01-01 00:00:00', imported_at = NOW() WHERE a.operation = 'DELETE' AND b.`refno` IS NOT NULL;
    INSERT INTO b2b_hub.acc_trans_c2
    SELECT 
    a.refno,
    a.`line`,
    a.created_at,
    'INSERT' operation,
    '1' imported,
    NOW() imported_at,
    '0' exported,
    '1001-01-01 00:00:00' exported_at 
    FROM
    (SELECT 
      a.refno,
      c.line,
      c.created_at 
    FROM
      b2b_hub.acc_trans a 
      INNER JOIN backend.acc_trans_c2 c 
        ON a.refno = c.refno 
      LEFT JOIN b2b_hub.acc_trans_c2 b 
        ON a.refno = b.`refno` 
        AND c.`line` = b.`line` 
    WHERE b.`refno` IS NULL 
      AND b.`line` IS NULL 
      AND c.bizdate BETWEEN DATE_FORMAT(DATE_ADD(NOW(),INTERVAL 0 MONTH),'%Y-%m-01') AND LAST_DAY(DATE_ADD(NOW(),INTERVAL 0 MONTH))
      AND c.trans_type = 'INV-CS') a ;
    -- IF GOT AMENDMENT
    UPDATE backend.acc_trans_c2 a INNER JOIN b2b_hub.acc_trans_c2 b ON a.`refno` = b.`refno` AND a.line = b.line 
    SET b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW(), b.created_at = a.created_at 
    WHERE b.exported = '1' AND a.`created_at` <> b.`created_at`;
    UPDATE b2b_hub.`acc_trans_c2` a LEFT JOIN backend.`acc_trans_c2` b
    ON a.`refno`=b.`refno` AND a.`line` = b.`line`  SET operation = 'DELETE', exported = '0', exported_at = '1001-01-01 00:00:00', imported_at = NOW()
    WHERE b.`line` IS NULL;
    UPDATE b2b_hub.`acc_trans_c2` a LEFT JOIN backend.`acc_trans` b ON a.`refno`= b.`refno` SET operation = 'INSERT', exported = '0', exported_at = '1001-01-01 00:00:00', imported_at = NOW() WHERE a.operation = 'DELETE' AND b.`refno` IS NOT NULL;
    -- UPDATE b2b_hub.acc_trans_c2 SET operation = 'DELETE' WHERE refno IN (SELECT a.refno FROM b2b_hub.acc_trans_c2 a LEFT JOIN backend.acc_trans_c2 b ON a.refno=b.`refno` 
    -- AND a.`line` = b.`line` WHERE b.`line` IS NULL);
    -- IF GOT AMENDMENT
    -- UPDATE backend.acc_trans_c2 a INNER JOIN b2b_hub.acc_trans_c2 b ON a.refno = b.refno AND a.line = b.line SET b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW() 
    -- WHERE b.exported = '1';
    -- housekeeping
    CALL b2b_hub.DeleteTableRecords('b2b_hub.acc_trans',3,'date_trans','b2b_hub.acc_trans_c2','refno');
  END */$$
DELIMITER ;

/* Event structure for event `insert_acc_trans_to_hub_previous_month` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `insert_acc_trans_to_hub_previous_month` ON SCHEDULE EVERY 20 MINUTE STARTS '2023-08-01 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- SET @date_start = (SELECT date_start FROM rest_api.`run_once_config` WHERE active = '1' ORDER BY date_start DESC LIMIT 1);
    SET @initial_date = (SELECT date_start FROM rest_api.`run_once_config` WHERE active = '1' LIMIT 1);
    SET @date_start = (SELECT IF(DATE_SUB(CURDATE(),INTERVAL 2 MONTH) > @initial_date,DATE_SUB(CURDATE(),INTERVAL 2 MONTH),@initial_date) AS date_start);
    INSERT INTO b2b_hub.acc_trans
    SELECT bb.refno AS refno,0 AS b2b_status,bb.date_trans AS date_trans,bb.supcus_code AS supcus_code,'INSERT' AS operation, 1 
    AS imported,NOW() AS imported_at,0 AS exported,'1001-01-01 00:00:00' AS exported_at FROM (SELECT company_name,company_id,ROUND(SUM(a.cost)-SUM(IF(inv_amt IS NULL,0,inv_amt)),2) AS s_diff,SUM(a.cost) AS s_cost_amt,SUM(IF(inv_amt IS NULL,0,inv_amt)) AS s_inv_amt,a.*,SUM(IF(inv_amt IS NULL,0,inv_amt)) 
    AS inv_amt,SUM(cost)-SUM(IF(inv_amt IS NULL,0,inv_amt)) AS diff, periodcode  AS PERIOD,
    trans_guid,export_at,COUNT(1) AS total_row,SUM(IF(trans_guid IS NULL,0,1)) AS total_posted FROM
    (SELECT a.outlet,a.CODE,a.acc_refno,IF(a.CODE='AA/NA','AA/NA - NOT APPLICABLE',CONCAT(a.CODE,' - ',NAME)) AS supplier,
    ROUND((amount),2) AS amount,
    ROUND((cost),2) AS cost,periodcode FROM
    (SELECT location_group AS Outlet,
    acc_refno,
    a.itemcode,
    #a.dept,
    ROUND(SUM(Cost_CS),2) AS cost,
    ROUND(SUM(ABS(sales_pos_amt_cs+sales_si_amt_cs)),2) AS amount,
    a.itemtype,IF(sup_code IS NULL,'AA/NA',sup_code) AS CODE,
    a.periodcode
    FROM report_summary.`sku_cs_date` a
    WHERE bizdate BETWEEN DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 MONTH),'%Y-%m-01') AND LAST_DAY(DATE_ADD(NOW(),INTERVAL -1 MONTH)) AND a.consign=1 
GROUP BY Outlet,CODE
    ) a
    INNER JOIN 
    (SELECT 'AA/NA' AS CODE,
    'Not Applicable' AS NAME
    UNION ALL
    SELECT a.code,NAME
    FROM backend.supcus a
    INNER JOIN backend.supcus_branch c
    ON a.supcus_guid=c.supcus_guid
    WHERE set_active=1 
    AND a.consign=1
    GROUP BY CODE) c
    ON a.CODE=c.CODE 
    GROUP BY Outlet,CODE
    ) a
    LEFT JOIN 
    (SELECT a.refno,b.outlet,supcus_code,ROUND(SUM(b.amount),2) AS inv_amt,
    a.trans_guid,IF(export_account='ok',DATE_FORMAT(export_at,'%d/%m/%y'),'') AS export_at
    ,a.company_name,a.company_id
    FROM backend.acc_trans a
    INNER JOIN 
    backend.acc_trans_c2 b
    ON a.trans_guid = b.trans_guid
    INNER JOIN backend.cp_set_branch c
    ON a.company_id = c.company_id
    WHERE a.trans_type='inv-cs' AND date_trans BETWEEN DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 MONTH),'%Y-%m-01') AND LAST_DAY(DATE_ADD(NOW(),INTERVAL -1 MONTH)) 
    AND approval = 1
    GROUP BY supcus_code,b.outlet,a.company_id) b
    ON a.Outlet=b.outlet AND a.CODE=b.supcus_code
    GROUP BY CODE,b.company_id
    HAVING s_diff <= 1.00
    )aa
    INNER JOIN
    backend.acc_trans bb ON aa.code = bb.supcus_code AND aa.period = DATE_FORMAT(date_trans,'%Y-%m') AND aa.company_id = bb.company_id INNER JOIN backend.acc_trans_c2 cc 
    ON bb.`trans_guid` = cc.`trans_guid` LEFT JOIN b2b_hub.acc_trans dd ON bb.refno = dd.`refno` WHERE dd.refno IS NULL GROUP BY bb.refno;
    -- IF GOT AMENDMENT
    UPDATE backend.acc_trans a INNER JOIN b2b_hub.acc_trans b ON a.refno = b.refno SET a.b2b_status = '1',b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW() 
    WHERE b.exported = '1' AND a.b2b_status = '0';
    UPDATE b2b_hub.`acc_trans` a LEFT JOIN backend.`acc_trans` b ON a.`refno`= b.`refno` SET operation = 'DELETE', exported = '0', exported_at = '1001-01-01 00:00:00', imported_at = NOW()
    WHERE b.`refno` IS NULL; 
    UPDATE b2b_hub.`acc_trans` a LEFT JOIN backend.`acc_trans` b ON a.`refno`= b.`refno` SET operation = 'INSERT', exported = '0', exported_at = '1001-01-01 00:00:00', imported_at = NOW() WHERE a.operation = 'DELETE' AND b.`refno` IS NOT NULL;
    INSERT INTO b2b_hub.acc_trans_c2
    SELECT 
    a.refno,
    a.`line`,
    a.created_at,
    'INSERT' operation,
    '1' imported,
    NOW() imported_at,
    '0' exported,
    '1001-01-01 00:00:00' exported_at 
    FROM
    (SELECT 
      a.refno,
      c.line,
      c.created_at 
    FROM
      b2b_hub.acc_trans a 
      INNER JOIN backend.acc_trans_c2 c 
        ON a.refno = c.refno 
      LEFT JOIN b2b_hub.acc_trans_c2 b 
        ON a.refno = b.`refno` 
        AND c.`line` = b.`line` 
    WHERE b.`refno` IS NULL 
      AND b.`line` IS NULL 
      AND c.bizdate BETWEEN DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 MONTH),'%Y-%m-01') AND LAST_DAY(DATE_ADD(NOW(),INTERVAL -1 MONTH))
      AND c.trans_type = 'INV-CS') a ;
    -- IF GOT AMENDMENT
    UPDATE backend.acc_trans_c2 a INNER JOIN b2b_hub.acc_trans_c2 b ON a.`refno` = b.`refno` AND a.line = b.line 
    SET b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW(), b.created_at = a.created_at 
    WHERE b.exported = '1' AND a.`created_at` <> b.`created_at`;
    UPDATE b2b_hub.`acc_trans_c2` a LEFT JOIN backend.`acc_trans_c2` b
    ON a.`refno`=b.`refno` AND a.`line` = b.`line`  SET operation = 'DELETE', exported = '0', exported_at = '1001-01-01 00:00:00', imported_at = NOW()
    WHERE b.`line` IS NULL;
    UPDATE b2b_hub.`acc_trans_c2` a LEFT JOIN backend.`acc_trans` b ON a.`refno`= b.`refno` SET operation = 'INSERT', exported = '0', exported_at = '1001-01-01 00:00:00', imported_at = NOW() WHERE a.operation = 'DELETE' AND b.`refno` IS NOT NULL;
    -- UPDATE b2b_hub.acc_trans_c2 SET operation = 'DELETE' WHERE refno IN (SELECT a.refno FROM b2b_hub.acc_trans_c2 a LEFT JOIN backend.acc_trans_c2 b ON a.refno=b.`refno` 
    -- AND a.`line` = b.`line` WHERE b.`line` IS NULL);
    -- IF GOT AMENDMENT
    -- UPDATE backend.acc_trans_c2 a INNER JOIN b2b_hub.acc_trans_c2 b ON a.refno = b.refno AND a.line = b.line SET b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW() 
    -- WHERE b.exported = '1';
    -- housekeeping
    CALL b2b_hub.DeleteTableRecords('b2b_hub.acc_trans',3,'date_trans','b2b_hub.acc_trans_c2','refno');
  END */$$
DELIMITER ;

/* Event structure for event `insert_companyprofile_to_hub` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `insert_companyprofile_to_hub` ON SCHEDULE EVERY 1 DAY STARTS '2023-06-24 03:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DELETE FROM b2b_hub.companyprofile WHERE exported = 1;
    INSERT INTO b2b_hub.companyprofile
    SELECT a.`CompanyName`,'1' imported,NOW() imported_at,'0' exported,'1001-01-01 00:00:00' exported_at FROM
    (SELECT a.`CompanyName` FROM backend.companyprofile a LEFT JOIN b2b_hub.companyprofile b ON a.`CompanyName`=b.`companyname` 
    WHERE b.`companyname` IS NULL)a;
  END */$$
DELIMITER ;

/* Event structure for event `insert_cp_set_branch_to_hub` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `insert_cp_set_branch_to_hub` ON SCHEDULE EVERY 1 DAY STARTS '2023-06-24 03:30:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    INSERT INTO b2b_hub.cp_set_branch
    SELECT a.`BRANCH_GUID`,a.`UPDATED_AT`,'1' imported,NOW() imported_at,'0' exported,'1001-01-01 00:00:00' exported_at FROM
    (SELECT a.`BRANCH_GUID`,a.`UPDATED_AT` FROM backend.cp_set_branch a LEFT JOIN b2b_hub.cp_set_branch b ON a.`BRANCH_GUID`=b.`branch_guid` 
    WHERE b.`branch_guid` IS NULL)a;
    -- IF GOT AMENDMENT
    UPDATE backend.cp_set_branch a INNER JOIN b2b_hub.cp_set_branch b ON a.`BRANCH_GUID` = b.`branch_guid` SET b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW(), b.updated_at = a.UPDATED_AT 
    WHERE b.exported = '1' AND a.`UPDATED_AT` <> b.`updated_at`;
  END */$$
DELIMITER ;

/* Event structure for event `insert_locationgroup_to_hub` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `insert_locationgroup_to_hub` ON SCHEDULE EVERY 1 DAY STARTS '2023-06-24 04:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    INSERT INTO b2b_hub.locationgroup
    SELECT a.`Code`,a.`Description`,a.`Remark`,a.`set_active`,'1' imported,NOW() imported_at,'0' exported,'1001-01-01 00:00:00' exported_at FROM
    (SELECT a.`Code`,a.`Description`,a.`Remark`,a.`set_active` FROM backend.locationgroup a LEFT JOIN b2b_hub.locationgroup b ON a.`Code`=b.`code` 
    WHERE b.`code` IS NULL)a;
    -- IF GOT AMENDMENT
    UPDATE backend.locationgroup a INNER JOIN b2b_hub.locationgroup b ON a.`Code` = b.`code` SET b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW(), b.set_active = a.set_active 
    WHERE b.exported = '1' AND a.`set_active` <> b.`set_active`;
  END */$$
DELIMITER ;

/* Event structure for event `insert_supcus_to_hub` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `insert_supcus_to_hub` ON SCHEDULE EVERY 15 MINUTE STARTS '2023-06-24 04:30:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    INSERT INTO b2b_hub.supcus
    SELECT a.`Type`,a.`Code`,a.`LastStamp`,'1' imported,NOW() imported_at,'0' exported,'1001-01-01 00:00:00' exported_at FROM
    (SELECT a.`Type`,a.`Code`,a.`LastStamp` FROM backend.supcus a LEFT JOIN b2b_hub.supcus b ON a.`Type`=b.`type` AND a.`Code`=b.`code`
    WHERE b.`type` IS NULL AND b.`code` IS NULL)a;
    -- IF GOT AMENDMENT
    UPDATE backend.supcus a INNER JOIN b2b_hub.supcus b ON a.`Type` = b.`type` AND a.`Code` = b.`code` SET b.exported = '0', b.exported_at = '1001-01-01 00:00:00', b.imported_at = NOW(), b.LastStamp = a.LastStamp 
    WHERE b.exported = '1' AND a.`LastStamp` <> b.`LastStamp`;
  END */$$
DELIMITER ;

/* Event structure for event `update_consignment_e_invoices_to_hub` */

DELIMITER $$

/*!50106 CREATE DEFINER=`panda_super`@`%` EVENT `update_consignment_e_invoices_to_hub` ON SCHEDULE EVERY 1 MINUTE STARTS '2023-07-01 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE backend.acc_trans a INNER JOIN b2b_hub.consignment_e_invoices b ON a.trans_guid = b.trans_guid 
    SET a.sup_doc_no = b.b2b_inv_no,a.sup_doc_date = b.b2b_inv_date,a.b2b_status = '2',b.exported = 1,b.exported_at = NOW() WHERE b.exported = '0';
  END */$$
DELIMITER ;

/* Procedure structure for procedure `DeleteTableRecords` */

DELIMITER $$

/*!50003 CREATE DEFINER=`panda_super`@`%` PROCEDURE `DeleteTableRecords`(
	IN MainTable VARCHAR(30),
	IN TotalMonth INT,
	IN DateField VARCHAR(30),
	IN ChildTable VARCHAR(30),
	IN RelationField VARCHAR(30)
)
BEGIN
	SET @MainTable = MainTable;
	SET @TotalMonth = TotalMonth;
	SET @DateField = DateField;
	SET @ChildTable = ChildTable;
	SET @RelationField = RelationField;
	
	SET @query = IF(@ChildTable = NULL OR @ChildTable = '',CONCAT('DELETE FROM ',@MainTable,' WHERE ',@DateField,' < Date_SUB(CURDATE(),INTERVAL ',@TotalMonth,' MONTH)'),
	CONCAT('DELETE a FROM ',@ChildTable,' a INNER JOIN ',@MainTable,' b ON b.',@RelationField,'=b.',@RelationField,
	' WHERE b.',@DateField,' < Date_SUB(CURDATE(),INTERVAL ',@TotalMonth,' MONTH)'));
	PREPARE stmt1 FROM @query;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;
	
	SET @query2 = IF(@ChildTable != NULL OR @ChildTable != '',CONCAT('DELETE FROM ',@MainTable,' WHERE ',@DateField,' < Date_SUB(CURDATE(),INTERVAL ',@TotalMonth,' MONTH)'),
	CONCAT('SELECT * FROM ',@MainTable,' LIMIT 1;'));
	PREPARE stmt2 FROM @query2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;
END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
