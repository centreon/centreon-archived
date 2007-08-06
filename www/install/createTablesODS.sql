-- 
-- Structure de la table `config`
-- 

CREATE TABLE `config` (
  `id` int(11) NOT NULL auto_increment,
  `RRDdatabase_path` varchar(255) default NULL,
  `len_storage_rrd` int(11) default NULL,
  `len_storage_mysql` int(11) default NULL,
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
  `fast_parsing` enum('0','1') default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

INSERT INTO `config` (`id`, `RRDdatabase_path`, `len_storage_rrd`, `len_storage_mysql`, `autodelete_rrd_db`, `sleep_time`, `purge_interval`, `storage_type`, `auto_drop`, `drop_file`, `perfdata_file`, `nagios_log_file`) VALUES (1, '@DATA_DIR_ODS@', 365, '260', '0', 10, 60, '2', '1', '@INSTALL_DIR_NAGIOS@/var/service-perfdata.tmp', '@INSTALL_DIR_NAGIOS@/var/service-perfdata', '@INSTALL_DIR_NAGIOS@/var/nagios.log');

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
  KEY `index_metric` (`id_metric`),
  KEY `ctime` (`ctime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `data_stats_daily`
-- 

CREATE TABLE `data_stats_daily` (
  `data_stats_daily_id` int(11) NOT NULL auto_increment,
  `metric_id` int(11) default NULL,
  `min` int(11) default NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `count` int(11) default NULL,
  `day_time` int(11) default NULL,
  PRIMARY KEY  (`data_stats_daily_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `data_stats_monthly`
-- 

CREATE TABLE `data_stats_monthly` (
  `data_stats_monthly_id` int(11) NOT NULL auto_increment,
  `metric_id` int(11) default NULL,
  `min` int(11) default NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `count` int(11) default NULL,
  `month_time` int(11) default NULL,
  PRIMARY KEY  (`data_stats_monthly_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `data_stats_yearly`
-- 

CREATE TABLE `data_stats_yearly` (
  `data_stats_yearly_id` int(11) NOT NULL auto_increment,
  `metric_id` int(11) default NULL,
  `min` int(11) default NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `count` int(11) default NULL,
  `year_time` int(11) default NULL,
  PRIMARY KEY  (`data_stats_yearly_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `check_interval` int(11) default NULL,
  `special` enum('0','1') default '0',
  `trashed` enum('0','1') default '0',
  `must_be_rebuild` enum('0','1','2') default '0',
  `storage_type` enum('0','1','2') default '2',
  PRIMARY KEY  (`id`),
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `host_id` (`host_id`),
  KEY `service_id` (`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `instance`
-- 

CREATE TABLE `instance` (
  `instance_id` int(11) NOT NULL auto_increment,
  `instance_name` varchar(254) default NULL,
  `instance_alias` varchar(254) default NULL,
  PRIMARY KEY  (`instance_id`),
  UNIQUE KEY `instance_name` (`instance_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `log`
-- 

CREATE TABLE `log` (
  `log_id` int(11) NOT NULL auto_increment,
  `ctime` int(11) default NULL,
  `host_name` varchar(255) default NULL,
  `service_description` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `output` text,
  `notification_cmd` varchar(255) default NULL,
  `notification_contact` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `retry` int(255) NOT NULL,
  `msg_type` enum('0','1','2','3','4','5') NOT NULL,
  PRIMARY KEY  (`log_id`),
  KEY `host_name` (`host_name`(64)),
  KEY `service_description` (`service_description`(64)),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
  PRIMARY KEY  (`metric_id`),
  KEY `index` (`index_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `statistics`
-- 

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL auto_increment,
  `ctime` int(11) default NULL,
  `lineRead` int(11) default NULL,
  `valueReccorded` int(11) default NULL,
  `last_insert_duration` int(11) default NULL,
  `average_duration` int(11) default NULL,
  `last_nb_line` int(11) default NULL,
  `cpt` int(11) default NULL,
  `last_restart` int(11) default NULL,
  `average` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `statistics` VALUES (1, '0', '0', '0', '0', '0', '0', '0', '0', '0');


ALTER TABLE `index_data` ADD INDEX ( `must_be_rebuild` );
ALTER TABLE `index_data` ADD INDEX ( `trashed` );
ALTER TABLE `index_data` ADD INDEX ( `host_name` );
ALTER TABLE `index_data` ADD INDEX ( `service_description` );


