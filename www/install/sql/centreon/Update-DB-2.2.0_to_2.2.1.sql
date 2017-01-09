ALTER TABLE `acl_topology` ADD `acl_comments` text DEFAULT NULL AFTER acl_topo_alias ;

UPDATE `informations` SET `value` = '2.2.1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.2.0' LIMIT 1;