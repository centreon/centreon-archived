-- 26 07 2006
ALTER TABLE `general_opt` ADD `snmp_trapd_used` ENUM( '0', '1' ) NULL AFTER `snmp_version` ;
UPDATE `topology` SET `topology_icone` = NULL , `topology_url` = './include/monitoring/log/viewAlertLog.php', `topology_url_opt` = NULL WHERE `topology_page` = '203' LIMIT 1 ;
