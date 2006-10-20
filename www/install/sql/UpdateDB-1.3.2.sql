-- 13 08 06
ALTER TABLE  `general_opt` ADD `debug_path` VARCHAR( 255 ) NULL AFTER `ldap_auth_enable`;
ALTER TABLE  `general_opt` ADD `debug_auth` enum('0','1') default NULL AFTER `debug_path`;
ALTER TABLE  `general_opt` ADD `debug_nagios_import` enum('0','1') default NULL AFTER `debug_auth`;
ALTER TABLE  `general_opt` ADD `debug_rrdtool` enum('0','1') default NULL AFTER `debug_nagios_import`;

-- 15 09 06
ALTER TABLE  `general_opt` ADD `debug_ldap_import` enum('0','1') default NULL AFTER `debug_rrdtool`;
ALTER TABLE  `general_opt` ADD `debug_inventory` enum('0','1') default NULL AFTER `debug_ldap_import`;
--

-- 01 09 2006
ALTER TABLE `hostgroup` ADD `hg_snmp_community` VARCHAR( 255 ) NULL AFTER `hg_alias` , ADD `hg_snmp_version` VARCHAR( 255 ) NULL AFTER `hg_snmp_community` ;

-- 01 09 2006
ALTER TABLE `general_opt` ADD `AjaxTimeReloadMonitoring` INT NOT NULL DEFAULT '15' AFTER `maxViewConfiguration`;
ALTER TABLE `general_opt` ADD `AjaxTimeReloadStatistic` INT NOT NULL DEFAULT '15' AFTER `AjaxTimeReloadMonitoring` ;

ALTER TABLE `general_opt` ADD `AjaxFirstTimeReloadMonitoring` INT NOT NULL DEFAULT '15' AFTER `AjaxTimeReloadStatistic`;
ALTER TABLE `general_opt` ADD `AjaxFirstTimeReloadStatistic` INT NOT NULL DEFAULT '1' AFTER `AjaxFirstTimeReloadMonitoring`;

ALTER TABLE  `general_opt` ADD `ldap_search_user` varchar(254) default NULL AFTER `ldap_ssl` ;
ALTER TABLE  `general_opt` ADD `ldap_search_user_pwd` varchar(254) default NULL AFTER `ldap_search_user`;
ALTER TABLE  `general_opt` ADD `ldap_search_filter` varchar(254) default NULL AFTER `ldap_search_user_pwd`;
ALTER TABLE  `general_opt` ADD `ldap_search_timeout` varchar(5) default '60' AFTER `ldap_search_filter`;
ALTER TABLE  `general_opt` ADD `ldap_search_limit` varchar(5) default '60' AFTER `ldap_search_timeout`;

--
-- Structure de la table `topology_JS`
--

CREATE TABLE `topology_JS` (
  `id_t_js` int(11) NOT NULL auto_increment,
  `id_page` int(11) default NULL,
  `o` varchar(12) default NULL,
  `PathName_js` text,
  `Init` text,
  PRIMARY KEY  (`id_t_js`),
  KEY `id_page` (`id_page`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contraintes pour la table `topology_JS`
--

ALTER TABLE `topology` ADD INDEX ( `topology_page` );

ALTER TABLE `topology_JS`
  ADD CONSTRAINT `topology_JS_ibfk_1` FOREIGN KEY (`id_page`) REFERENCES `topology` (`topology_page`) ON DELETE CASCADE;


--
-- Contenu de la table `topology_JS`
--


INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 3, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 307, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 2, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 20201, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 20202, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 202, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 6, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 601, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 601, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 6, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 6, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 601, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 602, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 602, 'c', './include/common/javascript/changetab.js', 'initChangeTab()');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60201, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60201, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60203, 'w', './include/common/javascript/autocomplete-3-2.js  ', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60203, 'c', './include/common/javascript/autocomplete-3-2.js  ', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60201, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60203, 'a', './include/common/javascript/autocomplete-3-2.js    	', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 604, 'a', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 604, 'c', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 604, 'w', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60401, 'c', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60401, 'w', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60401, 'a', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60402, 'c', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60402, 'w', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60402, 'a', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60403, 'c', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60403, 'w', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60403, 'a', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60404, 'c', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60404, 'w', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60404, 'a', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60503, 'a', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60503, 'w', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60503, 'c', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 606, 'a', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 606, 'c', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 606, 'w', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60601, 'a', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60601, 'c', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60601, 'w', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60602, 'a', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60602, 'c', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60602, 'w', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60602, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60602, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 602, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 602, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60201, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60201, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 50101, NULL, './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 502, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 502, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 502, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40204, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40204, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40204, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40205, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40205, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40205, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 20302, NULL, './include/common/javascript/changetab.js        ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 20303, NULL, './include/common/javascript/changetab.js        ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 606, 'c', './include/common/javascript/autocomplete-3-2.js  ', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 606, 'a', './include/common/javascript/autocomplete-3-2.js ', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 601, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60405, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60405, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 3, NULL, './include/common/javascript/ajaxReporting.js', 'initTimeline');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 307, NULL, './include/common/javascript/ajaxReporting.js', 'initTimeline');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 602, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 6, 'a', './include/common/javascript/changetab.js  	', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', '203', NULL, './include/common/javascript/changetab.js', 'initChangeTab');

