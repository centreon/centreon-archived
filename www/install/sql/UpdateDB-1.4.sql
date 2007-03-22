-- 03/01/2007 ---

ALTER TABLE `topology` ADD `topology_style_class` VARCHAR( 255 ) NULL ;
ALTER TABLE `topology` ADD `topology_style_id` VARCHAR( 255 ) NULL ;
ALTER TABLE `topology` ADD `topology_OnClick` VARCHAR( 255 ) NULL ;

-- PO here

-- 19/01/2007
DELETE FROM topology WHERE topology_page = '60601';
DELETE FROM topology WHERE topology_page = '60602';
DELETE FROM topology WHERE topology_page = '60603';
DELETE FROM topology WHERE topology_page = '606';

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_host_template_model', './img/icones/16x16/server_client_ext.gif', 601, 60103, 30, 1, './include/configuration/configObject/host_template_model/hostTemplateModel.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_service_template_model', './img/icones/16x16/element_template.gif', 602, 60206, 50, 1, './include/configuration/configObject/service_template_model/serviceTemplateModel.php', NULL, '0', '0', '1');
UPDATE topology SET topology_group = 2 WHERE topology_page = 60705;
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'mod_purgePolicy', './img/icones/16x16/data_down.gif', '607', '60708', '60', '2', './include/configuration/configObject/purgePolicy/purgePolicy.php', NULL , '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_perfparse', NULL, 607, NULL, NULL, 31, NULL, NULL, '0', '0', '1');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'a', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'c', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'w', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'a', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'c', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'w', './include/common/javascript/changetab.js       ', 'initChangeTab');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60102, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60102, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');

-- 20/01/2007
UPDATE `oreon_informations` SET `value` = '1.4' WHERE CONVERT( `key` USING utf8 ) = 'version' AND CONVERT( `value` USING utf8 ) = '1.3.4' LIMIT 1 ;
UPDATE `oreon_informations` SET `value` = '1.4' WHERE CONVERT( `key` USING utf8 ) = 'version' AND CONVERT( `value` USING utf8 ) = '1.3.3' LIMIT 1 ;
ALTER TABLE `escalation` ADD `esc_alias` VARCHAR( 255 ) NULL AFTER `esc_name` ;
ALTER TABLE `lca_define` ADD `lca_alias` VARCHAR( 255 ) NULL AFTER `lca_name` ;

-- 22/01/2007

--
-- Quand on rajoute, on rajoute a la Fin Merci !
--

CREATE TABLE `modules_informations` (
  `id` int(11) NOT NULL auto_increment,
  `internal_name` varchar(254) default NULL,
  `name` varchar(254) default NULL,
  `version` int(11) default NULL,
  `is_installed` enum('0','1') default NULL,
  `is_removeable` enum('0','1') default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 27/01/2006

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'views_title1', NULL, 402, NULL, NULL, 41, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'views_title2', NULL, 402, NULL, NULL, 40, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_host_graph', './img/icones/16x16/dot-chart.gif', 402, 40210, 10, 40, './include/views/graphs/graphODS/summaryODS.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_views_graphShow', './img/icones/16x16/column-chart.gif', 402, 40211, 20, 40, './include/views/graphs/graphODS/graphODS.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'views_title3', NULL, 402, NULL, NULL, 42, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'views_title4', NULL, 402, NULL, NULL, 43, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 4, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 402, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40201, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40202, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40203, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40208, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40210, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40211, NULL, './include/common/javascript/datePicker.js', '');

UPDATE topology SET topology_group = '4' WHERE topology_page = '40204';
UPDATE topology SET topology_group = '4' WHERE topology_page = '40205';
UPDATE topology SET topology_group = '3' WHERE topology_page = '40203';
UPDATE topology SET topology_group = '3' WHERE topology_page = '40208';


-- 28/01/2006

ALTER TABLE `general_opt` ADD `nagios_init_script` VARCHAR( 255 ) NULL AFTER `nagios_path_bin` ;
UPDATE `general_opt` SET `nagios_init_script` = '/etc/init.d/nagios';


-- 26/02/2007

