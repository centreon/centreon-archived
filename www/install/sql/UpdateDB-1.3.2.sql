-- 13 08 06
ALTER TABLE  `general_opt` ADD `debug_path` VARCHAR( 255 ) NULL AFTER `ldap_auth_enable`;
ALTER TABLE  `general_opt` ADD `debug_auth` enum('0','1') default NULL AFTER `debug_path`;
ALTER TABLE  `general_opt` ADD `debug_nagios_import` enum('0','1') default NULL AFTER `debug_auth`;
ALTER TABLE  `general_opt` ADD `debug_rrdtool` enum('0','1') default NULL AFTER `debug_nagios_import`;

-- 01 09 2006
ALTER TABLE `hostgroup` ADD `hg_snmp_community` VARCHAR( 255 ) NULL AFTER `hg_alias` , ADD `hg_snmp_version` VARCHAR( 255 ) NULL AFTER `hg_snmp_community` ;

-- 01 09 2006
ALTER TABLE `general_opt` ADD `AjaxTimeReloadMonitoring` INT NOT NULL DEFAULT '15' AFTER `maxViewConfiguration`;
ALTER TABLE `general_opt` ADD `AjaxTimeReloadStatistic` INT NOT NULL DEFAULT '15' AFTER `AjaxTimeReloadMonitoring` ;


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
ALTER TABLE `topology_JS`
  ADD CONSTRAINT `topology_JS_ibfk_1` FOREIGN KEY (`id_page`) REFERENCES `topology` (`topology_page`) ON DELETE CASCADE;

ALTER TABLE `topology` ADD INDEX ( `topology_page` );

--
-- Contenu de la table `topology_JS`
--

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES (1, 3, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', NULL),
(2, 307, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', NULL),
(3, 2, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM'),
(4, 20201, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM'),
(5, 20202, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM'),
(6, 202, NULL, './include/common/javascript/ajaxMonitoring.js', 'initM'),
(7, 6, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(8, 601, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(9, 601, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(10, 60101, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(11, 60101, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(12, 6, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(13, 6, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')'),
(14, 601, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')'),
(15, 60101, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')'),
(16, 602, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(17, 602, 'c', './include/common/javascript/changetab.js', 'initChangeTab()'),
(18, 60201, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(19, 60201, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(20, 60202, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(21, 60202, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(22, 60203, 'w', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')'),
(23, 60203, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')'),
(24, 60201, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(25, 60202, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(26, 60203, 'a', './include/common/javascript/autocomplete-3-2.js    	', 'initAutoComplete(''Form'',''city_name'',''sub'')'),
(27, 604, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(28, 604, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(29, 604, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(30, 60401, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(31, 60401, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(32, 60401, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(33, 60402, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(34, 60402, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(35, 60402, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(36, 60403, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(37, 60403, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(38, 60403, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(39, 60404, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(40, 60404, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(41, 60404, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(42, 60503, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(43, 60503, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(44, 60503, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(45, 606, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(46, 606, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(47, 606, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(48, 60601, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(49, 60601, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(50, 60601, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(51, 60602, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(52, 60602, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(53, 60602, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(54, 602, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL),
(55, 602, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL),
(56, 60201, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL),
(57, 60201, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL),
(58, 60202, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL),
(59, 60202, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL),
(60, 50101, NULL, './include/common/javascript/changetab.js', 'initChangeTab'),
(61, 502, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(62, 502, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(63, 502, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(64, 40204, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(65, 40204, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(66, 40204, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(67, 40205, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(68, 40205, 'w', './include/common/javascript/changetab.js', 'initChangeTab'),
(69, 40205, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(70, 20302, NULL, './include/common/javascript/changetab.js', 'initChangeTab'),
(71, 20303, NULL, './include/common/javascript/changetab.js', 'initChangeTab'),
(72, 606, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')'),
(73, 606, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')'),
(74, 601, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(75, 60405, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(76, 60405, 'c', './include/common/javascript/changetab.js', 'initChangeTab'),
(77, 3, NULL, './include/common/javascript/ajaxReporting.js', 'initTimeline'),
(78, 307, NULL, './include/common/javascript/ajaxReporting.js', 'initTimeline'),
(79, 602, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(81, 6, 'a', './include/common/javascript/changetab.js', 'initChangeTab'),
(82, 60101, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