-- 14 / 09 / 2006

DELETE FROM `topology` WHERE `topology_page` = '2020101';

-- 06/10/2006

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_opt_conf', './img/icones/16x16/nagios.gif', 50101, 5010101, 10, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=general', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'menu_nagios', './img/icones/16x16/nagios.gif', 50101, 5010102, 20, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=nagios', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'menu_colors', './img/icones/16x16/colors.gif', 50101, 5010103, 30, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=colors', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'menu_snmp', './img/icones/16x16/nagios.gif', 50101, 5010104, 40, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=snmp', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'menu_ldap', './img/icones/16x16/book_yellow.gif', 50101, 5010105, 50, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=ldap', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'menu_rrdtool', './img/icones/16x16/column-chart.gif', 50101, 5010106, 60, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=rrdtool', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'menu_debug', './img/icones/16x16/nagios.gif', 50101, 5010107, 70, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=debug', '0', '0', '1');


-- changement de topology

UPDATE `topology` SET `topology_name` = 'm_general',
`topology_icone` = NULL ,
`topology_parent` = '5',
`topology_order` = '10',
`topology_group` = '1',
`topology_url` = './include/options/oreon/myAccount/formMyAccount.php',
`topology_url_opt` = '&o=c',
`topology_popup` = '0',
`topology_modules` = '0',
`topology_show` = '1' WHERE `topology_page` = 501 LIMIT 1 ;


ALTER TABLE `servicegroup_relation` ADD `host_host_id` INT NULL AFTER `sgr_id` ;
ALTER TABLE `servicegroup_relation` ADD `hostgroup_hg_id` INT NULL AFTER `host_host_id` ;

ALTER TABLE `servicegroup_relation` ADD INDEX ( `host_host_id` ); 
ALTER TABLE `servicegroup_relation` ADD INDEX ( `hostgroup_hg_id` );

ALTER TABLE `servicegroup_relation`
  ADD CONSTRAINT `servicegroup_relation_ibfk_7` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `servicegroup_relation_ibfk_8` FOREIGN KEY (`hostgroup_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE;

DELETE FROM topology WHERE topology_parent = '7';

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_id_serv', NULL, 7, 701, 10, 1, './modules/inventory/inventory.php', '&o=s', '0', '1', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_id_serv', NULL, 701, 70101, 10, 1, './modules/inventory/inventory.php', '&o=s', '0', '1', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_id_network', NULL, 701, 70102, 20, 1, './modules/inventory/inventory.php', '&o=n', '0', '1', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_idUpdate', NULL, 701, 70103, 40, 1, './modules/inventory/inventory.php', '&o=u', NULL, NULL, '1');


INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_commands', NULL, 6, 608, 35, NULL, './include/configuration/configObject/command/command.php', '&type=0', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_commandCheck', './img/icones/16x16/exchange.gif', 608, 60801, 10, NULL, './include/configuration/configObject/command/command.php', '&type=2', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_commandNotif', './img/icones/16x16/exchange.gif', 608, 60802, 20, NULL, './include/configuration/configObject/command/command.php', '&type=1', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_commandMisc', './img/icones/16x16/exchange.gif', 608, 60803, 30, NULL, './include/configuration/configObject/command/command.php', '&type=3', '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_plugins', './img/icones/16x16/window_gear.gif', 608, 60804, 40, NULL, './include/configuration/configPlugins/Plugins.php', '', '0', '0', '1');

-- beta 2--


UPDATE `topology` SET `topology_order` = '20' WHERE `topology_page` = '20201' AND `topology_url_opt` = '&o=svc' LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '10' WHERE `topology_page` = '20201' AND `topology_url_opt` = '&o=svcpb' LIMIT 1 ;

DELETE FROM `topology` WHERE `topology_page` = 3 OR  `topology_page` = 307 OR  `topology_page` = 30701 OR  `topology_page` = 30702 OR `topology_parent` = 3;

INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES 
( 'm_reporting', NULL, NULL, 3, 30, 1, './include/reporting/dashboard/viewHostLog.php', NULL, '0', '0', '1'),
( 'm_dashboard', NULL, 3, 307, 3, 1, './include/reporting/dashboard/viewHostLog.php', NULL, '0', '0', '1'),
( 'm_dashboardHost', './img/icones/16x16/outbox.gif', 307, 30701, 10, 1, './include/reporting/dashboard/viewHostLog.php', NULL, '0', '0', '1'),
( 'm_dashboardService', NULL, 307, 30702, 20, 1, './include/reporting/dashboard/viewServicesLog.php', NULL, '0', '0', '0');

DELETE FROM `log_archive_file_name`;
DELETE FROM `log_archive_host`;
DELETE FROM `log_archive_service`;

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);
