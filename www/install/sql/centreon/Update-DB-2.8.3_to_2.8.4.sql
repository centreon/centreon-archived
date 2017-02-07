-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.4' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.3' LIMIT 1;

ALTER TABLE nagios_server ADD COLUMN centreonbroker_logs_path VARCHAR(255);
ALTER TABLE cfg_centreonbroker ADD COLUMN daemon TINYINT(1);
