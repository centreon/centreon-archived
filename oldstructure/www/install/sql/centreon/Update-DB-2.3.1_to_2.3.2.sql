ALTER TABLE `cfg_nagios_broker_module` ADD CONSTRAINT `fk_nagios_cfg` FOREIGN KEY (`cfg_nagios_id`) REFERENCES `cfg_nagios` (`nagios_id`) ON DELETE CASCADE;

ALTER TABLE `contact` CHANGE `contact_ldap_dn` `contact_ldap_dn` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

UPDATE `informations` SET `value` = '2.3.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.1' LIMIT 1;