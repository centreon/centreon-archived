INSERT INTO `options` (`key`, `value`) SELECT 'nagios_path', nagios_path FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'nagios_path_bin', nagios_path_bin FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'nagios_init_script', nagios_init_script FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'nagios_path_img', nagios_path_img FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'nagios_path_plugins', nagios_path_plugins FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'nagios_version', nagios_version FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'snmp_community', snmp_community FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'snmp_version', snmp_version FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'snmpttconvertmib_path_bin', snmpttconvertmib_path_bin FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'perl_library_path', perl_library_path FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'snmp_trapd_path_conf', snmp_trapd_path_conf FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'mailer_path_bin', mailer_path_bin FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'rrdtool_path_bin', rrdtool_path_bin FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'rrdtool_version', rrdtool_version FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'oreon_path', oreon_path FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'oreon_web_path', oreon_web_path FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'oreon_rrdbase_path', oreon_rrdbase_path FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'oreon_refresh', oreon_refresh FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_up', color_up FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_down', color_down FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_unreachable', color_unreachable FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_ok', color_ok FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_warning', color_warning FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_critical', color_critical FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_pending', color_pending FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_unknown', color_unknown FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'color_undetermined', color_undetermined FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'session_expire', session_expire FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'perfparse_installed', perfparse_installed FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'graph_preferencies', graph_preferencies FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'maxViewMonitoring', maxViewMonitoring FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'maxViewConfiguration', maxViewConfiguration FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'AjaxTimeReloadMonitoring', AjaxTimeReloadMonitoring FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'AjaxTimeReloadStatistic', AjaxTimeReloadStatistic FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'AjaxFirstTimeReloadMonitoring', AjaxFirstTimeReloadMonitoring FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'AjaxFirstTimeReloadStatistic', AjaxFirstTimeReloadStatistic FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'template', template FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'problem_sort_type', problem_sort_type FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'problem_sort_order', problem_sort_order FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_host', ldap_host FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_port', ldap_port FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_base_dn', ldap_base_dn FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_login_attrib', ldap_login_attrib FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_ssl', ldap_ssl FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_search_user', ldap_search_user FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_search_user_pwd', ldap_search_user_pwd FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_search_filter', ldap_search_filter FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_search_timeout', ldap_search_timeout FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_search_limit', ldap_search_limit FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ldap_auth_enable', ldap_auth_enable FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'debug_path', debug_path FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'debug_auth', debug_auth FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'debug_nagios_import', debug_nagios_import FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'debug_rrdtool', debug_rrdtool FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'debug_ldap_import', debug_ldap_import FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'debug_inventory', debug_inventory FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'gmt', gmt FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'patch_type_stable', patch_type_stable FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'patch_type_RC', patch_type_RC FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'patch_type_patch', patch_type_patch FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'patch_type_secu', patch_type_secu FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'patch_type_beta', patch_type_beta FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'patch_url_service', patch_url_service FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'patch_url_download', patch_url_download FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'patch_path_download', patch_path_download FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'ndo_activate', ndo_activate FROM `general_opt`;
INSERT INTO `options` (`key`, `value`) SELECT 'snmptt_unknowntrap_log_file', snmptt_unknowntrap_log_file FROM `general_opt`;


DELETE FROM `topology` WHERE topology_page = '2020201';
DELETE FROM `topology` WHERE topology_page = '2020202';
DELETE FROM `topology` WHERE topology_page = '2020203';
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Warning', NULL, 20201, 2020102, 20, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_warning', '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Critical', NULL, 20201, 2020103, 30, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_critical', '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Unknown', NULL, 20201, 2020104, 40, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unknown', '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2020102, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2020103, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2020104, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 10201, NULL, './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 10202, NULL, './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 10203, NULL, './include/common/javascript/changetab.js', 'initChangeTab');

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Hosts', './img/icones/16x16/document_gear.gif', '20306', '2030601', '10', '1', './include/monitoring/comments/comments.php', '&o=vh', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Services', './img/icones/16x16/document_gear.gif', '20306', '2030602', '10', '1', './include/monitoring/comments/comments.php', '&o=vs', NULL, NULL, '1', NULL, NULL, NULL);

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Hosts', './img/icones/16x16/document_gear.gif', '20305', '2030501', '10', '1', './include/monitoring/downtime/downtime.php', '&o=vh', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Services', './img/icones/16x16/document_gear.gif', '20305', '2030502', '10', '1', './include/monitoring/downtime/downtime.php', '&o=vs', NULL, NULL, '1', NULL, NULL, NULL);

UPDATE `topology` SET topology_name = 'Downtime' WHERE topology_name = 'downtime' AND topology_parent = '203';

ALTER TABLE session ADD update_acl ENUM('0','1') NOT NULL;

ALTER TABLE giv_graphs_template ADD scaled ENUM('0','1') NULL DEFAULT '1' AFTER split_component;

UPDATE `topology` SET topology_name = 'By Status' WHERE topology_name = 'Services Details' AND topology_parent = '202' AND topology_page IS NULL;
UPDATE `topology` SET topology_name = 'By Host' WHERE topology_name = 'Details' AND topology_parent = '202' AND topology_page IS NULL;
UPDATE `topology` SET topology_name = 'By Host Group' WHERE topology_name = 'Hosts Groups' AND topology_parent = '202' AND topology_page IS NULL;
UPDATE `topology` SET topology_name = 'By Service Group' WHERE topology_name = 'Services Groups' AND topology_parent = '202' AND topology_page IS NULL;

