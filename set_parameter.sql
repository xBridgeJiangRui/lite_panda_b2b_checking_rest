/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

USE `rest_api`;


SET @sqlscript = IF((SELECT COUNT(*) FROM information_schema.tables
WHERE table_schema='rest_api' AND table_name='troubleshoot_set_scheduler')<> 1, "
CREATE TABLE rest_api.troubleshoot_set_scheduler  (
  `scheduler_guid` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `type` varbinary(50) NULL DEFAULT NULL,
  `next_run_datetime` datetime(0) NULL DEFAULT NULL,
  `active` smallint(6) NULL DEFAULT 1,
  PRIMARY KEY (`scheduler_guid`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;","SELECT 'Table exists' LIMIT 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;

SET @sqlscript = IF((SELECT COUNT(*) FROM rest_api.troubleshoot_set_scheduler WHERE type = 'rest_check_error')<>1, "
INSERT INTO rest_api.troubleshoot_set_scheduler (scheduler_guid,type,next_run_datetime,active) VALUES ('E79539F0CA4111E98B0DE4E7491C3E1E','rest_check_error',NOW(),1)", "select 'RECORD_EXIST' limit 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;

SET @sqlscript = IF((SELECT COUNT(*) FROM information_schema.tables
WHERE table_schema='rest_api' AND table_name='troubleshoot_set_setting')<> 1, "
CREATE TABLE rest_api.troubleshoot_set_setting  (
  `module_name` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `code` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `reason` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`module_name`, `code`) USING BTREE,
  INDEX `module_name`(`module_name`) USING BTREE,
  INDEX `reason`(`reason`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;","SELECT 'Table exists' LIMIT 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;

SET @sqlscript = IF((SELECT COUNT(*) FROM rest_api.troubleshoot_set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code ='document_interval')<>1, "
INSERT INTO rest_api.troubleshoot_set_setting (module_name,code,reason) VALUES ('lite_panda_b2b_checking_rest','document_interval',60)", "select 'RECORD_EXIST' limit 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;

SET @sqlscript = IF((SELECT COUNT(*) FROM rest_api.troubleshoot_set_setting WHERE module_name = 'lite_panda_b2b_checking_rest' AND code ='email_send_interval')<>1, "
INSERT INTO rest_api.troubleshoot_set_setting (module_name,code,reason) VALUES ('lite_panda_b2b_checking_rest','email_send_interval',1440)", "select 'RECORD_EXIST' limit 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;

/* Create table in target */
SET @sqlscript = IF((SELECT COUNT(*) FROM information_schema.tables
WHERE table_schema='rest_api' AND table_name='troubleshoot_programmer_email_list')<> 1, "
CREATE TABLE rest_api.troubleshoot_programmer_email_list  (
  `seq` smallint(6) NULL DEFAULT NULL,
  `programmer_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `programmer_email` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `isactive` smallint(6) NULL DEFAULT NULL,
  PRIMARY KEY (`programmer_email`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;","SELECT 'Table exists' LIMIT 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;

SET @sqlscript = IF((SELECT COUNT(*) FROM rest_api.troubleshoot_programmer_email_list WHERE programmer_email = 'danielweng57@gmail.com' AND programmer_name ='daniel')<>1, "
INSERT INTO rest_api.troubleshoot_programmer_email_list (programmer_name,programmer_email,isactive,seq) VALUES ('daniel','danielweng57@gmail.com',1,1)", "select 'RECORD_EXIST' limit 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;

SET @sqlscript = IF((SELECT COUNT(*) FROM information_schema.tables
WHERE table_schema='rest_api' AND table_name='troubleshoot_email_setup')<> 1, "
CREATE TABLE rest_api.troubleshoot_email_setup  (
  `guid` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `smtp_server` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `smtp_port` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `smtp_security` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `password` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `sender_name` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `sender_email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `recipient_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `recipient_email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `subject` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `active` smallint(6) NULL DEFAULT 1,
  `url` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`guid`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;","SELECT 'Table exists' LIMIT 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;

SET @sqlscript = IF((SELECT COUNT(*) FROM rest_api.troubleshoot_email_setup WHERE sender_email = 'rexbridge.b2b@gmail.com' AND username ='xbridge.b2b@gmail.com')<>1, "
INSERT INTO rest_api.troubleshoot_email_setup (guid,smtp_server,smtp_port,smtp_security,username,password,sender_name,sender_email,recipient_name,recipient_email,subject,active,url) VALUES ('0B5AC2835FA111E7B048198A696EEB51', 'smtp.gmail.com', '587', 'TLS', 'xbridge.b2b@gmail.com', '80998211', 'B2B-noreply', 'rexbridge.b2b@gmail.com', 'Hugh', 'hughlim91@gmail.com', '1234', 1, 'localhost/lite_panda_b2b');", "select 'RECORD_EXIST' limit 1");
PREPARE query_result FROM @sqlscript;
EXECUTE query_result;