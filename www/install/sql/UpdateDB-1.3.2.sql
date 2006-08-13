-- 13 08 06
ALTER TABLE  `general_opt` ADD `debug_auth` enum('0','1') default NULL AFTER `ldap_auth_enable`;
ALTER TABLE  `general_opt` ADD `debug_nagios_import` enum('0','1') default NULL AFTER `debug_auth`;
