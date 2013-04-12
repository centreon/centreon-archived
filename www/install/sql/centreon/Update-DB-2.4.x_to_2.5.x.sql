ALTER TABLE `hostgroup` ADD COLUMN `hg_rrd_retention` INT(11) DEFAULT NULL AFTER `hg_map_icon_image`;
ALTER TABLE `ods_view_details` ADD INDEX `index_metric_mult` (`index_id`, `metric_id`); 
ALTER TABLE `cron_operation` ADD COLUMN `pid` INT(11) DEFAULT NULL AFTER `running`;
ALTER TABLE `traps` ADD COLUMN `traps_timeout` INT(11) DEFAULT NULL AFTER `traps_advanced_treatment`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_interval` INT(11) DEFAULT NULL AFTER `traps_timeout`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_interval_type` enum('0','1','2') DEFAULT '0' AFTER `traps_timeout`;

UPDATE `topology` SET `readonly` = '0' WHERE `topology_parent` = '608' AND `topology_url` IS NOT NULL;

-- /!\ WARNING /!\
-- This file must be renamed and the query below must be updated once we know the exact source and target versions.
-- /!\ WARNING /!\
UPDATE `informations` SET `value` = '2.5.x' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.x' LIMIT 1;
