-- --------------------------------------------------------

-- 
-- Structure de la table `log_action`
-- 

CREATE TABLE IF NOT EXISTS `log_action` (
  `action_log_id` int(11) NOT NULL auto_increment,
  `action_log_date` int(11) NOT NULL,
  `object_type` varchar(255) NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_name` varchar(255) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `log_contact_id` int(11) NOT NULL,
  PRIMARY KEY  (`action_log_id`),
  KEY `log_contact_id` (`log_contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `log_action_modification`
-- 

CREATE TABLE IF NOT EXISTS `log_action_modification` (
  `modification_id` int(11) NOT NULL auto_increment,
  `field_name` varchar(255) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  `action_log_id` int(11) NOT NULL,
  PRIMARY KEY  (`modification_id`),
  KEY `action_log_id` (`action_log_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DELETE FROM `topology` WHERE topology_name='Logs' AND topology_page='508';
DELETE FROM `topology` WHERE topology_name='Configuration' AND topology_page='50801';
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Logs', NULL, 5, 508, 11, 1, './include/options/configurationChangelog/viewLogs.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Configuration', './img/icones/16x16/text_code.gif', 5, 50801, 10, 80, './include/options/configurationChangelog/viewLogs.php', NULL, '0', '0', '1', NULL, NULL, NULL);