DELETE FROM `topology` WHERE topology_page = '20203' AND topology_name = 'Grids';
DELETE FROM `topology` WHERE topology_page = '20208' AND topology_name = 'Grids';
DELETE FROM `topology` WHERE topology_page = '20211' AND topology_name = 'Grids';
DELETE FROM `topology` WHERE topology_parent = '20203';
DELETE FROM `topology` WHERE topology_parent = '20208';
DELETE FROM `topology` WHERE topology_parent = '20211';

UPDATE `topology` SET topology_name = 'Details' WHERE topology_name = 'Overview' AND topology_page IN ('20204', '20209', '20212');

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Performance Info', './img/icones/16x16/document_gear.gif', '102', '10203', '10', '1', './include/nagiosStats/performanceInfo.php', NULL, NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Process Info', './img/icones/16x16/calculator.gif', '102', '10202', '10', '1', './include/nagiosStats/processInfo.php', NULL, NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Graphs', './img/icones/16x16/oszillograph.gif', '102', '10201', '10', '1', './include/nagiosStats/nagiosStats.php', NULL, NULL, NULL, '1', NULL, NULL, NULL);

INSERT INTO `topology`(topology_name, topology_icone, topology_parent, topology_page, topology_order, topology_group, topology_url, topology_show) VALUES  ('Reload ACL', './img/icones/16x16/refresh.gif', '502', '50205', '50', '1', './include/options/accessLists/reloadACL/reloadACL.php', '1');
ALTER TABLE `session` ADD update_acl ENUM('0', '1') ;
ALTER TABLE `contact` ADD `contact_location` INT default '0' AFTER `contact_comment` ;
ALTER TABLE `host` ADD `host_location` INT default '0' AFTER `host_snmp_version` ;

UPDATE `contact` SET `contact_location` = '0';
UPDATE `host` SET `host_location` = '0';

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Actions Access', './img/icones/16x16/wrench.gif', 502, 50204, 25, 1, './include/options/accessLists/actionsACL/actionsConfig.php', NULL, '0', '0', '1', NULL, NULL, NULL);

UPDATE `topology` SET `topology_order` = '5' WHERE `topology`.`topology_page` = '50203' LIMIT 1 ;

CREATE TABLE `acl_actions` (
  `acl_action_id` int(11) NOT NULL auto_increment,
  `acl_action_name` varchar(255) default NULL,
  `acl_action_description` varchar(255) default NULL,
  `acl_action_activate` enum('0','1','2') default NULL,
  PRIMARY KEY  (`acl_action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `acl_actions_rules` (
  `aar_id` int(11) NOT NULL auto_increment,
  `acl_action_rule_id` int(11) default NULL,
  `acl_action_name` varchar(255) default NULL,
  PRIMARY KEY  (`aar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `acl_group_actions_relations` (
  `agar_id` int(11) NOT NULL auto_increment,
  `acl_action_id` int(11) default NULL,
  `acl_group_id` int(11) default NULL,
  PRIMARY KEY  (`agar_id`),
  KEY `acl_action_id` (`acl_action_id`),
  KEY `acl_group_id` (`acl_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'Logs', NULL, 5, 508, 11, 1, './include/Administration/configChangelog/viewLogs.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'Configuration', './img/icones/16x16/text_code.gif', 508, 50801, 10, 80, './include/Administration/configChangelog/viewLogs.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'Unhandled Problems', './img/icones/16x16/server_network_problem.gif', 201, 20105, 5, 1, './include/monitoring/status/monitoringHost.php', '&o=h_unhandled', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 20105, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'Unhandled Problems', './img/icones/16x16/row_delete.gif', 202, 20215, 5, 7, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 20215, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');

ALTER TABLE `contact` ADD `contact_crypt` CHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'MD5';

UPDATE `topology` SET `topology_url` = './include/Administration/about/about.php' WHERE `topology_url` = './include/options/about/about.php' LIMIT 1;

ALTER TABLE `nagios_server` ADD `ssh_port` INT NULL AFTER `id` , ADD `ssh_private_key` VARCHAR( 255 ) NULL AFTER `ssh_port` ;

ALTER TABLE `hostgroup` ADD `hg_notes` VARCHAR( 255 ) NULL AFTER `hg_snmp_version` ,
ADD `hg_notes_url` VARCHAR( 255 ) NULL AFTER `hg_notes` ,
ADD `hg_action_url` VARCHAR( 255 ) NULL AFTER `hg_notes_url` ,
ADD `hg_icon_image` INT NULL AFTER `hg_action_url` ,
ADD `hg_map_icon_image` INT NULL AFTER `hg_icon_image` ;

CREATE TABLE `hostgroup_hg_relation` (
`hgr_id` INT NULL AUTO_INCREMENT PRIMARY KEY ,
`hg_parent_id` INT NULL ,
`hg_child_id` INT NULL
) ENGINE = InnoDB;

ALTER TABLE `hostgroup_hg_relation` ADD FOREIGN KEY ( `hg_parent_id` ) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE ;
ALTER TABLE `hostgroup_hg_relation` ADD FOREIGN KEY ( `hg_child_id` ) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE ;

alter table nagios_server add nagios_perfdata VARCHAR( 255 ) NULL;
update nagios_server set nagios_perfdata = '/usr/local/nagios/var/service-perfdata';



INSERT INTO `nagios_macro` (`macro_id`, `macro_name`) VALUES ( NULL, '$_HOSTSNMPCOMMUNITY$');
INSERT INTO `nagios_macro` (`macro_id`, `macro_name`) VALUES ( NULL, '$_HOSTSNMPVERSION$');
INSERT INTO `nagios_macro` (`macro_id`, `macro_name`) VALUES ( NULL, '$_HOSTLOCATION$');

