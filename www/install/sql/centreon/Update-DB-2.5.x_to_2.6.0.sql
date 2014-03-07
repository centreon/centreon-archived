
ALTER TABLE `traps` ADD COLUMN `traps_downtime` enum('0','1','2') DEFAULT '0' AFTER `traps_exec_method`;
ALTER TABLE `traps` ADD COLUMN `traps_output_transform` varchar(255) DEFAULT NULL AFTER `traps_downtime`;
ALTER TABLE `traps` ADD COLUMN `traps_routing_filter_services` varchar(255) DEFAULT NULL AFTER `traps_routing_value`;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.6.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.5.x' LIMIT 1;
