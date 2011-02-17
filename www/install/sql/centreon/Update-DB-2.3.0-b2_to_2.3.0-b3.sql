
UPDATE topology SET topology_name = 'Monitoring' WHERE topology_id = '5010102' AND topology_name = 'Nagios';

UPDATE `informations` SET `value` = '2.3.0-b3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-b2' LIMIT 1;
 