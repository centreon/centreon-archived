ALTER TABLE `topology_JS` ON UPDATE CASCADE;
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

INSERT INTO `topology_JS` (`id_page`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2021501, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM'),
INSERT INTO `topology_JS` (`id_page`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 2021502, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM'),
INSERT INTO `topology_JS` (`id_page`, `id_page`, `o`, ,PathName_js`, `Init`) VALUES(NULL, 2021503, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Warning', NULL, 20215, 2021501, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_warning', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Critical', NULL, 20215, 2021502, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_critical', NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Unknown', NULL, 20215, 2021503, 10, NULL, './include/monitoring/status/monitoringService.php', '&o=svc_unhandled_unknown', NULL, NULL, '1', NULL, NULL, NULL);

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`) VALUES (NULL, "Media", '6', '605', '60', '1');
UPDATE      `topology` SET `topology_parent` = '605', `topology_page` = '60501'  WHERE `topology_parent` = '501' AND  `topology_page` = '50102';
DELETE FROM `topology` WHERE `topology_parent` = '50102' AND `topology_page` = '5010201';
DELETE FROM `topology` WHERE `topology_parent` = '50102' AND `topology_page` = '5010202';

CREATE TABLE IF NOT EXISTS `acl_group_contactgroups_relations` (
  `agcgr_id` int(11) NOT NULL auto_increment,
  `cg_cg_id` int(11) default NULL,
  `acl_group_id` int(11) default NULL,
  PRIMARY KEY  (`agcgr_id`),
  KEY `cg_cg_id` (`cg_cg_id`),
  KEY `acl_group_id` (`acl_group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `acl_group_contactgroups_relations` ADD FOREIGN KEY ( `cg_cg_id` ) REFERENCES `centreon`.`contactgroup` (`cg_id`) ON DELETE CASCADE ;
ALTER TABLE `acl_group_contactgroups_relations` ADD FOREIGN KEY ( `acl_group_id` ) REFERENCES `centreon`.`acl_groups` (`acl_group_id`) ON DELETE CASCADE ;

CREATE TABLE IF NOT EXISTS `traps_matching_properties` (
  `tmo_id` int(11) NOT NULL AUTO_INCREMENT,
  `trap_id` int(11) DEFAULT NULL,
  `tmo_order` int(11) DEFAULT NULL,
  `tmo_regexp` varchar(255) DEFAULT NULL,
  `tmo_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`tmo_id`),
  KEY `trap_id` (`trap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 
ALTER TABLE `traps_matching_properties` ADD INDEX (`trap_id`);
ALTER TABLE `traps_matching_properties` ADD FOREIGN KEY (`trap_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE ;  
