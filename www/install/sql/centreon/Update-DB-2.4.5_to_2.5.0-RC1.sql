ALTER TABLE `hostgroup` ADD COLUMN `hg_rrd_retention` INT(11) DEFAULT NULL AFTER `hg_map_icon_image`;
ALTER TABLE `ods_view_details` ADD INDEX `index_metric_mult` (`index_id`, `metric_id`); 
ALTER TABLE `cron_operation` ADD COLUMN `pid` INT(11) DEFAULT NULL AFTER `running`;
ALTER TABLE `traps` ADD COLUMN `traps_timeout` INT(11) DEFAULT NULL AFTER `traps_advanced_treatment`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_interval` INT(11) DEFAULT NULL AFTER `traps_timeout`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_interval_type` enum('0','1','2') DEFAULT '0' AFTER `traps_exec_interval`;
ALTER TABLE `traps` ADD COLUMN `traps_log` enum('0','1') DEFAULT '0' AFTER `traps_exec_interval_type`;
ALTER TABLE `traps` ADD COLUMN `traps_routing_mode` enum('0','1') DEFAULT '0' AFTER `traps_log`;
ALTER TABLE `traps` ADD COLUMN `traps_routing_value` varchar(255) DEFAULT NULL AFTER `traps_routing_mode`;
ALTER TABLE `traps` ADD COLUMN `traps_exec_method` enum('0','1') DEFAULT '0' AFTER `traps_routing_value`;
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
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60101,NULL,'./include/common/javascript/centreon/hostResolve.js',NULL);


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

-- Ticket #4623
ALTER TABLE `nagios_server` ADD COLUMN `snmp_trapd_path_conf` VARCHAR(255) DEFAULT NULL AFTER `init_script_snmptt`;
UPDATE `nagios_server` SET snmp_trapd_path_conf = (SELECT `value` FROM `options` WHERE `key` = 'snmp_trapd_path_conf');
DELETE FROM `options` WHERE `key` = 'snmp_trapd_path_conf';
DELETE FROM `topology` WHERE `topology_page` = 5010104 AND topology_name = 'SNMP';
INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES ('CentCore',NULL,50101,5010110,25,1,'./include/options/oreon/generalOpt/generalOpt.php','&o=centcore','0','0','1',NULL,NULL,NULL,'1');

-- Ticket #4222
ALTER TABLE `nagios_server` ADD COLUMN `engine_name` VARCHAR(255) DEFAULT NULL AFTER `snmp_trapd_path_conf`;
ALTER TABLE `nagios_server` ADD COLUMN `engine_version` VARCHAR(255) DEFAULT NULL AFTER `engine_name`;

INSERT INTO `options` (`key`, `value`) VALUES ('monitoring_dwt_duration_scale', 's');

-- Ticket #4666
CREATE TABLE `poller_command_relations` (
  `poller_id` int(11) NOT NULL,
  `command_id` int(11) NOT NULL,
  `command_order` tinyint (3) DEFAULT NULL,
  KEY `poller_id` (`poller_id`),
  KEY `command_id` (`command_id`),
  CONSTRAINT `poller_command_relations_fk_1` FOREIGN KEY (`poller_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poller_command_relations_fk_2` FOREIGN KEY (`command_id`) REFERENCES `command` (`command_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60901,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60901,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

UPDATE `topology` SET `topology_name` = 'Extensions' WHERE `topology_name` = 'Modules' AND `topology_page` = 507;

-- noty
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (202,NULL,'./include/common/javascript/jquery/plugins/noty/jquery.noty.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (202,NULL,'./include/common/javascript/jquery/plugins/noty/themes/default.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (202,NULL,'./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20215,NULL,'./include/common/javascript/jquery/plugins/noty/jquery.noty.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20215,NULL,'./include/common/javascript/jquery/plugins/noty/themes/default.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20215,NULL,'./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20202,NULL,'./include/common/javascript/jquery/plugins/noty/jquery.noty.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20202,NULL,'./include/common/javascript/jquery/plugins/noty/themes/default.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20202,NULL,'./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20201,NULL,'./include/common/javascript/jquery/plugins/noty/jquery.noty.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20201,NULL,'./include/common/javascript/jquery/plugins/noty/themes/default.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20201,NULL,'./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (201,NULL,'./include/common/javascript/jquery/plugins/noty/jquery.noty.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (201,NULL,'./include/common/javascript/jquery/plugins/noty/themes/default.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (201,NULL,'./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20105,NULL,'./include/common/javascript/jquery/plugins/noty/jquery.noty.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20105,NULL,'./include/common/javascript/jquery/plugins/noty/themes/default.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20105,NULL,'./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20103,NULL,'./include/common/javascript/jquery/plugins/noty/jquery.noty.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20103,NULL,'./include/common/javascript/jquery/plugins/noty/themes/default.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20103,NULL,'./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js',NULL);

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20102,NULL,'./include/common/javascript/jquery/plugins/noty/jquery.noty.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20102,NULL,'./include/common/javascript/jquery/plugins/noty/themes/default.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (20102,NULL,'./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js',NULL);
-- end of noty

-- Ticket #4611
ALTER TABLE `nagios_server` ADD COLUMN `description` VARCHAR(50) DEFAULT NULL;

-- Ticket #4201
DELETE FROM cb_list WHERE cb_field_id = '6'; 
INSERT INTO cb_list (cb_list_id, cb_field_id, default_value) VALUES ('5', '6', 'no');
DELETE FROM cb_list WHERE cb_field_id = '25'; 
INSERT INTO cb_list (cb_list_id, cb_field_id, default_value) VALUES ('5', '25', 'no');

INSERT INTO cb_list_values (cb_list_id, value_name, value_value) VALUES ('5', 'No', 'no');
INSERT INTO cb_list_values (cb_list_id, value_name, value_value) VALUES ('5', 'Yes', 'yes');
INSERT INTO cb_list_values (cb_list_id, value_name, value_value) VALUES ('5', 'Auto', 'auto');

INSERT INTO cb_list_values (cb_list_id, value_name, value_value) VALUES ('1', 'No', 'no');
INSERT INTO cb_list_values (cb_list_id, value_name, value_value) VALUES ('1', 'Yes', 'yes');

-- Ticket #4938

INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES (46, 'negociation', 'Enable negociation', 'Enable negociation option (use only for version of Centren Broker >= 2.5)', 'int', NULL);
INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`) VALUES (1, 46, 'yes');
INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES (3, 46, 1, 8), (10, 46, 1, 8);

UPDATE `informations` SET `value` = '2.5.0-RC1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.5' LIMIT 1;
