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

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'Logs', NULL, 5, 508, 11, 1, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'Configuration', './img/icones/16x16/text_code.gif', 508, 50801, 10, 80, './include/administration/configChangelog/viewLogs.php', NULL, '0', '0', '1', NULL, NULL, NULL);

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

CREATE TABLE `centreon`.`hostgroup_hg_relation` (
`hgr_id` INT NULL AUTO_INCREMENT PRIMARY KEY ,
`hg_parent_id` INT NULL ,
`hg_child_id` INT NULL
) ENGINE = InnoDB;

ALTER TABLE `hostgroup_hg_relation` ADD FOREIGN KEY ( `hg_parent_id` ) REFERENCES `centreon`.`hostgroup` (
`hg_id`
) ON DELETE CASCADE ;

ALTER TABLE `hostgroup_hg_relation` ADD FOREIGN KEY ( `hg_child_id` ) REFERENCES `centreon`.`hostgroup` (
`hg_id`
) ON DELETE CASCADE ;


