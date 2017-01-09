
UPDATE topology set topology_url = './include/monitoring/downtime/downtimeService.php' WHERE topology_page = '20218';
UPDATE topology set topology_url = './include/monitoring/downtime/downtimeHost.php' WHERE topology_page = '20106';

UPDATE topology set topology_url = './include/monitoring/comments/commentService.php' WHERE topology_page = '20219';
UPDATE topology set topology_url = './include/monitoring/comments/commentHost.php' WHERE topology_page = '20107';

UPDATE `informations` SET `value` = '2.2.0-RC1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.2.0-b1' LIMIT 1;