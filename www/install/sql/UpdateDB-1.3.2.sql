-- 13 08 06
ALTER TABLE  `general_opt` ADD `debug_path` VARCHAR( 255 ) NULL AFTER `ldap_auth_enable`;
ALTER TABLE  `general_opt` ADD `debug_auth` enum('0','1') default NULL AFTER `debug_path`;
ALTER TABLE  `general_opt` ADD `debug_nagios_import` enum('0','1') default NULL AFTER `debug_auth`;
ALTER TABLE  `general_opt` ADD `debug_rrdtool` enum('0','1') default NULL AFTER `debug_nagios_import`;

-- 01 09 2006
ALTER TABLE `hostgroup` ADD `hg_snmp_community` VARCHAR( 255 ) NULL AFTER `hg_alias` , ADD `hg_snmp_version` VARCHAR( 255 ) NULL AFTER `hg_snmp_community` ;