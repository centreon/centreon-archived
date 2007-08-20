-- 17/04/2007

ALTER TABLE `traps` ADD `traps_status` ENUM( '-1', '0', '1', '2', '3' ) NULL DEFAULT NULL ;
ALTER TABLE `traps` ADD `manufacturer_id` INT( 11 ) NOT NULL ;
ALTER TABLE `inventory_manufacturer` ADD `description` TEXT NULL ;
ALTER TABLE `traps` CHANGE `traps_comments` `traps_comments` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- 30/04/2007

ALTER TABLE `general_opt` DROP `perfparse_installed`;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40202 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40201 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40203 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40208 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` IS NULL AND `topology`.`topology_parent` = 402 AND `topology`.`topology_group` = 41 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` IS NULL AND `topology`.`topology_parent` = 402 AND `topology`.`topology_group` = 42 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 60708 LIMIT 1;

UPDATE `oreon_informations` SET `value` = '1.4.1-RC1' WHERE CONVERT( `key` USING utf8 ) = 'version' LIMIT 1 ;

-- 10/05/2007

ALTER TABLE `general_opt` ADD `patch_type_stable` enum('Y','N') default 'Y' AFTER `gmt`  ;
ALTER TABLE `general_opt` ADD `patch_type_RC` enum('Y','N') default 'N' AFTER `patch_type_stable`;
ALTER TABLE `general_opt` ADD `patch_type_patch` enum('Y','N') default 'N'  AFTER `patch_type_RC`;
ALTER TABLE `general_opt` ADD `patch_type_secu` enum('Y','N') default 'Y'  AFTER `patch_type_patch`;
ALTER TABLE `general_opt` ADD `patch_type_beta` enum('Y','N') default 'N' AFTER `patch_type_secu`;
ALTER TABLE `general_opt` ADD `patch_url_service` varchar(255) default NULL AFTER `patch_type_beta`;
ALTER TABLE `general_opt` ADD `patch_url_download` varchar(255) default NULL AFTER `patch_url_service`;
ALTER TABLE `general_opt` ADD `patch_path_download` varchar(255) default NULL AFTER `patch_url_download`;

UPDATE `general_opt` SET patch_type_stable = 'Y',  `patch_type_RC` = 'Y', `patch_type_patch` = 'Y',  `patch_type_secu`= 'Y',  `patch_type_beta` = 'Y',  `patch_url_service`= 'http://update.oreon-project.org/version.php', `patch_url_download` = 'http://update.oreon-project.org/patch/', `patch_path_download` = '/tmp/';

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_patch', './img/icones/16x16/download.gif', 501, 50105, 11, 1, './include/options/oreon/upGrade/checkVersion.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_checkVersion', '', 50105, 5010501, 1, 1, './include/options/oreon/upGrade/checkVersion.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_patchOptions', '', 50105, 5010502, 2, 1, './include/options/oreon/upGrade/patchOptions.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_preUpdate', '', 50105, 5010503, 3, 1, './include/options/oreon/upGrade/preUpdate.php', NULL, '0', '0', '0', NULL, NULL, NULL);

-- 15/05/2007

ALTER TABLE `general_opt`  DROP `snmp_trapd_used`,  DROP `snmp_trapd_path_daemon`;

-- 18/05/2007

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_service', NULL, 602, NULL, NULL, 1, NULL, NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_meta_service', NULL, 602, NULL, NULL, 2, NULL, NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_traps_command', NULL, 602, NULL, NULL, 3, NULL, NULL, '0', '0', '1');

UPDATE `topology` SET `topology_group` = '2' WHERE `topology`.`topology_page` = 60204 LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '3' WHERE `topology`.`topology_page` = 60205 LIMIT 1 ;

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_mnftr', './img/icones/16x16/factory.gif', '602', '60207', '60', '3', './include/configuration/configObject/traps-manufacturer/mnftr.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_mibs', './img/icones/16x16/component_add.gif', '602', '60208', '70', '3', './include/configuration/configObject/traps-mibs/mibs.php', NULL , '0', '0', '1');

-- 04/06/2007

ALTER TABLE `cfg_nagios` ADD `event_broker_options` VARCHAR( 255 ) NULL ;

-- 19/06/2007

