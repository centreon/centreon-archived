
ALTER TABLE `extended_host_information` ADD UNIQUE (`host_host_id`);
UPDATE `topology` set `topology_show` = '0' WHERE `topology_page` = '50105' LIMIT 1;

ALTER TABLE `topology_JS` ADD INDEX ( `id_page` , `o` );
ALTER TABLE `acl_topology` ADD INDEX ( `acl_topo_id` , `acl_topo_activate` ); 

UPDATE `informations` SET `value` = '2.1-RC7' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1-RC6' LIMIT 1;

