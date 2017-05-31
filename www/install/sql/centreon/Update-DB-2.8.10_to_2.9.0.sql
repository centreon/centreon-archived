-- Change version of Centreon
UPDATE `informations` SET `value` = '2.9.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.10' LIMIT 1;

DELETE FROM topology_JS WHERE PathName_js LIKE '%aculous%';
ALTER TABLE `cfg_nagios` DROP COLUMN `log_initial_states`;