CREATE TABLE `traps_vendor` (`id` int(11) NOT NULL auto_increment, `name` varchar(254) default NULL, `alias` varchar(254) default NULL, `description` text, PRIMARY KEY  (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;
INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES (1, 'cisco', 'Cisco Networks', NULL), (2, 'hp', 'HP Networks', NULL), (3, '3com', '3Com', NULL), (4, 'ciscolinksys', 'Cisco-Linksys', NULL), (6, 'dell', 'Dell', NULL), (7, 'Generic', 'Generic', 'References Generic Traps');

-- 25/06/2007

ALTER TABLE `traps` CHANGE `manufacturer_id` `manufacturer_id` INT( 11 ) NULL ;
UPDATE `traps` SET manufacturer_id = NULL WHERE manufacturer_id = '0';
ALTER TABLE `traps`  ADD CONSTRAINT `traps_ibfk_1` FOREIGN KEY ( `manufacturer_id` ) REFERENCES `traps_vendor` ( `id` ) ON DELETE CASCADE ;

-- 27/06/2007

ALTER TABLE `general_opt` ADD `perl_library_path` VARCHAR( 255 ) NOT NULL , ADD `snmpttconvertmib_path_bin` VARCHAR( 255 ) NOT NULL ;

-- 9/07/2007

ALTER TABLE `traps` DROP `traps_handler` ;

-- 10/07/2007
INSERT INTO `command` VALUES ('', 'process-service-perfdata-ods', '$USER1$#S#process-service-perfdata  &quot;$LASTSERVICECHECK$&quot; &quot;$HOSTNAME$&quot; &quot;$SERVICEDESC$&quot; &quot;$SERVICEOUTPUT$&quot; &quot;$SERVICESTATE$&quot; &quot;$SERVICEPERFDATA$&quot;', '', 3);


-- 19/07/2007
DELETE FROM topology WHERE topology_page = '401';
DELETE FROM topology WHERE topology_page = '40101';
DELETE FROM topology WHERE topology_page = '40102';

TRUNCATE TABLE `view_city`;
TRUNCATE TABLE `view_country`;
ALTER TABLE `view_city` DROP FOREIGN KEY `view_city_ibfk_1`;
ALTER TABLE `extended_host_information`  DROP FOREIGN KEY `extended_host_information_ibfk_2`;
ALTER TABLE `extended_host_information`  DROP FOREIGN KEY `extended_host_information_ibfk_3`;
ALTER TABLE `hostgroup`  DROP FOREIGN KEY `hostgroup_ibfk_1`;
ALTER TABLE `hostgroup`  DROP FOREIGN KEY `hostgroup_ibfk_2`;
ALTER TABLE `servicegroup`  DROP FOREIGN KEY `servicegroup_ibfk_1`;
ALTER TABLE `servicegroup`  DROP FOREIGN KEY `servicegroup_ibfk_2`;

ALTER TABLE `extended_host_information` DROP `country_id` , DROP `city_id` ;
ALTER TABLE `hostgroup` DROP `country_id` ,DROP `city_id` ;
ALTER TABLE `servicegroup` DROP `country_id` ,DROP `city_id` ;

DROP TABLE `view_city`;
DROP TABLE `view_country`;
DROP TABLE `view_map`;

DELETE FROM `topology_JS` WHERE `id_page` = 6;
DELETE FROM `topology_JS` WHERE `id_page` =  601;
DELETE FROM `topology_JS` WHERE `id_page` =  60101;
DELETE FROM `topology_JS` WHERE `id_page` =  60102;
DELETE FROM `topology_JS` WHERE `id_page` =  60103;

ALTER TABLE `giv_graphs_template`
  DROP `title`,
  DROP `img_format`,
  DROP `period`,
  DROP `step`,
  DROP `default_tpl2`;
  
ALTER TABLE `giv_components_template`
  DROP `ds_legend`,
  DROP `default_tpl2`;
  
ALTER TABLE `giv_graphs_template` ADD `split_component` ENUM("0", "1") NOT NULL DEFAULT '0' AFTER `stacked` ;
ALTER TABLE `giv_graphs_template` ADD `base` INT NULL DEFAULT '1000' AFTER `height` ;

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'menu_ODS', './img/icones/16x16/chart.gif', 501, 50106, 60, 1, './include/options/ods/manageData.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'menu_ODS_manage', './img/icones/16x16/data_down.gif', 50106, 5010602, 20, 1, './include/options/ods/manageData.php', NULL, '0', '0', '1');

-- 23/07/2007
ALTER TABLE `giv_graphs`  DROP FOREIGN KEY `giv_graphs_ibfk_1`;
ALTER TABLE `giv_components`  DROP FOREIGN KEY `giv_components_ibfk_1`;
DROP TABLE `giv_graphs`;
DROP TABLE `giv_components`;

UPDATE `topology` SET `topology_icone` = './img/icones/16x16/about.gif' WHERE `topology_page` =50601 LIMIT 1;

UPDATE `oreon_informations` SET `value` = '1.4.1-RC1' WHERE CONVERT( `oreon_informations`.`key` USING utf8 ) = 'version' AND CONVERT( `oreon_informations`.`value` USING utf8 ) = '1.4' LIMIT 1 ;
UPDATE `oreon_informations` SET `value` = '1.4.1-RC2' WHERE CONVERT( `oreon_informations`.`key` USING utf8 ) = 'version' AND CONVERT( `oreon_informations`.`value` USING utf8 ) = '1.4.1-RC1' LIMIT 1 ;

-- 03/08/2007
UPDATE `oreon_informations` SET `value` = '1.4.1-RC3' WHERE CONVERT( `oreon_informations`.`key` USING utf8 ) = 'version' AND CONVERT( `oreon_informations`.`value` USING utf8 ) = '1.4.1-RC2' LIMIT 1 ;

INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 601, 'a',
'./include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 601, 'c',
'./include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 601, 'mc',
'./include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60103, 'a',
'./include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60103, 'c',
'./include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60103, 'mc',
'./include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 6, 'a',
'./include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 6, 'c',
'./include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 6, 'mc',
'./include/common/javascript/autoSelectCommandExample.js', NULL);

INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 6, 'a', './include/common/javascript/changetab.js',
'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 6, 'w', './include/common/javascript/changetab.js',
'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 6, 'c', './include/common/javascript/changetab.js',
'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 6, 'mc', './include/common/javascript/changetab.js',
'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 601, 'w',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 601, 'c',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 601, 'a',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 601, 'mc',
'./include/common/javascript/changetab.js', 'initChangeTab');

INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60101, 'c',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60101, 'w',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60101, 'a',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60101, 'mc',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60103, 'a',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60103, 'c',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60103, 'w',
'./include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` ,
`Init`) VALUES ('', 60103, 'mc',
'./include/common/javascript/changetab.js', 'initChangeTab');


