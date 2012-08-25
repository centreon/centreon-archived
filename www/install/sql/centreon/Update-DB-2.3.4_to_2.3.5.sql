
UPDATE `nagios_server` SET `monitoring_engine` = 'NAGIOS' WHERE monitoring_engine IS NULL;

UPDATE `topology` SET `topology_url_opt` = '&type=h' WHERE `topology_page` = 60106;
UPDATE `topology` SET `topology_url_opt` = '&type=s' WHERE `topology_page` = 60216;

UPDATE `informations` SET `value` = '2.3.5' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.4' LIMIT 1;