CREATE TABLE `ods_view_details` (
`dv_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`index_id` INT NULL ,
`metric_id` INT NULL ,
`contact_id` INT NULL ,
`all_user` ENUM( "0", "1" ) NULL
) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'menu_ODS', '', 50101, 5010110, 100, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=ods', '0', '0', '1');

-- 28/02/2007

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 6, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 6, 'mc', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 601, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 601, 'mc', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'mc', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'mc', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 6, 'mc', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 601, 'mc', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'mc', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60201, 'mc', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'mc', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'mc', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 602, 'mc', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 6, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 601, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 602, 'mc', './include/common/javascript/changetab.js', 'initChangeTab()');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 604, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60201, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');

ALTER TABLE `modules_informations` CHANGE `internal_name` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `name` `rname` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `version` `release` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE `modules_informations` ADD `infos` TEXT NULL ,
ADD `author` VARCHAR( 255 ) NULL ,
ADD `lang_files` ENUM( '0', '1' ) NULL ;
ALTER TABLE `modules_informations` DROP `is_installed` ;

UPDATE `topology` SET `topology_name` = 'm_modules',
`topology_icone` = './img/icones/16x16/press.gif',
`topology_parent` = '501',
`topology_page` = '50103',
`topology_order` = '30',
`topology_group` = '1',
`topology_url` = './include/options/oreon/modules/modules.php',
`topology_url_opt` = NULL ,
`topology_popup` = '0',
`topology_modules` = '0',
`topology_show` = '1',
`topology_style_class` = NULL ,
`topology_style_id` = NULL ,
`topology_OnClick` = NULL WHERE `topology_page` = 50103 LIMIT 1 ;

ALTER TABLE `modules_informations` CHANGE `release` `mod_release` VARCHAR( 255 ) NULL DEFAULT NULL;

-- 01/03/2007

ALTER TABLE `modules_informations` ADD `sql_files` ENUM( '0', '1' ) NULL , ADD `php_files` ENUM( '0', '1' ) NULL ;


-- 05/02/2007
-- Table: log_archive_host
ALTER TABLE `log_archive_host` DROP `UNDETERMINATETimeScheduled` ,
DROP `UNDETERMINATETimeUnScheduled` ;

ALTER TABLE `log_archive_host` CHANGE `UPTimeUnScheduled` `UPnbEvent` INT( 11 ) NULL DEFAULT NULL ,
CHANGE `DOWNTimeUnScheduled` `DOWNnbEvent` INT( 11 ) NULL DEFAULT NULL ,
CHANGE `UNREACHABLETimeUnScheduled` `UNREACHABLEnbEvent` INT( 11 ) NULL DEFAULT NULL ;

ALTER TABLE `log_archive_host` ADD `UPTimeAverageAck` INT NOT NULL AFTER `UPnbEvent` ,
ADD `UPTimeAverageRecovery` INT NOT NULL AFTER `UPTimeAverageAck` ;

ALTER TABLE `log_archive_host` ADD `DOWNTimeAverageAck` INT NOT NULL AFTER `DOWNnbEvent` ,
ADD `DOWNTimeAverageRecovery` INT NOT NULL AFTER `DOWNTimeAverageAck` ;

ALTER TABLE `log_archive_host` ADD `UNREACHABLETimeAverageAck` INT NOT NULL AFTER `UNREACHABLEnbEvent` ,
ADD `UNREACHABLETimeAverageRecovery` INT NOT NULL AFTER `UNREACHABLETimeAverageAck` ;

-- log_archive_service
ALTER TABLE `log_archive_service` CHANGE `OKTimeUnScheduled` `OKnbEvent` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `WARNINGTimeUnScheduled` `WARNINGnbEvent` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `UNKNOWNTimeUnScheduled` `UNKNOWNnbEvent` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `CRITICALTimeUnScheduled` `CRITICALnbEvent` INT( 11 ) NOT NULL DEFAULT '0';

ALTER TABLE `log_archive_service` ADD `OKTimeAverageAck` INT NOT NULL AFTER `OKnbEvent` ,
ADD `OKTimeAverageRecovery` INT NOT NULL AFTER `OKTimeAverageAck` ;

ALTER TABLE `log_archive_service` ADD `WARNINGTimeAverageAck` INT NOT NULL AFTER `WARNINGnbEvent` ,
ADD `WARNINGTimeAverageRecovery` INT NOT NULL AFTER `WARNINGTimeAverageAck`;

ALTER TABLE `log_archive_service` ADD `UNKNOWNTimeAverageAck` INT NOT NULL AFTER `UNKNOWNnbEvent` ,
ADD `UNKNOWNTimeAverageRecovery` INT NOT NULL AFTER `UNKNOWNTimeAverageAck`;

ALTER TABLE `log_archive_service` ADD `CRITICALTimeAverageAck` INT NOT NULL AFTER `CRITICALnbEvent` ,
ADD `CRITICALTimeAverageRecovery` INT NOT NULL AFTER `CRITICALTimeAverageAck`;

DELETE FROM `topology_JS` WHERE `id_page` = 3 AND `id_page` = 307 AND `id_page` = 30701 AND `id_page` = 30702;
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 3, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', 'initTimeline');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 307, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', 'initTimeline');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 30701, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', 'initTimeline');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 30702, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', 'initTimeline');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 3, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 307, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 30701, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 30702, NULL, './include/common/javascript/datePicker.js', '');

ALTER TABLE `session` ADD `s_nbHostsUp` INT NULL ,ADD `s_nbHostsDown` INT NULL ,ADD `s_nbHostsUnreachable` INT NULL ,ADD `s_nbHostsPending` INT NULL ,ADD `s_nbServicesOk` INT NULL ,ADD `s_nbServicesWarning` INT NULL ,ADD `s_nbServicesCritical` INT NULL ,ADD `s_nbServicesPending` INT NULL ,ADD `s_nbServicesUnknown` INT NULL ;

-- 14/03/2007

ALTER TABLE `meta_service` ADD `meta_display` VARCHAR( 255 ) NULL AFTER `meta_name` ;
ALTER TABLE `cfg_nagios` ADD `broker_module` VARCHAR( 255 ) NULL ;

-- 15/03/2007

-- Insert sous menu

INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'm_service_detail', NULL , '202', NULL , NULL , '7', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_details', NULL , '202', NULL , NULL , '8', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'm_meta_service', NULL , '202', NULL , NULL , '10', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_nagios', NULL , '202', NULL , NULL , '12', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_service_group', NULL , '202', NULL , NULL , '10', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_details_HG', NULL , '202', NULL , NULL , '9', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);


-- Update old menu

UPDATE `topology` SET `topology_name` = 'mon_svc_all', `topology_icone` = './img/icones/16x16/row.gif',`topology_group` = '7' WHERE `topology_page` = 20201 LIMIT 1 ;
UPDATE `topology` SET `topology_icone` = './img/icones/16x16/row_delete.gif',`topology_group` = '7' WHERE `topology_page` =20202 LIMIT 1 ;
UPDATE `topology` SET `topology_icone` = './img/icones/16x16/services_grid.gif', `topology_group` = '8' WHERE `topology_page` =20203 LIMIT 1 ;
UPDATE `topology` SET `topology_icone` = './img/icones/16x16/table_view.gif', `topology_group` = '8' WHERE `topology_page` =20204 LIMIT 1 ;
UPDATE `topology` SET `topology_icone` = './img/icones/16x16/column.gif', `topology_group` = '8' WHERE `topology_page` =20205 LIMIT 1 ;
UPDATE `topology` SET `topology_icone` = './img/icones/16x16/stopwatch.gif', `topology_group` = '11' WHERE `topology_page` =20207 LIMIT 1 ;
UPDATE `topology` SET `topology_icone` = './img/icones/16x16/calculator.gif', `topology_group` = '11' WHERE `topology_page` =20206 LIMIT 1 ;

-- Ajoute sous menu probleme

INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_svc_warning', NULL , '20202', '2020201', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svc_warning', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_svc_critical', NULL , '20202', '2020202', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svc_critical', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_svc_unknown', NULL , '20202', '2020203', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svc_unknown', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES ( NULL , 'mon_svc_ok', NULL , '20201', '2020101', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svc_ok', '0', '0', '1', NULL , NULL , NULL);

-- Ajoute menu HG
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_status_grid', './img/icones/16x16/services_grid.gif', 202, 20208, 30, 9, './include/monitoring/status/monitoringService.php', '&o=svcgridHG', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_status_resume', './img/icones/16x16/table_view.gif', 202, 20209, 40, 9, './include/monitoring/status/monitoringService.php', '&o=svcOVHG', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_status_summary', './img/icones/16x16/column.gif', 202, 20210, 50, 9, './include/monitoring/status/monitoringService.php', '&o=svcSumHG', '0', '0', '1');
-- Ajoute menu SG
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_status_grid', './img/icones/16x16/services_grid.gif', 202, 20211, 30, 10, './include/monitoring/status/monitoringService.php', '&o=svcgridSG', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_status_resume', './img/icones/16x16/table_view.gif', 202, 20212, 30, 10, './include/monitoring/status/monitoringService.php', '&o=svcOVSG', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_status_summary', './img/icones/16x16/column.gif', 202, 20213, 30, 10, './include/monitoring/status/monitoringService.php', '&o=svcSumSG', '0', '0', '1');
-- Ajoute menu 

INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES ('' , 'mon_problems', NULL , '20203', '2020301', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgrid&problem=1', '0', '0', '1', NULL , NULL , NULL);


INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES ('' , 'mon_acknowloedge', NULL , '20203', '2020302', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgrid&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES ('' , 'mon_not_acknowloedge', NULL , '20203', '2020303', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgrid&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de resum� 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20204', '2020401', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOV&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20204', '2020402', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOV&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20204', '2020403', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOV&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de sommaire 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20205', '2020501', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSum&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20205', '2020502', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSum&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20205', '2020502', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSum&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);


-- Grid by HG
-- Grille de status 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20208', '2020801', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridHG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20208', '2020802', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridHG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20208', '2020803', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridHG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de resum� 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20209', '2020901', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVHG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20209', '2020902', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVHG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20209', '2020903', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVHG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de sommaire 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20210', '2021001', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumHG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20210', '2021002', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumHG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20210', '2021002', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumHG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grid by SG
-- Grille de status 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20211', '2021101', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridSG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20211', '2021102', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridSG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20211', '2021103', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridSG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de resum� 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20212', '2021201', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVSG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20212', '2021202', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVSG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20212', '2021203', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVSG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de sommaire 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20213', '2021301', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumSG&problem=1', '0', '0','1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20213', '2021302', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumSG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20213', '2021302', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumSG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Topology JS

INSERT INTO `topology_JS` ( `id_t_js` , `id_page` , `o` , `PathName_js` , `Init` )VALUES ('' , '2020101', NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` ( `id_t_js` , `id_page` , `o` , `PathName_js` , `Init` )VALUES ('' , '2020201', NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` ( `id_t_js` , `id_page` , `o` , `PathName_js` , `Init` )VALUES ('' , '2020202', NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` ( `id_t_js` , `id_page` , `o` , `PathName_js` , `Init` )VALUES ('' , '2020203', NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');


-- le 17/03/2007

UPDATE `topology` SET `topology_url` = '' WHERE `topology`.`topology_page` = 203 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '60',`topology_group` = '31' WHERE `topology`.`topology_page` = 20301 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '70',`topology_group` = '31' WHERE `topology`.`topology_page` = 20302 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '80',`topology_group` = '31' WHERE `topology`.`topology_page` = 20303 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '80',`topology_group` = '31' WHERE `topology`.`topology_page` = 20304 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '90',`topology_group` = '32' WHERE `topology`.`topology_page` = 20305 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '100',`topology_group` = '32' WHERE `topology`.`topology_page` = 20306 LIMIT 1 ;

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('' , 'm_log_advanced', NULL , '203', NULL , NULL , '30', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('' , 'm_log_lite', NULL , '203', NULL , NULL , '31', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);

-- All Logs -- 

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_all_logs', './img/icones/16x16/index.gif', 203, 20311, 10, 30, './include/monitoring/mysql_log/viewLog.php', NULL, NULL, NULL, '1', NULL, NULL, '');

-- Notifications -- 

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_notify_logs', './img/icones/16x16/mail_attachment.gif', 203, 20312, 20, 30, './include/monitoring/mysql_log/viewNotifications.php', NULL, NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_host', NULL, 20312, 2031201, 10, 30, './include/monitoring/mysql_log/viewNotifications.php', '&o=notif_host', '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_service', NULL, 20312, 2031202, 20, 30, './include/monitoring/mysql_log/viewNotifications.php', '&o=notif_svc', '0', '0', '1', NULL, NULL, NULL);

-- Alertes -- 

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_alerts_log', './img/icones/16x16/scroll_warning.gif', 203, 20313, 30, 30, './include/monitoring/mysql_log/viewAlerts.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_host', NULL, 20313, 2031301, 10, 30, './include/monitoring/mysql_log/viewAlerts.php', '&o=alerts_host', '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_service', NULL, 20313, 2031302, 20, 30, './include/monitoring/mysql_log/viewAlerts.php', '&o=alerts_svc', '0', '0', '1', NULL, NULL, NULL);


-- Errors -- 

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_warning_log', './img/icones/16x16/scroll_delete.gif', 203, 20314, 40, 30, './include/monitoring/mysql_log/viewErrors.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology_JS` ( `id_t_js` , `id_page` , `o` , `PathName_js` , `Init` ) VALUES ('' , '20314', NULL , './include/common/javascript/datePicker.js', NULL);

UPDATE `topology` SET `topology_url` = '' WHERE `topology`.`topology_id` =4 LIMIT 1 ;
UPDATE `topology` SET `topology_url` = '' WHERE `topology`.`topology_id` =110 LIMIT 1 ;

UPDATE `topology` SET `topology_group` = '40' WHERE `topology`.`topology_page` = '40210' LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '40' WHERE `topology`.`topology_page` = '40211' LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '41' WHERE `topology`.`topology_page` = '40201' LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '41' WHERE `topology`.`topology_page` = '40202' LIMIT 1 ;


UPDATE `topology` SET `topology_group` = '41' WHERE `topology`.`topology_group` = 1 AND `topology`.`topology_page` IS NULL  LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '40' WHERE `topology`.`topology_group` = 3 AND `topology`.`topology_page` IS NULL  LIMIT 1 ;

UPDATE `topology` SET `topology_group` = '41' WHERE `topology`.`topology_group` = 1 AND `topology`.`topology_page` IS NULL  LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '40' WHERE `topology`.`topology_group` = 3 AND `topology`.`topology_page` IS NULL  LIMIT 1 ;


UPDATE `general_opt` SET `template` = 'Basic' WHERE `general_opt`.`gopt_id` =1 LIMIT 1 ;

UPDATE `topology` SET `topology_group` = '42' WHERE `topology`.`topology_page` = 40203 LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '43' WHERE `topology`.`topology_page` = 40204 LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '43' WHERE `topology`.`topology_page` = 40205 LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '42' WHERE `topology`.`topology_page` = 40207 LIMIT 1 ;

-- le 20/03/2007

-- 
-- Structure de la table `css_color_menu`
-- 

CREATE TABLE `css_color_menu` (
  `id_css_color_menu` int(11) NOT NULL auto_increment,
  `menu_nb` int(11) default NULL,
  `css_name` varchar(255) character set utf8 default NULL,
  PRIMARY KEY  (`id_css_color_menu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'genOpt_css', './img/icones/16x16/colors.gif', 50101, 5010109, 90, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=css', '0', '0', '1');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 20311, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 20312, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 20313, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 20314, NULL, './include/common/javascript/datePicker.js', '');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 2031201, 'notif_host', './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 2031202, 'notif_svc', './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 2031301, 'alerts_host', './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 2031302, 'alerts_svc', './include/common/javascript/datePicker.js', '');

UPDATE `topology` SET `topology_group` = '42' WHERE `topology`.`topology_id` =116 LIMIT 1 ;


-- 21/03/2007

DELETE FROM `topology` WHERE `topology_parent` = '307' OR `topology_page` = '307';
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_dashboard', NULL , '307', NULL , NULL , '50', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_dashboard', NULL, 3, 307, 3, 1, '', NULL, '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_dashboardHost', './img/icones/16x16/text_rich_marked.gif', 307, 30701, 10, 50, './include/reporting/dashboard/viewHostLog.php', NULL, '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_dashboardService', './img/icones/16x16/text_rich_marked.gif', 307, 30702, 20, 50, './include/reporting/dashboard/viewServicesLog.php', NULL, '0', '0', '0', NULL , NULL , NULL);

-- 22/03/2007


-- About

DELETE FROM `topology` WHERE `topology_page` = '506' OR `topology_parent` = '506'
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_about', NULL, 506, NULL, NULL, 80, NULL, NULL, NULL, NULL, '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_about', NULL, 5, 506, 10, 1, './include/options/about/about.php', NULL, NULL, NULL, '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_about', './img/icones/16x16/earth2.gif', 506, 50601, 10, 80, './include/options/about/about.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_web', './img/icones/16x16/earth2.gif', 506, 50602, 20, 80, 'http://www.oreon-project.org', NULL, '1', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_forum', './img/icones/16x16/book_yellow.gif', 506, 50602, 30, 80, 'http://forum.oreon-project.org', NULL, '1', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_wiki', './img/icones/16x16/book_green.gif', 506, 50604, 40, 80, 'http://wiki.oreon-project.org', NULL, '1', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_bug', './img/icones/16x16/trafficlight_on.gif', 506, 50605, 50, 80, 'http://bugs.oreon-project.org', NULL, '1', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_donate', './img/icones/16x16/flower_white.gif', 506, 50606, 60, 80, 'http://sourceforge.net/donate/index.php?group_id=140316', NULL, '1', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_pro', './img/icones/16x16/cd_gold.gif', 506, 50607, 70, 80, 'http://www.oreon-services.com', NULL, '1', '0', '1');

-- Finish About

-- Monitoring Services

DELETE FROM topology WHERE topology_page LIKE '202%';

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_service', NULL, 2, 202, 10, 1, './include/monitoring/status/monitoringService.php', '&o=svcpb', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'mon_svc_all', './img/icones/16x16/row.gif', 202, 20201, 20, 7, './include/monitoring/status/monitoringService.php', '&o=svc', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_services_problems', './img/icones/16x16/row_delete.gif', 202, 20202, 10, 7, './include/monitoring/status/monitoringService.php', '&o=svcpb', NULL, NULL, '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_grid', './img/icones/16x16/services_grid.gif', 202, 20203, 30, 8, './include/monitoring/status/monitoringService.php', '&o=svcgrid', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_resume', './img/icones/16x16/table_view.gif', 202, 20204, 40, 8, './include/monitoring/status/monitoringService.php', '&o=svcOV', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_summary', './img/icones/16x16/column.gif', 202, 20205, 50, 8, './include/monitoring/status/monitoringService.php', '&o=svcSum', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_scheduling', './img/icones/16x16/stopwatch.gif', 202, 20207, 60, 33, './include/monitoring/status/monitoringService.php', '&o=svcSch', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_meta_service', './img/icones/16x16/calculator.gif', 202, 20206, 61, 11, './include/monitoring/status/monitoringService.php', '&o=meta', '0', '0', '1');

