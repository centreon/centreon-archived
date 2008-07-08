

ALTER TABLE `host` ADD `display_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_alias` ;
ALTER TABLE `host` ADD `initial_state` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_checks_enabled` ;
ALTER TABLE `host` ADD `flap_detection_options` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_flap_detection_enabled` ;

ALTER TABLE `service` CHANGE `service_activate` `service_activate` ENUM( '0', '1', '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' 
ALTER TABLE `host` CHANGE `host_activate` `host_activate` ENUM( '0', '1', '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1'

ALTER TABLE `nagios_server` CHANGE `nagiosstats_bin` `nagiostats_bin` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL

-- 
-- Structure de la table `on_demand_macro_host`
-- 

CREATE TABLE IF NOT EXISTS `on_demand_macro_host` (
  `host_macro_id` int(11) NOT NULL auto_increment,
  `host_macro_name` varchar(255) NOT NULL,
  `host_macro_value` varchar(255) NOT NULL,
  `host_host_id` varchar(11) NOT NULL,
  PRIMARY KEY  (`host_macro_id`),
  KEY `host_host_id` (`host_host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(318, 60101, 'a', './include/common/javascript/commandGetArgs/cmdGetExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(319, 60101, 'c', './include/common/javascript/commandGetArgs/cmdGetExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(320, 60101, 'mc', './include/common/javascript/commandGetArgs/cmdGetExample.js', NULL);
