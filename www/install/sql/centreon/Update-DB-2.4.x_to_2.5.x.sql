ALTER TABLE `hostgroup` ADD COLUMN `hg_rrd_retention` INT(11) DEFAULT NULL AFTER `hg_map_icon_image`;
ALTER TABLE `ods_view_details` ADD INDEX `index_metric_mult` (`index_id`, `metric_id`); 
ALTER TABLE `cron_operation` ADD COLUMN `pid` INT(11) DEFAULT NULL AFTER `running`;
ALTER TABLE `traps` ADD COLUMN `traps_timeout` INT(11) DEFAULT NULL AFTER `traps_advanced_treatment`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_interval` INT(11) DEFAULT NULL AFTER `traps_timeout`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_interval_type` enum('0','1','2') DEFAULT '0' AFTER `traps_exec_interval`;
ALTER TABLE `traps` ADD COLUMN `traps_log` enum('0','1') DEFAULT '0' AFTER `traps_exec_interval_type`;
ALTER TABLE `traps` ADD COLUMN `traps_routing_mode` enum('0','1') DEFAULT '0' AFTER `traps_log`;
ALTER TABLE `traps` ADD COLUMN `traps_routing_value` varchar(255) DEFAULT NULL AFTER `traps_routing_mode`;

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,'a','./include/common/javascript/changetab.js','initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,'c','./include/common/javascript/changetab.js','initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,'w','./include/common/javascript/changetab.js','initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,NULL,'./include/common/javascript/centreon/doClone.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,NULL,'./include/common/javascript/centreon/serviceFilterByHost.js',NULL);

CREATE TABLE `traps_preexec` (
  `trap_id` int(11) DEFAULT NULL,
  `tpe_order` int(11) DEFAULT NULL,
  `tpe_string` varchar(512) DEFAULT NULL,
  KEY `trap_id` (`trap_id`),
  CONSTRAINT `traps_preexec_ibfk_1` FOREIGN KEY (`trap_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `topology` SET `readonly` = '0' WHERE `topology_parent` = '608' AND `topology_url` IS NOT NULL;

-- ticket #2329
ALTER TABLE  `traps` CHANGE  `traps_args`  `traps_args` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- Ticket #4201
INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`) VALUE (2, 'BBDO Protocol', 'bbdo');

-- Add possibility to lock a service template
ALTER TABLE  `service` ADD  `service_locked` BOOLEAN NOT NULL AFTER  `command_command_id_arg2`;

-- /!\ WARNING /!\
-- This file must be renamed and the query below must be updated once we know the exact source and target versions.
-- /!\ WARNING /!\
UPDATE `informations` SET `value` = '2.5.x' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.x' LIMIT 1;
