alter table contact add contact_enable_notifications enum('0','1') default '0' after contact_oreon;
alter table contact add contact_template_id int(11) default null after contact_enable_notifications;

ALTER TABLE `contact` ADD INDEX ( `contact_template_id` );

ALTER TABLE `contact` 
	ADD CONSTRAINT `contact_ibfk_3` FOREIGN KEY (`contact_template_id`) REFERENCES `contact` (`contact_id`) ON DELETE SET NULL;

--
-- Structure de la table `auth_ressource`
--

CREATE TABLE IF NOT EXISTS `auth_ressource` (
  `ar_id` INT(11) NOT NULL AUTO_INCREMENT,
  `ar_type` VARCHAR(50) NOT NULL,
  `ar_enable` ENUM('0', '1') DEFAULT 0,
  `ar_order` INT(3) DEFAULT 0,
  PRIMARY KEY (`ar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Structure de la table `auth_ressource_info`
--

CREATE TABLE IF NOT EXISTS `auth_ressource_info` (
  `ar_id` INT(11) NOT NULL,
  `ari_name` VARCHAR(100) NOT NULL,
  `ari_value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ar_id`, `ari_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Contraintes pour la table `auth_ressource_info`
--
ALTER TABLE `auth_ressource_info`
  ADD CONSTRAINT `auth_ressource_info_ibfk_1` FOREIGN KEY (`ar_id`) REFERENCES `auth_ressource` (`ar_id`) ON DELETE CASCADE;
  
-- New ldap options
INSERT INTO `options` (`key`,`value`) values ('ldap_dns_use_ssl', '0');
INSERT INTO `options` (`key`,`value`) values ('ldap_dns_use_tls', '0');
INSERT INTO `options` (`key`,`value`) values ('ldap_srv_dns', '0');
INSERT INTO `options` (`key`,`value`) values ('ldap_dns_use_domain', '0');
INSERT INTO `options` (`key`,`value`) values ('broker', 'Ndo');

--
-- Structure de la table `downtime`
--
CREATE TABLE IF NOT EXISTS `downtime` (
  `dt_id` INT(11) NOT NULL AUTO_INCREMENT,
  `dt_name` VARCHAR(100) NOT NULL,
  `dt_description` VARCHAR(255) DEFAULT NULL,
  `dt_activate` ENUM('0', '1') DEFAULT '1',
  PRIMARY KEY (`dt_id`),
  KEY `downtime_idx01` (`dt_id`, `dt_activate`),
  UNIQUE KEY `downtime_idx02` (`dt_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_period`
--
CREATE TABLE IF NOT EXISTS `downtime_period` (
  `dt_id` INT(11) NOT NULL,
  `dtp_start_time` TIME NOT NULL,
  `dtp_end_time` TIME NOT NULL,
  `dtp_day_of_week` VARCHAR(15) DEFAULT NULL,
  `dtp_month_cycle` ENUM('first', 'last', 'all', 'none') DEFAULT 'all',
  `dtp_day_of_month` VARCHAR(100) DEFAULT NULL,
  `dtp_fixed` ENUM('0', '1') DEFAULT '1',
  `dtp_duration` INT DEFAULT NULL,
  `dtp_next_date` DATE DEFAULT NULL,
  `dtp_activate` ENUM('0', '1') DEFAULT '1',
  KEY `downtime_period_idx01` (`dt_id`, `dtp_activate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_host_relation`
--
CREATE TABLE IF NOT EXISTS `downtime_host_relation` (
	`dt_id` INT(11) NOT NULL,
	`host_host_id` INT(11) NOT NULL,
	PRIMARY KEY (`dt_id`, `host_host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_hostgroup_relation`
--
CREATE TABLE IF NOT EXISTS `downtime_hostgroup_relation` (
	`dt_id` INT(11) NOT NULL,
	`hg_hg_id` INT(11) NOT NULL,
	PRIMARY KEY (`dt_id`, `hg_hg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_service_relation`
--
CREATE TABLE IF NOT EXISTS `downtime_service_relation` (
	`dt_id` INT(11) NOT NULL,
	`host_host_id` INT(11) NOT NULL,
	`service_service_id` INT(11) NOT NULL,
	PRIMARY KEY (`dt_id`, `host_host_id`, `service_service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_servicegroup_relation`
--
CREATE TABLE IF NOT EXISTS `downtime_servicegroup_relation` (
	`dt_id` INT(11) NOT NULL,
	`sg_sg_id` INT(11) NOT NULL,
	PRIMARY KEY (`dt_id`, `sg_sg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Contraintes pour la table `downtime_period`
--
ALTER TABLE `downtime_period`
  ADD CONSTRAINT `downtime_period_ibfk_1` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE; 
  
--
-- Contraintes pour la table `downtime_host_relation`
--
ALTER TABLE `downtime_host_relation`
  ADD CONSTRAINT `downtime_host_relation_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downtime_host_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE;
  
--
-- Contraintes pour la table `downtime_hostgroup_relation`
--
ALTER TABLE `downtime_hostgroup_relation`
  ADD CONSTRAINT `downtime_hostgroup_relation_ibfk_1` FOREIGN KEY (`hg_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downtime_hostgroup_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE;
  
--
-- Contraintes pour la table `downtime_service_relation`
--
ALTER TABLE `downtime_service_relation`
  ADD CONSTRAINT `downtime_service_relation_ibfk_1` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downtime_service_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE;
  
--
-- Contraintes pour la table `downtime_service_relation`
--
ALTER TABLE `downtime_servicegroup_relation`
  ADD CONSTRAINT `downtime_servicegroup_relation_ibfk_1` FOREIGN KEY (`sg_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downtime_servicegroup_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE;
 
--
-- Alter contactgroup for ldap group
--
 ALTER TABLE `contactgroup` ADD `cg_type` varchar(10) default 'local';
 ALTER TABLE `contactgroup` ADD `cg_ldap_dn` varchar(255) default NULL;
 
 
 ALTER TABLE `contact` ADD `contact_register` TINYINT( 6 ) NOT NULL DEFAULT '0';
 
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Downtimes', NULL, 6, 606, 25, 1, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Downtime Scheduler', './img/icones/16x16/warning.gif', 606, 60600, 40, 1, './include/configuration/configDowntime/downtime.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Ajax forms', NULL, 606, 60601, 40, 1, './include/configuration/configDowntime/ajaxForms.php', NULL, '0', '0', '0', NULL, NULL, NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60600, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60600, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60600, 'w', './include/common/javascript/changetab.js', 'initChangeTab'); 
 
ALTER TABLE nagios_server ADD is_default INT DEFAULT '0' AFTER localhost;

UPDATE topology SET topology_name = 'Monitoring' WHERE topology_page = '5010102' AND topology_name = 'Nagios';

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'NDOutils', NULL, 609, NULL, NULL, 10, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
UPDATE `topology` SET `topology_group` = '10' WHERE `topology_parent` = 609 AND topology_name = 'ndo2db.cfg' LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '10' WHERE `topology_parent` = 609 AND topology_name = 'ndomod.cfg' LIMIT 1 ;

INSERT INTO `options` (`key`, `value`) VALUES ('ldap_contact_tmpl', '0');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_search_timeout', '60');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_search_limit', '60');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_auto_import', '0');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_last_acl_update', '0');

DELETE FROM topology WHERE topology_page IN (
'2021501',
'2021502',
'2021503',
'2020201',
'2020202',
'2020203',
'2020101',
'2020102',
'2020103',
'2020104');

DELETE FROM topology_JS WHERE id_page IN (
'2021501',
'2021502',
'2021503',
'2020201',
'2020202',
'2020203',
'2020101',
'2020102',
'2020103',
'2020104');


INSERT INTO `options` (`key`, `value`) VALUES('monitoring_ack_svc', '1');
INSERT INTO `options` (`key`, `value`) VALUES('monitoring_dwt_duration', '3600');
INSERT INTO `options` (`key`, `value`) VALUES('monitoring_ack_active_checks', '1');
INSERT INTO `options` (`key`, `value`) VALUES('monitoring_ack_persistent', '1');
INSERT INTO `options` (`key`, `value`) VALUES('monitoring_ack_notify', '0');
INSERT INTO `options` (`key`, `value`) VALUES('monitoring_ack_sticky', '1');
INSERT INTO `options` (`key`, `value`) VALUES('monitoring_dwt_fixed', '1');
INSERT INTO `options` (`key`, `value`) VALUES('monitoring_dwt_svc', '1');
INSERT INTO `options` (`key`, `value`) VALUES('tactical_host_limit', '100');
INSERT INTO `options` (`key`, `value`) VALUES('tactical_service_limit', '100');
INSERT INTO `options` (`key`, `value`) VALUES('tactical_refresh_interval', '20');

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Centreon-Broker', NULL, 609, NULL, NULL, 11, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Configuration', './img/icones/16x16/text_code_colored.gif', 609, 60904, 40, 11, './include/configuration/configCentreonBroker/centreon-broker.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60904, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60904, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60904, 'w', './include/common/javascript/changetab.js', 'initChangeTab');

-- Tables for config Centreon-broker
CREATE TABLE cfg_centreonbroker (
	config_id INT NOT NULL AUTO_INCREMENT,
	config_name VARCHAR(100) NOT NULL,
	config_filename VARCHAR(255) NOT NULL,
	config_activate ENUM('0', '1') DEFAULT '0',
	ns_nagios_server INT NOT NULL,
	PRIMARY KEY (`config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE cfg_centreonbroker_info (
	config_id INT NOT NULL,
	config_key VARCHAR(50) NOT NULL,
	config_value VARCHAR(255) NOT NULL,
	config_group VARCHAR (50) NOT NULL,
	config_group_id INT DEFAULT NULL,
	KEY cfg_centreonbroker_info_idx01 (config_id),
	KEY cfg_centreonbroker_info_idx02 (config_id, config_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cfg_centreonbroker_info`
  ADD CONSTRAINT `cfg_centreonbroker_info_ibfk_01` FOREIGN KEY (`config_id`) REFERENCES `cfg_centreonbroker` (`config_id`) ON DELETE CASCADE;
  
-- Add column for Centreon Broker configuration path in pollers
ALTER TABLE `nagios_server` ADD COLUMN `centreonbroker_cfg_path` VARCHAR(255) AFTER `nagios_perfdata`;

-- Move Downtime to host and service page

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'Downtimes', './img/icones/16x16/warning.gif', 601, 60106, 50, 1, './include/configuration/configDowntime/downtime.php', '&o=h', '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'Downtimes', './img/icones/16x16/warning.gif', 602, 60216, 100, 1, './include/configuration/configDowntime/downtime.php', '&o=s', '0', '0', '1', NULL, NULL, NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60106, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60106, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60106, 'w', './include/common/javascript/changetab.js', 'initChangeTab'); 

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60216, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60216, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60216, 'w', './include/common/javascript/changetab.js', 'initChangeTab'); 

DELETE FROM topology WHERE topology_page = '606';
DELETE FROM topology WHERE topology_parent = '606';

UPDATE topology SET topology_order = 40 WHERE topology_page = '50501';
UPDATE topology SET topology_order = 60, topology_page = '50503', topology_parent = '505', topology_icone = './img/icones/16x16/data_into.gif' WHERE topology_page = '503';

-- Add column for templates curves : link curves by host/service too
ALTER TABLE `giv_components_template` ADD `host_id` INT( 11 ) NULL AFTER `compo_id` ;
ALTER TABLE `giv_components_template` ADD `service_id` INT( 11 ) NULL AFTER `host_id` ;

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 607, NULL, './include/common/javascript/scriptaculous/s2.js ', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60701, NULL, './include/common/javascript/scriptaculous/s2.js ', NULL);

-- Add column for templates curves : ds_hidecurve / ds_legend / ds_jumpline
ALTER TABLE `giv_components_template`
ADD `ds_hidecurve` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `ds_order`,
ADD `ds_legend` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `ds_invert` ,
ADD `ds_jumpline` ENUM( '0', '1', '2', '3' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `ds_legend` ;

-- Add table : 'virtual_metrics' : RRD:CDEF [Virtual/Metrics]
CREATE TABLE IF NOT EXISTS `virtual_metrics` (
  `vmetric_id` int(11) NOT NULL AUTO_INCREMENT,
  `index_id` int(11) DEFAULT NULL,
  `vmetric_name` varchar(255) DEFAULT NULL,
  `def_type` enum('0','1') DEFAULT '0',
  `rpn_function` varchar(255) DEFAULT NULL,
  `warn` int(11) DEFAULT NULL,
  `crit` int(11) DEFAULT NULL,
  `unit_name` varchar(32) DEFAULT NULL,
  `hidden` enum('0','1') DEFAULT '0',
  `comment` text,
  `vmetric_activate` enum('0','1') DEFAULT NULL,
  `ck_state` enum('0','1','2') DEFAULT NULL,
  PRIMARY KEY (`vmetric_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Update 'topology' : RRD:CDEF [Virtual/Metrics]
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES
(NULL, 'Virtuals', NULL, 402, NULL, NULL, 46, NULL, NULL, '0', '0', '1', NULL, NULL, NULL),
(NULL, 'Metrics', './img/icones/16x16/chart.gif', 402, 40208, 80, 46, './include/views/graphs/virtualMetrics/virtualMetrics.php', NULL, '0', '0', '1', NULL, NULL, NULL);

-- Change 'ods_view_details' field 'metric_id' from int(11) to varchar(12) : RRD:CDEF [Virtual/Metrics]
ALTER TABLE `ods_view_details` CHANGE `metric_id` `metric_id` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- Add column for display warning and critical area in split view graph
ALTER TABLE `giv_components_template` 
ADD `ds_color_area_warn` VARCHAR(14) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ds_color_area` ,
ADD `ds_color_area_crit` VARCHAR(14) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ds_color_area_warn`;

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60705, NULL, './include/common/javascript/changetab.js', 'initChangeTab');

ALTER TABLE `host` CHANGE `host_register` `host_register` ENUM('0','1','2') NOT NULL DEFAULT '0';
ALTER TABLE `service` CHANGE `service_register` `service_register` ENUM('0','1','2') NOT NULL DEFAULT '0';

-- Update 'topology_JS' : move color_picker under modalbox

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 40204, 'a', './include/common/javascript/color_picker_mb.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 40204, 'c', './include/common/javascript/color_picker_mb.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 40205, 'a', './include/common/javascript/color_picker_mb.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 40205, 'c', './include/common/javascript/color_picker_mb.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 5010103, NULL, './include/common/javascript/color_picker_mb.js', NULL);

DELETE FROM `topology_JS` WHERE `id_page` IN ('20215', '20202', '20201', '20105', '20103', '20102') AND `Init` = 'initM' ;

-- Add column to save random line color
ALTER TABLE `ods_view_details` ADD `rnd_color` VARCHAR(7) NULL DEFAULT NULL AFTER `metric_id`;

UPDATE `options` SET `value` = 'ndo' WHERE `key` = 'broker' AND `value` = 'Ndo';


------------------------------------------------------------------
-- 2.3-RC1

ALTER TABLE nagios_server ADD COLUMN centreonbroker_module_path VARCHAR(255) DEFAULT NULL AFTER centreonbroker_cfg_path;
ALTER TABLE `cfg_centreonbroker` ADD COLUMN config_filename VARCHAR(255) NOT NULL AFTER config_name;
--
-- Structure de la table `cb_field`
--

CREATE TABLE IF NOT EXISTS `cb_field` (
  `cb_field_id` int(11) NOT NULL auto_increment,
  `fieldname` varchar(100) NOT NULL,
  `displayname` varchar(100) NOT NULL,
  `description` varchar(255) default NULL,
  `fieldtype` varchar(255) NOT NULL default 'text',
  `external` varchar(255) default NULL,
  PRIMARY KEY  (`cb_field_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_list`
--

CREATE TABLE IF NOT EXISTS `cb_list` (
  `cb_list_id` int(11) NOT NULL,
  `cb_field_id` int(11) NOT NULL,
  `default_value` varchar(255) default NULL,
  PRIMARY KEY  (`cb_list_id`,`cb_field_id`),
  UNIQUE KEY `cb_field_idx_01` (`cb_field_id`),
  KEY `fk_cb_list_1` (`cb_field_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_list_values`
--

CREATE TABLE IF NOT EXISTS `cb_list_values` (
  `cb_list_id` int(11) NOT NULL,
  `value_name` varchar(255) NOT NULL,
  `value_value` varchar(255) NOT NULL,
  PRIMARY KEY  (`cb_list_id`,`value_name`),
  KEY `fk_cb_list_values_1` (`cb_list_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_module`
--

CREATE TABLE IF NOT EXISTS `cb_module` (
  `cb_module_id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `libname` varchar(50) default NULL,
  `loading_pos` int(11) default NULL,
  `is_bundle` int(1) NOT NULL default '0',
  `is_activated` int(1) NOT NULL default '0',
  PRIMARY KEY  (`cb_module_id`),
  UNIQUE KEY `cb_module_idx01` (`name`),
  UNIQUE KEY `cb_module_idx02` (`libname`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_module_relation`
--

CREATE TABLE IF NOT EXISTS `cb_module_relation` (
  `cb_module_id` int(11) NOT NULL,
  `module_depend_id` int(11) NOT NULL,
  `inherit_config` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cb_module_id`,`module_depend_id`),
  KEY `fk_cb_module_relation_1` (`cb_module_id`),
  KEY `fk_cb_module_relation_2` (`module_depend_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_tag`
--

CREATE TABLE IF NOT EXISTS `cb_tag` (
  `cb_tag_id` int(11) NOT NULL auto_increment,
  `tagname` varchar(50) NOT NULL,
  PRIMARY KEY  (`cb_tag_id`),
  UNIQUE KEY `cb_tag_ix01` (`tagname`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_tag_type_relation`
--

CREATE TABLE IF NOT EXISTS `cb_tag_type_relation` (
  `cb_tag_id` int(11) NOT NULL,
  `cb_type_id` int(11) NOT NULL,
  PRIMARY KEY  (`cb_tag_id`,`cb_type_id`),
  KEY `fk_cb_tag_type_relation_1` (`cb_tag_id`),
  KEY `fk_cb_tag_type_relation_2` (`cb_type_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_type`
--

CREATE TABLE IF NOT EXISTS `cb_type` (
  `cb_type_id` int(11) NOT NULL auto_increment,
  `type_name` varchar(50) NOT NULL,
  `type_shortname` varchar(50) NOT NULL,
  `cb_module_id` int(11) NOT NULL,
  PRIMARY KEY  (`cb_type_id`),
  KEY `fk_cb_type_1` (`cb_module_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_type_field_relation`
--

CREATE TABLE IF NOT EXISTS `cb_type_field_relation` (
  `cb_type_id` int(11) NOT NULL,
  `cb_field_id` int(11) NOT NULL,
  `is_required` int(11) NOT NULL default '0',
  `order_display` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cb_type_id`,`cb_field_id`),
  KEY `fk_cb_type_field_relation_1` (`cb_type_id`),
  KEY `fk_cb_type_field_relation_2` (`cb_field_id`)
) ENGINE=InnoDB;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `cb_list`
--
ALTER TABLE `cb_list`
  ADD CONSTRAINT `fk_cb_list_1` FOREIGN KEY (`cb_field_id`) REFERENCES `cb_field` (`cb_field_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_list_values`
--
ALTER TABLE `cb_list_values`
  ADD CONSTRAINT `fk_cb_list_values_1` FOREIGN KEY (`cb_list_id`) REFERENCES `cb_list` (`cb_list_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_module_relation`
--
ALTER TABLE `cb_module_relation`
  ADD CONSTRAINT `fk_cb_module_relation_1` FOREIGN KEY (`cb_module_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cb_module_relation_2` FOREIGN KEY (`module_depend_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_tag_type_relation`
--
ALTER TABLE `cb_tag_type_relation`
  ADD CONSTRAINT `fk_cb_tag_type_relation_1` FOREIGN KEY (`cb_tag_id`) REFERENCES `cb_tag` (`cb_tag_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cb_tag_type_relation_2` FOREIGN KEY (`cb_type_id`) REFERENCES `cb_type` (`cb_type_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_type`
--
ALTER TABLE `cb_type`
  ADD CONSTRAINT `fk_cb_type_1` FOREIGN KEY (`cb_module_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_type_field_relation`
--
ALTER TABLE `cb_type_field_relation`
  ADD CONSTRAINT `fk_cb_type_field_relation_1` FOREIGN KEY (`cb_type_id`) REFERENCES `cb_type` (`cb_type_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cb_type_field_relation_2` FOREIGN KEY (`cb_field_id`) REFERENCES `cb_field` (`cb_field_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- Insert for Centreon Broker configurations

--
-- Contenu de la table `cb_tag`
--

INSERT INTO `cb_tag` (`cb_tag_id`, `tagname`) VALUES
(4, 'correlation'),
(2, 'input'),
(3, 'logger'),
(1, 'output');

--
-- Contenu de la table `cb_module`
--

INSERT INTO `cb_module` (`cb_module_id`, `name`, `libname`, `loading_pos`, `is_bundle`, `is_activated`) VALUES
(1, 'SQL', 'sql.so', 80, 0, 1),
(2, 'TCP', 'tcp.so', 50, 0, 1),
(3, 'file', 'file.so', 50, 0, 1),
(4, 'local', 'local.so', 50, 0, 1),
(5, 'NDO', 'ndo.so', 80, 0, 1),
(6, 'NEB', 'neb.so', 10, 0, 1),
(7, 'RRD', 'rrd.so', 30, 0, 1),
(8, 'Storage', 'storage.so', 20, 0, 1),
(9, 'Core', NULL, NULL, 1, 1),
(10, 'Centreon Storage', NULL, NULL, 1, 1),
(11, 'Compression', 'compression.so', 60, 0, 1),
(12, 'Failover', NULL, NULL, 0, 1),
(13, 'Correlation', 'correlation.so', 20, 0, 1);

--
-- Contenu de la table `cb_type`
--

INSERT INTO `cb_type` (`cb_type_id`, `type_name`, `type_shortname`, `cb_module_id`) VALUES
(3, 'IPv4', 'ipv4', 2),
(10, 'IPv6', 'ipv6', 2),
(11, 'File', 'file', 3),
(12, 'Local Server Socket', 'local_server', 4),
(13, 'RRD File Generator', 'rrd', 7),
(14, 'Perfdata Generator (Centreon Storage)', 'storage', 8),
(15, 'Local Client Socket', 'local_client', 4),
(16, 'Broker SQL Database', 'sql', 1),
(17, 'File', 'file', 9),
(18, 'Standard', 'standard', 9),
(19, 'Syslog', 'syslog', 9),
(20, 'Compressor', 'compressor', 11),
(21, 'Failover', 'failover', 12),
(22, 'Correlation', 'correlation', 13);

--
-- Contenu de la table `cb_field`
--

INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES
(1, 'port', 'Connection port', 'Port for listen or connect in TCP', 'int', NULL),
(2, 'host', 'Host to connect to', NULL, 'text', NULL),
(3, 'ca_certificate', 'Trusted CA''s certificate', NULL, 'text', NULL),
(4, 'private_key', 'Private key file.', NULL, 'text', NULL),
(5, 'public_cert', 'Public certificate', NULL, 'text', NULL),
(6, 'tls', 'Enable TLS encryption', NULL, 'radio', NULL),
(7, 'db_host', 'DB host', NULL, 'text', NULL),
(8, 'db_user', 'DB user', NULL, 'text', NULL),
(9, 'db_password', 'DB password', NULL, 'text', NULL),
(10, 'db_name', 'DB name', NULL, 'text', NULL),
(11, 'path', 'File path', NULL, 'text', NULL),
(12, 'protocol', 'Serialization Protocol', NULL, 'select', NULL),
(13, 'metrics_path', 'Metrics RRD Directory', NULL, 'text', NULL),
(14, 'status_path', 'Status RRD Directory', NULL, 'text', NULL),
(15, 'db_type', 'DB type', NULL, 'select', NULL),
(16, 'interval', 'Interval Length', 'Interval Length in seconds', 'int', NULL),
(17, 'length', 'RRD Length', 'RRD storage duration.', 'int', NULL),
(18, 'db_port', 'DB Port', 'Port on which the DB server listens', 'int', NULL),
(19, 'name', 'Name of the logger', 'For a file logger this is the path to the file. For a standard logger, one of ''stdout'' or ''stderr''.', 'text', NULL),
(20, 'config', 'Configuration messages', 'Enable or not configuration messages logging.', 'radio', NULL),
(21, 'debug', 'Debug messages', 'Enable or not debug messages logging.', 'radio', NULL),
(22, 'error', 'Error messages', 'Enable or not error messages logging.', 'radio', NULL),
(23, 'info', 'Informational messages', 'Enable or not informational messages logging.', 'radio', NULL),
(24, 'level', 'Logging level', 'How much messages must be logged.', 'select', NULL),
(25, 'compression', 'Compression (zlib)', 'Enable or not data stream compression.', 'radio', NULL),
(26, 'compression_level', 'Compression level', 'Ranges from 1 (no compression) to 9 (best compression). -1 is the default', 'int', NULL),
(27, 'compression_buffer', 'Compression buffer size', 'The higher the buffer size is, the best compression. This however increase data streaming latency. Use with caution.', 'int', NULL),
(28, 'failover', 'Failover Name', 'Name of the input or output object that will act as failover.', 'text', NULL),
(29, 'file', 'Correlation File', 'Path to the correlation file which holds host, services, dependencies and parenting definitions.', 'text', NULL);

--
-- Contenu de la table `cb_list`
--

INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`) VALUES
(1, 6, 'no'),
(1, 20, 'yes'),
(1, 21, 'no'),
(1, 22, 'yes'),
(1, 23, 'no'),
(1, 25, 'no'),
(2, 12, NULL),
(3, 15, NULL),
(4, 24, NULL);

--
-- Contenu de la table `cb_list_values`
--

INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`) VALUES
(1, 'No', 'no'),
(1, 'Yes', 'yes'),
(2, 'NDO Protocol', 'ndo'),
(3, 'DB2', 'db2'),
(3, 'InterBase', 'ibase'),
(3, 'MySQL', 'mysql'),
(3, 'ODBC', 'odbc'),
(3, 'Oracle', 'oci'),
(3, 'PostgreSQL', 'postgresql'),
(3, 'SQLite', 'sqlite'),
(3, 'Sysbase', 'tds'),
(4, 'Base', 'high'),
(4, 'Detailed', 'medium'),
(4, 'Very detailed', 'low');

--
-- Contenu de la table `cb_module_relation`
--

INSERT INTO `cb_module_relation` (`cb_module_id`, `module_depend_id`, `inherit_config`) VALUES
(1, 6, 0),
(1, 8, 0),
(1, 12, 1),
(2, 11, 1),
(2, 12, 1),
(3, 11, 1),
(3, 12, 1),
(4, 11, 1),
(4, 12, 1),
(5, 6, 0),
(7, 8, 0),
(7, 12, 1),
(8, 6, 0),
(8, 12, 1),
(13, 6, 0);

--
-- Contenu de la table `cb_tag_type_relation`
--

INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`) VALUES
(1, 3),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(2, 3),
(2, 10),
(2, 11),
(2, 12),
(2, 15),
(3, 17),
(3, 18),
(3, 19),
(4, 22);

--
-- Contenu de la table `cb_type_field_relation`
--

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(3, 1, 1, 1),
(3, 2, 0, 2),
(3, 3, 0, 7),
(3, 4, 0, 5),
(3, 5, 0, 6),
(3, 6, 1, 4),
(3, 12, 1, 3),
(10, 1, 1, 1),
(10, 2, 0, 2),
(10, 3, 0, 7),
(10, 4, 0, 5),
(10, 5, 0, 6),
(10, 6, 1, 4),
(10, 12, 1, 3),
(11, 11, 1, 1),
(11, 12, 1, 2),
(12, 11, 1, 1),
(12, 12, 1, 2),
(13, 1, 0, 4),
(13, 11, 0, 3),
(13, 13, 1, 1),
(13, 14, 1, 2),
(14, 7, 1, 4),
(14, 8, 1, 5),
(14, 9, 1, 6),
(14, 10, 1, 7),
(14, 15, 1, 3),
(14, 16, 1, 1),
(14, 17, 1, 2),
(15, 11, 1, 1),
(15, 12, 1, 2),
(16, 7, 1, 2),
(16, 8, 1, 3),
(16, 9, 1, 4),
(16, 10, 1, 5),
(16, 15, 1, 1),
(17, 19, 1, 1),
(17, 20, 1, 2),
(17, 21, 1, 3),
(17, 22, 1, 4),
(17, 23, 1, 5),
(17, 24, 1, 6),
(18, 19, 1, 1),
(18, 20, 1, 2),
(18, 21, 1, 3),
(18, 22, 1, 4),
(18, 23, 1, 5),
(18, 24, 1, 6),
(19, 20, 1, 1),
(19, 21, 1, 2),
(19, 22, 1, 3),
(19, 23, 1, 4),
(19, 24, 1, 5),
(20, 25, 0, 101),
(20, 26, 0, 102),
(20, 27, 0, 103),
(21, 28, 0, 2),
(22, 29, 1, 1);


INSERT INTO `nagios_macro` (`macro_id`, `macro_name`) VALUES ( NULL, '$LONGSERVICEOUTPUT$');

ALTER TABLE `session` CHANGE `session_id` `session_id` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-------------------------------------------------------------------------------
-- 2.3-RC2

--
-- Add size_to_max option to graph templates
--
ALTER TABLE `giv_graphs_template` ADD COLUMN `size_to_max` TINYINT(6) NOT NULL AFTER upper_limit;

--
-- Add password type for Centreon Broker field configuration
--
UPDATE  `cb_field` SET  `fieldtype` =  'password' WHERE  `cb_field_id` = 9;

--
-- Insert DB Port for Centreon Broker Configuration
--
INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES (14, 18, 1, 5);
INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES (16, 18, 1, 3);

--
-- Update default information for Centreon Broker configuration in poller
-- 
UPDATE `nagios_server` SET `centreonbroker_cfg_path` = '/etc/centreon-broker', `centreonbroker_module_path` = '/usr/share/centreon/lib/centreon-broker';

--
-- Update progress bar lib
--
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/scriptaculous/jsProgressBarHandler.js ' WHERE `PathName_js` = './include/common/javascript/scriptaculous/s2.js ';

UPDATE contact SET contact_enable_notifications = '1';
UPDATE contact SET contact_register = '1';

DELETE FROM topology WHERE topology_url LIKE './include/monitoring/mysql_log/viewLog.php' AND topology_name LIKE 'All Logs' AND topology_show = '0';
DELETE FROM topology WHERE topology_url LIKE './include/monitoring/mysql_log/viewErrors.php' AND topology_name LIKE 'Warnings' AND topology_show = '0';
DELETE FROM topology WHERE topology_page = '20313' OR topology_parent = '20313';
DELETE FROM topology WHERE topology_page = '20312' OR topology_parent = '20312';

INSERT INTO options (`key`, `value`) VALUES ('centstorage', '1');

ALTER TABLE `acl_topology` ADD `acl_comments` text DEFAULT NULL AFTER acl_topo_alias ;

UPDATE `informations` SET `value` = '2.3.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.2.2' LIMIT 1;
 