UPDATE `informations` SET `value` = '2.0-RC3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.0-RC2' LIMIT 1;

CREATE TABLE IF NOT EXISTS `options` (
	`key` VARCHAR( 255 ) NULL ,
	`value` VARCHAR( 255 ) NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci ;


UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20203;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020301;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020302;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020303;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20204;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020401;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020402;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020403;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20205;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020501;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020502;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020503;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20208;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020801;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020802;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020803;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20209;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020901;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020902;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2020903;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20210;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2021001;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2021002;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20211;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2021101;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2021102;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2021103;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20212;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2021201;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 2021202;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 201;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20102;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20103;
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/ajaxMonitoring.js' WHERE id_page = 20104;

UPDATE `topology` SET `topology_url_opt` = NULL WHERE `topology`.`topology_page` = 203 LIMIT 1 ;

UPDATE `topology` SET `topology_show` = '0' WHERE `topology`.`topology_page` = 50606 LIMIT 1 ;
UPDATE `topology` SET `topology_url` = 'http://www.merethis.com' WHERE `topology`.`topology_page` = 50607 LIMIT 1 ;

DELETE FROM `topology` WHERE `topology_page` = 40211 LIMIT 1;
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'All Graphs', './img/icones/16x16/column-chart.gif', 402, 40201, 10, 40, './include/views/graphs/graphs.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES( NULL, 40201, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES( NULL, 40201, NULL, './include/common/javascript/codebase/dhtmlxtree.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES( NULL, 40201, NULL, './include/common/javascript/codebase/dhtmlxcommon.js', NULL);

UPDATE `topology` SET `topology_url` = './include/options/centStorage/manageData.php' WHERE `topology`.`topology_page` = 5010602 LIMIT 1 ;

DELETE FROM `topology` WHERE `topology_page` = 40210 LIMIT 1;

ALTER TABLE `contact` ADD `contact_acl_group_list` VARCHAR( 255 ) NULL ;
ALTER TABLE `contact` ADD `contact_autologin_key` VARCHAR( 255 ) NULL ;

ALTER TABLE `host` ADD `host_first_notification_delay` INT NULL AFTER `host_notifications_enabled` ;
ALTER TABLE `service` ADD `service_first_notification_delay` INT NULL AFTER `service_notifications_enabled` ;

DELETE FROM `topology` WHERE `topology_page` = '7';
