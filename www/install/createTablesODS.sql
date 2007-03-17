-- 
-- Structure de la table `config`
-- 

CREATE TABLE `config` (
  `id` int(11) NOT NULL auto_increment,
  `RRDdatabase_path` varchar(255) default NULL,
  `len_storage_rrd` int(11) default NULL,
  `autodelete_rrd_db` enum('0','1') default NULL,
  `sleep_time` int(11) default '10',
  `purge_interval` int(11) default '2',
  `storage_type` int(11) default '2',
  `average` int(11) default NULL,
  `auto_drop` enum('0','1') NOT NULL default '0',
  `drop_file` varchar(255) default NULL,
  `perfdata_file` varchar(255) default NULL,
  `archive_log` enum('0','1') NOT NULL default '0',
  `archive_retention` int(11) default '31',
  `nagios_log_file` varchar(255) default NULL,
  `last_line_read` int(11) default '31',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

INSERT INTO `config` (`id`, `RRDdatabase_path`, `len_storage_rrd`, `autodelete_rrd_db`, `sleep_time`, `purge_interval`, `storage_type`, `auto_drop`, `drop_file`, `perfdata_file`, `nagios_log_file`) VALUES (1, '/srv/oreon/OreonDataStorage/', 31536000, '0', 10, 60, 0, '1', '/srv/nagios/var/service-perfdata.tmp', '/srv/nagios/var/service-perfdata', '/srv/nagios/var/nagios.log');

-- 
-- Structure de la table `data_bin`
-- 

CREATE TABLE `data_bin` (
  `id_bin` int(11) NOT NULL auto_increment,
  `id_metric` int(11) default NULL,
  `ctime` int(11) default NULL,
  `value` float default NULL,
  `status` enum('0','1','2','3','4') default NULL,
  PRIMARY KEY  (`id_bin`),
  KEY `index_metric` (`id_metric`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `index_data`
-- 

CREATE TABLE `index_data` (
  `id` int(11) NOT NULL auto_increment,
  `host_name` varchar(75) default NULL,
  `host_id` int(11) default NULL,
  `service_description` varchar(75) default NULL,
  `service_id` int(11) default NULL,
  `special` enum('0','1') default '0',
  `storage_type` enum('0','1','2') default '2',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `log`
-- 

CREATE TABLE `log` (
  `log_id` int(11) NOT NULL auto_increment,
  `ctime` int(11) default NULL,
  `host_name` varchar(255) default NULL,
  `service_description` varchar(255) default NULL,
  `status` enum('0','1','2','3') default NULL,
  `output` text,
  `notification_cmd` varchar(255) default NULL,
  `notification_contact` varchar(255) default NULL,
  `type` enum('0','1') NOT NULL,
  `retry` int(255) NOT NULL,
  `msg_type` enum('0','1','2','3','4','5') NOT NULL,
  PRIMARY KEY  (`log_id`),
  KEY `host_name` (`host_name`(64)),
  KEY `service_description` (`service_description`(64)),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=43156 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `metrics`
-- 

CREATE TABLE `metrics` (
  `metric_id` int(11) NOT NULL auto_increment,
  `index_id` int(11) default NULL,
  `metric_name` varchar(32) default NULL,
  `unit_name` varchar(32) default NULL,
  `warn` float default NULL,
  `crit` float default NULL,
  `rrdDataBase_Path` varchar(255) default NULL,
  PRIMARY KEY  (`metric_id`),
  KEY `index` (`index_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `statistics`
-- 

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL auto_increment,
  `ctime` int(11) default NULL,
  `last_insert_duration` int(11) default NULL,
  `average_duration` int(11) default NULL,
  `last_nb_line` int(11) default NULL,
  `cpt` int(11) default NULL,
  `last_restart` int(11) default NULL,
  `average` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 

INSERT INTO `statistics` (`id`, `ctime`, `last_insert_duration`, `average_duration`, `last_nb_line`, `cpt`, `last_restart`, `average`) VALUES (1, 0, 0, 0, 0, 0, 0, 0);


-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `data_bin`
-- 
ALTER TABLE `data_bin`
  ADD CONSTRAINT `data_bin_ibfk_1` FOREIGN KEY (`id_metric`) REFERENCES `metrics` (`metric_id`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `metrics`
-- 
ALTER TABLE `metrics`
  ADD CONSTRAINT `metrics_ibfk_1` FOREIGN KEY (`index_id`) REFERENCES `index_data` (`id`) ON DELETE CASCADE;
