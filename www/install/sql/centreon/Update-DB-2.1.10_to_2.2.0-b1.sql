ALTER TABLE `topology_JS` ADD CONSTRAINT `topology_page_idfk_1` FOREIGN KEY (`id_page`) REFERENCES `topology` (`topology_page`) ON DELETE CASCADE;

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 603, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 603, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 603, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 603, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60301, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60301, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60301, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60301, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60304, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60304, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60304, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 50203, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 50203, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 50203, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
ALTER TABLE `host` ADD `host_retry_check_interval` INT NULL AFTER `host_check_interval` ;

ALTER TABLE `contact` ADD `contact_address1` VARCHAR( 200 ) NULL AFTER `contact_pager` ,
ADD `contact_address2` VARCHAR( 250 ) NULL AFTER `contact_address1` ,
ADD `contact_address3` VARCHAR( 200 ) NULL AFTER `contact_address2` ,
ADD `contact_address4` VARCHAR( 200 ) NULL AFTER `contact_address3` ,
ADD `contact_address5` VARCHAR( 200 ) NULL AFTER `contact_address4` ,
ADD `contact_address6` VARCHAR( 200 ) NULL AFTER `contact_address5` ;

-- Graphs
ALTER TABLE `giv_components_template` ADD `ds_stack` enum('0','1') default NULL;

ALTER TABLE `acl_resources` ADD `all_hosts` enum('0','1') default NULL AFTER acl_res_alias;
ALTER TABLE `acl_resources` ADD `all_hostgroups` enum('0','1') default NULL AFTER all_hosts;
ALTER TABLE `acl_resources` ADD `all_servicegroups` enum('0','1') default NULL AFTER all_hostgroups;


INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Warning', NULL, 20215, 2021501, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_warning', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Critical', NULL, 20215, 2021502, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_critical', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Unknown', NULL, 20215, 2021503, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_unknown', NULL, NULL, '1', NULL, NULL, NULL);

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Warning', NULL, 20202, 2020201, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_warning', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Critical', NULL, 20202, 2020202, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_critical', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Unknown', NULL, 20202, 2020203, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_unknown', NULL, NULL, '1', NULL, NULL, NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2020201, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2020202, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2020203, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2021501, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2021502, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2021503, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');


