-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.3' LIMIT 1;

UPDATE nagios_server SET monitoring_engine = 'CENGINE' WHERE monitoring_engine = 'Centreon Engine';