-- Ajoute sous menu probleme

INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_svc_warning', NULL , '20202', '2020201', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svc_warning', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_svc_critical', NULL , '20202', '2020202', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svc_critical', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_svc_unknown', NULL , '20202', '2020203', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svc_unknown', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'mon_svc_ok', NULL , '20201', '2020101', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svc_ok', '0', '0', '1', NULL , NULL , NULL);

-- Ajoute menu HG
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_grid', './img/icones/16x16/services_grid.gif', 202, 20208, 30, 9, './include/monitoring/status/monitoringService.php', '&o=svcgridHG', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_resume', './img/icones/16x16/table_view.gif', 202, 20209, 40, 9, './include/monitoring/status/monitoringService.php', '&o=svcOVHG', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_summary', './img/icones/16x16/column.gif', 202, 20210, 50, 9, './include/monitoring/status/monitoringService.php', '&o=svcSumHG', '0', '0', '1');
-- Ajoute menu SG
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_grid', './img/icones/16x16/services_grid.gif', 202, 20211, 30, 10, './include/monitoring/status/monitoringService.php', '&o=svcgridSG', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_resume', './img/icones/16x16/table_view.gif', 202, 20212, 30, 10, './include/monitoring/status/monitoringService.php', '&o=svcOVSG', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_status_summary', './img/icones/16x16/column.gif', 202, 20213, 30, 10, './include/monitoring/status/monitoringService.php', '&o=svcSumSG', '0', '0', '1');