CREATE TABLE IF NOT EXISTS `acl_group_contactgroups_relations` (
  `agcgr_id` int(11) NOT NULL auto_increment,
  `cg_cg_id` int(11) default NULL,
  `acl_group_id` int(11) default NULL,
  PRIMARY KEY  (`agcgr_id`),
  KEY `cg_cg_id` (`cg_cg_id`),
  KEY `acl_group_id` (`acl_group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `acl_group_contactgroups_relations` ADD FOREIGN KEY ( `cg_cg_id` ) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE ;
ALTER TABLE `acl_group_contactgroups_relations` ADD FOREIGN KEY ( `acl_group_id` ) REFERENCES `acl_groups` (`acl_group_id`) ON DELETE CASCADE ;

CREATE TABLE IF NOT EXISTS `traps_matching_properties` (
  `tmo_id` int(11) NOT NULL AUTO_INCREMENT,
  `trap_id` int(11) DEFAULT NULL,
  `tmo_order` int(11) DEFAULT NULL,
  `tmo_regexp` varchar(255) DEFAULT NULL,
  `tmo_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`tmo_id`),
  KEY `trap_id` (`trap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 
ALTER TABLE `traps_matching_properties` ADD INDEX (`trap_id`);
ALTER TABLE `traps_matching_properties` ADD FOREIGN KEY (`trap_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE ;  


CREATE TABLE IF NOT EXISTS `timeperiod_include_relations` (
  `include_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `timeperiod_id` INT( 11 ) NOT NULL ,
  `timeperiod_include_id` INT( 11 ) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `timeperiod_exclude_relations` (
  `include_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `timeperiod_id` INT( 11 ) NOT NULL ,
  `timeperiod_exclude_id` INT( 11 ) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `timeperiod_exceptions` (
  `exception_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `timeperiod_id` INT(11) NOT NULL ,
  `days` VARCHAR(255) NOT NULL ,
  `timerange` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `timeperiod_exceptions`
  ADD CONSTRAINT `timeperiod_exceptions_relation_ibfk_1` FOREIGN KEY (`timeperiod_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE CASCADE;

ALTER TABLE `cfg_nagios` ADD `passive_host_checks_are_soft` INT(11) DEFAULT NULL ;
ALTER TABLE `cfg_nagios` ADD `check_for_orphaned_hosts` enum('0','1','2') default NULL ;
ALTER TABLE `cfg_nagios` ADD `external_command_buffer_slots` INT(11) DEFAULT NULL ;
ALTER TABLE `cfg_nagios` CHANGE service_reaper_frequency check_result_reaper_frequency INT(11);
ALTER TABLE `cfg_nagios` CHANGE `translate_passive_host_checks` `translate_passive_host_checks` INT(11) DEFAULT NULL ;
ALTER TABLE `cfg_nagios` ADD use_aggressive_host_checking enum('0','1','2') default 0;

ALTER TABLE `cfg_nagios` DROP COLUMN aggregate_status_updates;
ALTER TABLE `cfg_nagios` DROP COLUMN use_agressive_host_checking;

CREATE TABLE `command_arg_description` (
	`cmd_id` INT( 11 ) NOT NULL ,
	`macro_name` VARCHAR( 255 ) NOT NULL ,
	`macro_description` VARCHAR( 255 ) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE  `command_arg_description` ADD CONSTRAINT  `command_arg_description_ibfk_1` FOREIGN KEY (`cmd_id`) REFERENCES  `command` (  `command_id` ) ON DELETE CASCADE;

INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG1", "share");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG2", "user");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG3", "password");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG4", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG5", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (4, "ARG1", "status");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (4, "ARG2", "output");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (5, "ARG1", "SNMP version");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (5, "ARG2", "community");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (5, "ARG3", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (5, "ARG4", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (6, "ARG1", "count");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (6, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (6, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (7, "ARG1", "SNMP version");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (7, "ARG2", "community");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (7, "ARG3", "process name");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG1", "disk number");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG4", "community");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG5", "SNMP version");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG3", "path, partition");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG1", "interface");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG4", "community");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG5", "SNMP version");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG6", "Max bandwidth");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (14, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (14, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (15, "ARG1", "path");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (15, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (15, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (16, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (16, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (17, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (17, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (17, "ARG3", "process owner");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (18, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (18, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (21, "ARG1", "drive letter");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (21, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (21, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (23, "ARG1", "port");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (23, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (23, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (27, "ARG1", "hostname");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (29, "ARG1", "interface");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (30, "ARG1", "query address");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (31, "ARG1", "OID");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (31, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (31, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (33, "ARG1", "port");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG1", "variable");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG2", "params");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG3", "password");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG4", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG5", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (59, "ARG1", "port");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (59, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (59, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (62, "ARG1", "interface");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (62, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (62, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (62, "ARG4", "SNMP version");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (76, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (76, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (78, "ARG1", "drive letter");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (78, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (78, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (94, "ARG1", "community");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (94, "ARG2", "SNMP version");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (94, "ARG3", "OID");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (94, "ARG4", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (94, "ARG5", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (95, "ARG1", "process name");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (95, "ARG2", "memory thresholds");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (96, "ARG1", "community");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (96, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (96, "ARG3", "warning");



DELETE FROM topology WHERE topology_page = '10202';
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_icone`) VALUES (NULL, "System Information", '505', '50501', '10', '1','./include/options/sysInfos/index.php', './img/icones/16x16/about.gif');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Process Control', './img/icones/16x16/calculator.gif', '505', '50502', '20', '1', './include/Administration/corePerformance/processInfo.php', NULL, NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 50502, NULL, './include/common/javascript/changetab.js', 'initChangeTab');
UPDATE `topology` SET `topology_url` = NULL WHERE  `topology_parent` = '5' AND  `topology_page` = '505';

UPDATE      `topology` SET `topology_name` = 'Process Control', `topology_parent` = '505', `topology_page` = '50502', `topology_order` = '20', `topology_url` = './include/Administration/corePerformance/processInfo.php'  WHERE `topology_parent` = '102' AND  `topology_page` = '10202';
UPDATE      `topology` SET `topology_url` = NULL WHERE  `topology_parent` = '5' AND  `topology_page` = '505';
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 50502, NULL, './include/common/javascript/changetab.js', 'initChangeTab');

ALTER TABLE `command` ADD `command_comment` TEXT NULL ;

INSERT INTO `options` (`key`, `value`) VALUES ('monitoring_engine', 'NAGIOS');

ALTER TABLE `modules_informations` ADD `svc_tools` ENUM( '0', '1' ) DEFAULT '0', ADD `host_tools` ENUM( '0', '1' ) DEFAULT '0', ADD INDEX ( svc_tools, host_tools );

CREATE TABLE IF NOT EXISTS `cfg_nagios_broker_module` (
  `bk_mod_id` int(11) NOT NULL AUTO_INCREMENT,
  `cfg_nagios_id` int(11) DEFAULT NULL,
  `broker_module` varchar(255) DEFAULT NULL,
PRIMARY KEY (`bk_mod_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `hostcategories` (
  `hc_id` int(11) NOT NULL auto_increment,
  `hc_name` varchar(200) default NULL,
  `hc_alias` varchar(200) default NULL,
  `hc_comment` text,
  `hc_activate` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`hc_id`),
  KEY `name_index` (`hc_name`),
  KEY `alias_index` (`hc_alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `hostcategories_relation` (
  `hcr_id` int(11) NOT NULL auto_increment,
  `hostcategories_hc_id` int(11) default NULL,
  `host_host_id` int(11) default NULL,
  PRIMARY KEY  (`hcr_id`),
  KEY `hostcategories_index` (`hostcategories_hc_id`),
  KEY `host_index` (`host_host_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `hostcategories_relation` ADD FOREIGN KEY ( `hostcategories_hc_id` ) REFERENCES `hostcategories` (`hc_id`) ON DELETE CASCADE ;
ALTER TABLE `hostcategories_relation` ADD FOREIGN KEY ( `host_host_id` ) REFERENCES `host` (`host_id`) ON DELETE CASCADE ;

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Categories', './img/icones/16x16/cube_green.gif', 601, 60104, 40, 1, './include/configuration/configObject/host_categories/hostCategories.php', NULL, '0', '0', '1', NULL, NULL, NULL);

CREATE TABLE IF NOT EXISTS `acl_resources_hc_relations` (
  `arhcr_id` int(11) NOT NULL auto_increment,
  `hc_id` int(11) default NULL,
  `acl_res_id` int(11) default NULL,
  PRIMARY KEY  (`arhcr_id`),
  KEY `hc_id` (`hc_id`),
  KEY `acl_res_id` (`acl_res_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `acl_resources_hc_relations`
  ADD CONSTRAINT `acl_resources_hc_relations_ibfk_1` FOREIGN KEY (`hc_id`) REFERENCES `hostcategories` (`hc_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acl_resources_hc_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE;

ALTER TABLE `cron_operation` CHANGE `time_launch` `time_launch` INT(11) DEFAULT NULL ;
ALTER TABLE `cron_operation` ADD `running` enum('0','1') AFTER `module` ;
ALTER TABLE `cron_operation` ADD `last_execution_time` INT NULL AFTER `running` ;

ALTER TABLE traps_matching_properties ADD tmo_string VARCHAR(255) AFTER tmo_regexp;

DELETE FROM `topology` WHERE `topology_parent` = '203' AND `topology_page` = '20305';
DELETE FROM `topology` WHERE `topology_parent` = '20305' AND `topology_page` = '2030501';
DELETE FROM `topology` WHERE `topology_parent` = '20305' AND `topology_page` = '2030502';
DELETE FROM `topology` WHERE `topology_parent` = '203' AND `topology_page` = '20306';
DELETE FROM `topology` WHERE `topology_parent` = '20306' AND `topology_page` = '2030601';
DELETE FROM `topology` WHERE `topology_parent` = '20306' AND `topology_page` = '2030602';

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Downtime', './img/icones/16x16/warning.gif', 202, 20218, 60, 33, './include/monitoring/downtime/downtime.php', '&o=vs', '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Comments', './img/icones/16x16/messages.gif', 202, 20219, 60, 33, './include/monitoring/comments/comments.php', '&o=vs', '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Nagios', NULL, 201, NULL, NULL, 2, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Downtime', './img/icones/16x16/warning.gif', 201, 20106, 5, 2, './include/monitoring/downtime/downtime.php', '&o=vh', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Comments', './img/icones/16x16/messages.gif', 201, 20107, 5, 2, './include/monitoring/comments/comments.php', '&o=vh', NULL, NULL, '1', NULL, NULL, NULL);


CREATE TABLE IF NOT EXISTS `acl_resources_poller_relations` (
  `arpr_id` int(11) NOT NULL auto_increment,
  `poller_id` int(11) default NULL,
  `acl_res_id` int(11) default NULL,
  PRIMARY KEY  (`arpr_id`),
  KEY `poller_id` (`poller_id`),
  KEY `acl_res_id` (`acl_res_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE `acl_resources_poller_relations`
  ADD CONSTRAINT `acl_resources_poller_relations_ibfk_1` FOREIGN KEY (`poller_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acl_resources_poller_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE;
  
ALTER TABLE `acl_topology_relations` ADD COLUMN `access_right` TINYINT NOT NULL DEFAULT 1;
ALTER TABLE `topology` ADD COLUMN `readonly` ENUM('0', '1') NOT NULL DEFAULT '1';
UPDATE `topology` SET `readonly` = '0' WHERE `topology_page` IN (60101, 60102, 60103, 60201, 60202, 60203, 60206, 60209, 60207, 60205, 60204, 602080, 60301, 60302, 60304, 60305, 60708, 60707, 60703, 60401, 60402, 60403, 60404, 60405, 60406, 60407, 60408, 60409, 60410, 60411);

UPDATE `cfg_nagios` SET `downtime_file` = NULL, `comment_file` = NULL;

ALTER TABLE `nagios_server` ADD COLUMN `monitoring_engine` VARCHAR(20) NULL AFTER `init_script`;

UPDATE `topology` SET `topology_url` = './include/Administration/corePerformance/nagiosStats.php' WHERE topology_page = '10201' AND topology_parent = '102' AND topology_name = 'Graphs'; 
UPDATE `topology` SET `topology_url` = './include/Administration/corePerformance/performanceInfo.php' WHERE topology_page = '10203' AND topology_name = 'Performance Info';

UPDATE `informations` SET `value` = '2.2.0-b1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1.10' LIMIT 1;