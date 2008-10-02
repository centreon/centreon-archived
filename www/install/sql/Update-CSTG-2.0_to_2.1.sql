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

-- --------------------------------------------------------

-- 
-- Structure de la table `acl_actions`
-- 

CREATE TABLE `acl_actions` (
  `acl_action_id` int(11) NOT NULL auto_increment,
  `acl_action_name` varchar(255) default NULL,
  `acl_action_description` varchar(255) default NULL,
  `acl_action_activate` enum('0','1','2') default NULL,
  PRIMARY KEY  (`acl_action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `acl_actions_rules`
-- 

CREATE TABLE `acl_actions_rules` (
  `aar_id` int(11) NOT NULL auto_increment,
  `acl_action_rule_id` int(11) default NULL,
  `acl_action_name` varchar(255) default NULL,
  PRIMARY KEY  (`aar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `acl_group_actions_relations`
-- 

CREATE TABLE `acl_group_actions_relations` (
  `agar_id` int(11) NOT NULL auto_increment,
  `acl_action_id` int(11) default NULL,
  `acl_group_id` int(11) default NULL,
  PRIMARY KEY  (`agar_id`),
  KEY `acl_action_id` (`acl_action_id`),
  KEY `acl_group_id` (`acl_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------