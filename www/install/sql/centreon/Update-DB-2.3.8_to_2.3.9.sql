
ALTER TABLE host ADD INDEX(`host_register`);
ALTER TABLE host_service_relation ADD INDEX(`host_host_id`, `service_service_id`);

UPDATE cron_operation SET name = 'logAnalyser' WHERE name = 'logAnalyzer';

UPDATE `informations` SET `value` = '2.3.9' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.8' LIMIT 1;
