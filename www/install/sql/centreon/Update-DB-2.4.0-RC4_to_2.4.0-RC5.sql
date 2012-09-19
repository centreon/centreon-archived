--
-- Update meta infotmation about Centreon
--

UPDATE `informations` SET `value` = '2.4.0-RC5' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC4' LIMIT 1;

--
-- Adds new fields to `cfg_cgi`
--

ALTER TABLE `cfg_cgi` ADD `action_url_target` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER  `cgi_activate`;
ALTER TABLE `cfg_cgi` ADD `escape_html_tags` ENUM('0', '1', '2') NULL DEFAULT '2' AFTER `action_url_target`;
ALTER TABLE `cfg_cgi` ADD `lock_author_names` ENUM('0', '1', '2') NULL DEFAULT '2' AFTER `escape_html_tags`;
ALTER TABLE `cfg_cgi` ADD `notes_url_target` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lock_author_names`;

ALTER TABLE `hostgroup`
  DROP `hg_snmp_community`,
  DROP `hg_snmp_version`;
  
/*!40000 ALTER TABLE `topology` DISABLE KEYS */;
/*!40000 ALTER TABLE `topology_JS` DISABLE KEYS */;
SET FOREIGN_KEY_CHECKS = 0;
UPDATE `topology` SET `topology_page` = 617 WHERE `topology_page` = 611 AND `topology_name` = 'SNMP Traps' AND `topology_order` = 40;
UPDATE `topology` SET `topology_parent` = 617 WHERE `topology_parent` = 611 AND `topology_name` = 'SNMP Traps' AND `topology_order` = 40 AND `topology_page` IS NULL;
UPDATE `topology` SET `topology_parent` = 617, `topology_page` = 61701 WHERE `topology_parent` = 611 AND `topology_name` = 'SNMP Traps' AND `topology_order` = 10 AND `topology_page` = 61101;
UPDATE `topology` SET `topology_parent` = 617, `topology_page` = 61702 WHERE `topology_parent` = 611 AND `topology_name` = 'Manufacturer' AND `topology_order` = 20 AND `topology_page` = 61102;
UPDATE `topology` SET `topology_parent` = 617, `topology_page` = 61703 WHERE `topology_parent` = 611 AND `topology_name` = 'MIBs' AND `topology_order` = 30 AND `topology_page` = 61103;
UPDATE `topology` SET `topology_parent` = 617, `topology_page` = 61704, `topology_name` = 'Generate' WHERE `topology_parent` = 611 AND (`topology_name` = 'Generate' OR `topology_name` = 'SNMP traps') AND `topology_order` = 30 AND `topology_page` = 61104;

UPDATE `topology_JS` SET `id_page` = 61704 WHERE `id_page` = 61104 AND `o` IS NULL;
SET FOREIGN_KEY_CHECKS = 1;
/*!40000 ALTER TABLE `topology` ENABLE KEYS */;
/*!40000 ALTER TABLE `topology_JS` ENABLE KEYS */;