-- Ajoute menu 
-- Grid
-- Grille de status 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES ('' , 'mon_problems', NULL , '20203', '2020301', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgrid&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES ('' , 'mon_acknowloedge', NULL , '20203', '2020302', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgrid&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES ('' , 'mon_not_acknowloedge', NULL , '20203', '2020303', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgrid&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de resume
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20204', '2020401', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOV&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20204', '2020402', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOV&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20204', '2020403', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOV&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de sommaire 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20205', '2020501', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSum&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20205', '2020502', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSum&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20205', '2020502', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSum&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);


-- Grid by HG
-- Grille de status 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20208', '2020801', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridHG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20208', '2020802', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridHG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20208', '2020803', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridHG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de resume
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20209', '2020901', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVHG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20209', '2020902', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVHG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20209', '2020903', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVHG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de sommaire 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20210', '2021001', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumHG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20210', '2021002', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumHG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20210', '2021002', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumHG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grid by SG
-- Grille de status 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20211', '2021101', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridSG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20211', '2021102', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridSG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20211', '2021103', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcgridSG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de resume 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20212', '2021201', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVSG&problem=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20212', '2021202', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVSG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20212', '2021203', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcOVSG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);

-- Grille de sommaire 
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_problems', NULL , '20213', '2021301', '10', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumSG&problem=1', '0', '0','1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_acknowloedge', NULL , '20213', '2021302', '20', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumSG&acknowledge=1', '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` )VALUES ('' , 'mon_not_acknowloedge', NULL , '20213', '2021302', '30', NULL , './include/monitoring/status/monitoringService.php', '&o=svcSumSG&acknowledge=0', '0', '0', '1', NULL , NULL , NULL);




