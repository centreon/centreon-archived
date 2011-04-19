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

-- Add column to save random line color
ALTER TABLE `ods_view_details` ADD `rnd_color` VARCHAR(7) NULL DEFAULT NULL AFTER `metric_id`;

UPDATE `options` SET `value` = 'ndo' WHERE `key` = 'broker' AND `value` = 'Ndo';

UPDATE `informations` SET `value` = '2.3.0-b4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-b3' LIMIT 1;
