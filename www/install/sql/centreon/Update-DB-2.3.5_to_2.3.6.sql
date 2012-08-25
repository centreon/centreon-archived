UPDATE `nagios_server` SET `monitoring_engine` = 'NAGIOS' WHERE monitoring_engine IS NULL;

UPDATE `informations` SET `value` = '2.3.6' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.5' LIMIT 1;