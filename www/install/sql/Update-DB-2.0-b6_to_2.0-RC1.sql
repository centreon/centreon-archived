
DELETE FROM topology WHERE topology_page = 50103;

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Modules', NULL, 5, 507, 10, 1, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Modules', NULL, 507, NULL, NULL, 1, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Setup', './img/icones/16x16/press.gif', 507, 50701, 10, 1, './include/options/oreon/modules/modules.php', NULL, NULL, NULL, '1', NULL, NULL, NULL);


UPDATE `topology` SET `topology_order` = '12' WHERE `topology`.`topology_page` = 502 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '15' WHERE `topology`.`topology_page` = 503 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '14' WHERE `topology`.`topology_page` = 504 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '15' WHERE `topology`.`topology_page` = 505 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '16' WHERE `topology`.`topology_page` = 506 LIMIT 1 ;

UPDATE `topology` SET `topology_icone` = './img/icones/16x16/text_code.gif' WHERE `topology`.`topology_page` = 20301 LIMIT 1 ;

CREATE TABLE IF NOT EXISTS `contact_host_relation` (
  `chr_id` int(11) NOT NULL auto_increment,
  `host_host_id` int(11) default NULL,
  `contact_id` int(11) default NULL,
  PRIMARY KEY  (`chr_id`),
  KEY `host_index` (`host_host_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contact_service_relation` (
  `csr_id` int(11) NOT NULL auto_increment,
  `service_service_id` int(11) default NULL,
  `contact_id` int(11) default NULL,
  PRIMARY KEY  (`csr_id`),
  KEY `service_index` (`service_service_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `contact_host_relation` ADD FOREIGN KEY ( `host_host_id` ) REFERENCES `host` (`host_id`) ON DELETE CASCADE ;
ALTER TABLE `contact_host_relation` ADD FOREIGN KEY ( `contact_id` ) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE ;

ALTER TABLE `contact_service_relation` ADD FOREIGN KEY ( `service_service_id` ) REFERENCES `service` (`service_id`) ON DELETE CASCADE ;
ALTER TABLE `contact_service_relation` ADD FOREIGN KEY ( `contact_id` ) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE ;

UPDATE `topology` SET `topology_name` = 'Acknowledged' WHERE `topology_name` = 'mon_acknowloedge';
UPDATE `topology` SET `topology_name` = 'Not Acknowledged' WHERE `topology_name` = 'mon_not_acknowloedge';
UPDATE `topology` SET `topology_name` = 'Problems' WHERE `topology_name` = 'mon_problems';

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES (NULL, 30703, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES (NULL, 30704, NULL, './include/common/javascript/datePicker.js', '');

UPDATE `topology` SET `topology_name` = 'Overview' WHERE `topology`.`topology_name` = 'Resume' ;
UPDATE `topology` SET `topology_name` = 'Unknown' WHERE `topology`.`topology_name` = 'Unknonwn' ;

UPDATE `informations` SET `value` = '2.0-RC1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.0-b6' LIMIT 1;

