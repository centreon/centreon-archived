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



