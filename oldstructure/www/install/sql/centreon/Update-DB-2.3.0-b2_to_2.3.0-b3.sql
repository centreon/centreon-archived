
UPDATE topology SET topology_name = 'Monitoring' WHERE topology_page = '5010102' AND topology_name = 'Nagios';

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'NDOutils', NULL, 609, NULL, NULL, 10, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
UPDATE `topology` SET `topology_group` = '10' WHERE `topology_parent` = 609 AND topology_name = 'ndo2db.cfg' LIMIT 1 ;
UPDATE `topology` SET `topology_group` = '10' WHERE `topology_parent` = 609 AND topology_name = 'ndomod.cfg' LIMIT 1 ;

INSERT INTO `options` (`key`, `value`) VALUES ('ldap_contact_tmpl', '0');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_search_timeout', '60');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_search_limit', '60');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_auto_import', '0');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_last_acl_update', '0');

UPDATE `informations` SET `value` = '2.3.0-b3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-b2' LIMIT 1;
 