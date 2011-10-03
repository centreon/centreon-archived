
DELETE FROM topology WHERE topology_url LIKE './include/monitoring/mysql_log/viewLog.php' AND topology_name LIKE 'All Logs' AND topology_show = '0';
DELETE FROM topology WHERE topology_url LIKE './include/monitoring/mysql_log/viewErrors.php' AND topology_name LIKE 'Warnings' AND topology_show = '0';
DELETE FROM topology WHERE topology_page = '20313' OR topology_parent = '20313';
DELETE FROM topology WHERE topology_page = '20312' OR topology_parent = '20312';

INSERT INTO options (`key`, `value`) VALUES ('centstorage', '1');

ALTER TABLE `acl_topology` ADD `acl_comments` text DEFAULT NULL AFTER acl_topo_alias ;

--
-- Update order for Perfdata generator for Centreon Broker configuration
--
UPDATE `centreon`.`cb_type_field_relation` SET `order_display` = '6' WHERE `cb_type_field_relation`.`cb_type_id` =14 AND `cb_type_field_relation`.`cb_field_id` =8;
UPDATE `centreon`.`cb_type_field_relation` SET `order_display` = '7' WHERE `cb_type_field_relation`.`cb_type_id` =14 AND `cb_type_field_relation`.`cb_field_id` =9;
UPDATE `centreon`.`cb_type_field_relation` SET `order_display` = '8' WHERE `cb_type_field_relation`.`cb_type_id` =14 AND `cb_type_field_relation`.`cb_field_id` =10;

--
-- Update order for Broker SQL for Centreon Broker configuration
--
UPDATE `centreon`.`cb_type_field_relation` SET `order_display` = '4' WHERE `cb_type_field_relation`.`cb_type_id` =16 AND `cb_type_field_relation`.`cb_field_id` =8;
UPDATE `centreon`.`cb_type_field_relation` SET `order_display` = '5' WHERE `cb_type_field_relation`.`cb_type_id` =16 AND `cb_type_field_relation`.`cb_field_id` =9;
UPDATE `centreon`.`cb_type_field_relation` SET `order_display` = '6' WHERE `cb_type_field_relation`.`cb_type_id` =16 AND `cb_type_field_relation`.`cb_field_id` =10;


UPDATE `informations` SET `value` = '2.3.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-RC3' LIMIT 1;
