
--
-- Update Script for BETA 4 to BETA 5
--

ALTER TABLE `host` ADD `display_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_alias`;
ALTER TABLE `host` ADD `initial_state` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_checks_enabled`;
ALTER TABLE `host` ADD `flap_detection_options` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_flap_detection_enabled`;

ALTER TABLE `service` CHANGE `service_activate` `service_activate` ENUM( '0', '1', '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';
ALTER TABLE `host` CHANGE `host_activate` `host_activate` ENUM( '0', '1', '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';

ALTER TABLE `nagios_server` CHANGE `nagiosstats_bin` `nagiostats_bin` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

--
-- Structure de la table `host_template_relation`
--

CREATE TABLE `host_template_relation` (
  `host_host_id` int(11) NOT NULL default '0',
  `host_tpl_id` int(11) NOT NULL default '0',
  `order` int(11) default NULL,
  PRIMARY KEY  (`host_host_id`,`host_tpl_id`),
  KEY `host_tpl_id` (`host_tpl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contraintes pour la table `host_template_relation`
-- 

ALTER TABLE `host_template_relation`
  ADD CONSTRAINT `host_template_relation_ibfk_2` FOREIGN KEY (`host_tpl_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `host_template_relation_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE;

-- --------------------------------------------------------

-- 
-- Structure de la table `on_demand_macro_host`
-- 

CREATE TABLE IF NOT EXISTS `on_demand_macro_host` (
  `host_macro_id` int(11) NOT NULL auto_increment,
  `host_macro_name` varchar(255) NOT NULL,
  `host_macro_value` varchar(255) NOT NULL,
  `host_host_id` int(11) NOT NULL,
  PRIMARY KEY  (`host_macro_id`),
  KEY `host_host_id` (`host_host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

-- --------------------------------------------------------

-- 
-- Structure de la table `on_demand_macro_service`
--
CREATE TABLE `on_demand_macro_service` (
  `svc_macro_id` int(11) NOT NULL auto_increment,
  `svc_macro_name` varchar(255) NOT NULL,
  `svc_macro_value` varchar(255) NOT NULL,
  `svc_svc_id` int(11) NOT NULL,
  PRIMARY KEY  (`svc_macro_id`),
  KEY `svc_svc_id` (`svc_svc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

-- --------------------------------------------------------

-- 
-- Contraintes pour la table `on_demand_macro_service`
-- 
ALTER TABLE `on_demand_macro_service`
  ADD CONSTRAINT `on_demand_macro_service_ibfk_1` FOREIGN KEY (`svc_svc_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE;

-- --------------------------------------------------------

-- 
-- Contraintes pour la table `on_demand_macro_host`
-- 
ALTER TABLE `on_demand_macro_host`
  ADD CONSTRAINT `on_demand_macro_host_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE;

-- --------------------------------------------------------

-- 
-- Topology
-- 

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(318, 60101, 'a', './include/common/javascript/commandGetArgs/cmdGetExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(319, 60101, 'c', './include/common/javascript/commandGetArgs/cmdGetExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(320, 60101, 'mc', './include/common/javascript/commandGetArgs/cmdGetExample.js', NULL);

-- --------------------------------------------------------

-- 
-- Update Centreon version
-- 

UPDATE `informations` SET `value` = '2.0-b5' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.0-b4' LIMIT 1;

-- --------------------------------------------------------

-- --- END -----
