alter table contact add contact_enable_notifications enum('0','1') default '0' after contact_oreon;
alter table contact add contact_template_id int(11) default null after contact_enable_notifications;

ALTER TABLE `contact` ADD INDEX ( `contact_template_id` );

ALTER TABLE `contact` 
	ADD CONSTRAINT `contact_ibfk_3` FOREIGN KEY (`contact_template_id`) REFERENCES `contact` (`contact_id`) ON DELETE SET NULL;

--
-- Structure de la table `auth_ressource`
--

CREATE TABLE IF NOT EXISTS `auth_ressource` (
  `ar_id` INT(11) NOT NULL AUTO_INCREMENT,
  `ar_type` VARCHAR(50) NOT NULL,
  `ar_enable` ENUM('0', '1') DEFAULT 0,
  `ar_order` INT(3) DEFAULT 0,
  PRIMARY KEY (`ar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Structure de la table `auth_ressource_info`
--

CREATE TABLE IF NOT EXISTS `auth_ressource_info` (
  `ar_id` INT(11) NOT NULL,
  `ari_name` VARCHAR(100) NOT NULL,
  `ari_value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ar_id`, `ari_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Contraintes pour la table `auth_ressource_info`
--
ALTER TABLE `auth_ressource_info`
  ADD CONSTRAINT `auth_ressource_info_ibfk_1` FOREIGN KEY (`ar_id`) REFERENCES `auth_ressource` (`ar_id`) ON DELETE CASCADE;

--
-- Structure de la table `downtime`
--
CREATE TABLE IF NOT EXISTS `downtime` (
  `dt_id` INT(11) NOT NULL AUTO_INCREMENT,
  `dt_name` VARCHAR(100) NOT NULL,
  `dt_description` VARCHAR(255) DEFAULT NULL,
  `dt_activate` ENUM('0', '1') DEFAULT '1',
  PRIMARY KEY (`dt_id`),
  KEY `downtime_idx01` (`dt_id`, `dt_activate`),
  UNIQUE KEY `downtime_idx02` (`dt_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_period`
--
CREATE TABLE IF NOT EXISTS `downtime_period` (
  `dt_id` INT(11) NOT NULL,
  `dtp_start_time` TIME NOT NULL,
  `dtp_end_time` TIME NOT NULL,
  `dtp_day_of_week` VARCHAR(15) DEFAULT NULL,
  `dtp_month_cycle` ENUM('first', 'last', 'all', 'none') DEFAULT 'all',
  `dtp_day_of_month` VARCHAR(100) DEFAULT NULL,
  `dtp_fixed` ENUM('0', '1') DEFAULT '1',
  `dtp_duration` INT DEFAULT NULL,
  `dtp_next_date` DATE DEFAULT NULL,
  `dtp_activate` ENUM('0', '1') DEFAULT '1',
  KEY `downtime_period_idx01` (`dt_id`, `dtp_activate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_host_relation`
--
CREATE TABLE IF NOT EXISTS `downtime_host_relation` (
	`dt_id` INT(11) NOT NULL,
	`host_host_id` INT(11) NOT NULL,
	PRIMARY KEY (`dt_id`, `host_host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_hostgroup_relation`
--
CREATE TABLE IF NOT EXISTS `downtime_hostgroup_relation` (
	`dt_id` INT(11) NOT NULL,
	`hg_hg_id` INT(11) NOT NULL,
	PRIMARY KEY (`dt_id`, `hg_hg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_service_relation`
--
CREATE TABLE IF NOT EXISTS `downtime_service_relation` (
	`dt_id` INT(11) NOT NULL,
	`service_service_id` INT(11) NOT NULL,
	PRIMARY KEY (`dt_id`, `service_service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Structure de la table `downtime_servicegroup_relation`
--
CREATE TABLE IF NOT EXISTS `downtime_servicegroup_relation` (
	`dt_id` INT(11) NOT NULL,
	`sg_sg_id` INT(11) NOT NULL,
	PRIMARY KEY (`dt_id`, `sg_sg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Contraintes pour la table `downtime_period`
--
ALTER TABLE `downtime_period`
  ADD CONSTRAINT `downtime_period_ibfk_1` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE; 
  
--
-- Contraintes pour la table `downtime_host_relation`
--
ALTER TABLE `downtime_host_relation`
  ADD CONSTRAINT `downtime_host_relation_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downtime_host_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE;
  
--
-- Contraintes pour la table `downtime_hostgroup_relation`
--
ALTER TABLE `downtime_hostgroup_relation`
  ADD CONSTRAINT `downtime_hostgroup_relation_ibfk_1` FOREIGN KEY (`hg_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downtime_hostgroup_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE;
  
--
-- Contraintes pour la table `downtime_service_relation`
--
ALTER TABLE `downtime_service_relation`
  ADD CONSTRAINT `downtime_service_relation_ibfk_1` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downtime_service_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE;
  
--
-- Contraintes pour la table `downtime_service_relation`
--
ALTER TABLE `downtime_servicegroup_relation`
  ADD CONSTRAINT `downtime_servicegroup_relation_ibfk_1` FOREIGN KEY (`sg_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downtime_servicegroup_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE;
 
--
-- Alter contactgroup for ldap group
--
 ALTER TABLE `contactgroup` ADD `cg_type` varchar(10) default 'local';
 ALTER TABLE `contactgroup` ADD `cg_ldap_dn` varchar(255) default NULL;
 
 
 ALTER TABLE `contact` ADD `contact_register` TINYINT( 6 ) NOT NULL DEFAULT '0';
 
 UPDATE `informations` SET `value` = '2.3.0-RC1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.2.0' LIMIT 1;
 