-- 17/04/2007

ALTER TABLE `traps` ADD `traps_status` ENUM( '-1', '0', '1', '2', '3' ) NULL DEFAULT NULL ;
ALTER TABLE `traps` ADD `manufacturer_id` INT( 11 ) NOT NULL ;
ALTER TABLE `traps` CHANGE `traps_comments` `traps_comments` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;

-- 24/04/2007

ALTER TABLE `inventory_manufacturer` ADD `description` TEXT NULL ;
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES (NULL, 'm_mnftr', './img/icones/16x16/factory.gif', 602, 60207, 60, 2, './include/configuration/configObject/traps-manufacturer/mnftr.php', NULL, '0', '0', '1', NULL, NULL, NULL);

-- 25/04/2007

INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick` ) VALUES (NULL , 'm_mibs', './img/icones/16x16/component_add.gif', '602', '60208', '70', '2', './include/configuration/configObject/mibs/mibs.php', NULL , '0', '0', '1', NULL , NULL , NULL);

-- 30/04/2007
ALTER TABLE `general_opt` DROP `perfparse_installed`;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40202 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40201 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40203 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40208 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` IS NULL AND `topology`.`topology_parent` = 402 AND `topology`.`topology_group` = 41 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` IS NULL AND `topology`.`topology_parent` = 402 AND `topology`.`topology_group` = 42 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 60708 LIMIT 1;

UPDATE `oreon_informations` SET `value` = '1.4.1' WHERE CONVERT( `key` USING utf8 ) = 'version' LIMIT 1 ;

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
ALTER TABLE `general_opt`  DROP `snmp_trapd_used`,   DROP `snmp_trapd_path_daemon`;