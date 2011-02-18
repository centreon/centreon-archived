
UPDATE topology SET topology_name = 'Monitoring' WHERE topology_id = '5010102' AND topology_name = 'Nagios';

INSERT INTO `options` (`key`, `value`) VALUES ('ldap_contact_tmpl', '0');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_search_timeout', '60');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_search_limit', '60');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_auto_import', '0');
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_last_acl_update', '0');

UPDATE `informations` SET `value` = '2.3.0-b3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-b2' LIMIT 1;
 