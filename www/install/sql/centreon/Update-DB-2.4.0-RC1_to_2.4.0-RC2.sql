UPDATE `topology` SET `topology_name` = 'Monitoring Engines' WHERE `topology_parent` = 607 AND `topology_group` = 33 AND `topology_page` IS NULL;
UPDATE `topology` SET `topology_name` = 'Monitoring Engines' WHERE `topology_parent` = 6 AND `topology_page` = 607;


UPDATE `informations` SET `value` = '2.4.0-RC2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC1' LIMIT 1;