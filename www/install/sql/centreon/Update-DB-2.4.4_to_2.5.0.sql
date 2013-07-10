ALTER TABLE `hostgroup` ADD COLUMN `hg_rrd_retention` INT(11) DEFAULT NULL AFTER `hg_map_icon_image`;
ALTER TABLE `ods_view_details` ADD INDEX `index_metric_mult` (`index_id`, `metric_id`); 
ALTER TABLE `cron_operation` ADD COLUMN `pid` INT(11) DEFAULT NULL AFTER `running`;
ALTER TABLE `traps` ADD COLUMN `traps_timeout` INT(11) DEFAULT NULL AFTER `traps_advanced_treatment`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_interval` INT(11) DEFAULT NULL AFTER `traps_timeout`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_interval_type` enum('0','1','2') DEFAULT '0' AFTER `traps_exec_interval`;
ALTER TABLE `traps` ADD COLUMN `traps_log` enum('0','1') DEFAULT '0' AFTER `traps_exec_interval_type`;
ALTER TABLE `traps` ADD COLUMN `traps_routing_mode` enum('0','1') DEFAULT '0' AFTER `traps_log`;
ALTER TABLE `traps` ADD COLUMN `traps_routing_value` varchar(255) DEFAULT NULL AFTER `traps_routing_mode`;
ALTER TABLE `traps` ADD COLUMN `traps_advanced_treatment_default` enum('0','1') DEFAULT '0' AFTER `traps_advanced_treatment`;

ALTER TABLE `cfg_nagios` ADD COLUMN `use_setpgid` enum('0','1','2') DEFAULT NULL AFTER `enable_environment_macros`;
ALTER TABLE `cfg_nagios` ADD COLUMN `use_check_result_path` enum('0','1') DEFAULT '0' AFTER `check_result_path`;
UPDATE `cfg_nagios` SET `use_setpgid` = '2';


INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,'a','./include/common/javascript/changetab.js','initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,'c','./include/common/javascript/changetab.js','initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,'w','./include/common/javascript/changetab.js','initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,NULL,'./include/common/javascript/centreon/doClone.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (61701,NULL,'./include/common/javascript/centreon/serviceFilterByHost.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (5010105,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (5010105,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60101,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60101,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60103,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60103,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60201,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60201,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60202,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60202,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60206,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60206,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60703,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60703,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

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

-- ticket #4536
UPDATE `service` SET `service_alias` = 'Swap' WHERE `service_description` = 'SNMP-Linux-Swap' AND `service_alias` = 'Memory';

-- Ticket #4201
INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`) VALUE (2, 'BBDO Protocol', 'bbdo');

-- Add Centreon Broker configuration
INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES 
(42, 'store_in_data_bin', 'Store in performance data in data_bin', 'It should be enabled to control whether or not Centreon Broker should insert performance data in the data_bin table.', 'radio', NULL),
(43, 'insert_in_index_data', 'Insert in index data', 'Whether or not Broker should create entries in the index_data table. This process should be done by Centreon and this option should only be enabled by advanced users knowing what they\'re doing', 'text', 'T=options:C=value:CK=key:K=index_data'),
(44, 'write_metrics', 'Write metrics', 'This can be used to disable graph update and therefore reduce I/O', 'radio', NULL),
(45, 'write_status', 'Write status', 'This can be used to disable graph update and therefore reduce I/O', 'radio', NULL);

INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`) VALUES
(1, 42, 'yes'),
(1, 44, 'yes'),
(1, 45, 'yes');

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(13, 42, 1, 5),
(13, 43, 1, 6),
(13, 44, 1, 5),
(13, 45, 1, 6);

-- Add option to not inherit host contacts and contactgroups Ticket #4498
ALTER TABLE `service` ADD COLUMN `service_inherit_contacts_from_host` enum('0','1') DEFAULT '1' AFTER `service_notifications_enabled`;

-- Ticket #1845
ALTER TABLE `service` ADD COLUMN `cg_additive_inheritance` boolean DEFAULT 0 AFTER `service_notifications_enabled`;
ALTER TABLE `service` ADD COLUMN `contact_additive_inheritance` boolean DEFAULT 0 AFTER `service_notifications_enabled`;
ALTER TABLE `host` ADD COLUMN `cg_additive_inheritance` boolean DEFAULT 0 AFTER `host_notifications_enabled`;
ALTER TABLE `host` ADD COLUMN `contact_additive_inheritance` boolean DEFAULT 0 AFTER `host_notifications_enabled`;

-- Ticket #3988
INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`,`topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) 
VALUES ('Contact Templates','./img/icones/16x16/user1_information.png',603,60306,13,1,'./include/configuration/configObject/contact_template_model/contact_template.php','0','0','1',NULL,NULL,NULL,'0');
INSERT INTO `topology_JS` (`id_page`, `PathName_js`, `Init`) VALUES (60306, './include/common/javascript/changetab.js', 'initChangeTab');

-- Ticket #4539
ALTER TABLE `hostcategories` ADD COLUMN `level` TINYINT(5) DEFAULT NULL AFTER `hc_alias`;
ALTER TABLE `hostcategories` ADD COLUMN `icon_id` INT(11) DEFAULT NULL AFTER `level`;
ALTER TABLE `service_categories` ADD COLUMN `level` TINYINT(5) DEFAULT NULL AFTER `sc_description`;
ALTER TABLE `service_categories` ADD COLUMN `icon_id` INT(11) DEFAULT NULL AFTER `sc_description`;
DELETE FROM `topology` WHERE `topology_page` = 60228 AND `topology_name` = 'Criticality';
DELETE FROM `topology` WHERE `topology_page` = 60107 AND `topology_name` = 'Criticality';
ALTER TABLE `traps` ADD COLUMN `severity_id` int(11) DEFAULT NULL AFTER `traps_status`;
ALTER TABLE `traps` ADD CONSTRAINT `traps_ibfk_2` FOREIGN KEY (`severity_id`) REFERENCES `service_categories` (`sc_id`) ON DELETE CASCADE;
ALTER TABLE `traps_matching_properties` ADD COLUMN `severity_id` int(11) DEFAULT NULL AFTER `tmo_status`;
ALTER TABLE `traps_matching_properties` ADD CONSTRAINT `traps_matching_properties_ibfk_2` FOREIGN KEY (`severity_id`) REFERENCES `service_categories` (`sc_id`) ON DELETE CASCADE;

UPDATE `informations` SET `value` = '2.5.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.4' LIMIT 1;

-- Ticket #3765
ALTER TABLE `cfg_centreonbroker` ADD COLUMN `config_write_timestamp` enum('0','1') DEFAULT '1' AFTER `config_filename`;
