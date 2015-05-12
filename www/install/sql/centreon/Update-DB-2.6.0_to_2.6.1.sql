ALTER TABLE `cfg_nagios` ADD COLUMN `log_pid` enum('0','1') DEFAULT '1';
  
-- Change version of Centreon
UPDATE `informations` SET `value` = '2.6.1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.0' LIMIT 1;
