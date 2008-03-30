-- --------------------------------------------------------

--
-- Structure de la table `log_archive_file_name`
--

CREATE TABLE IF NOT EXISTS `log_archive_file_name` (
  `id_log_file` int(11) NOT NULL auto_increment,
  `file_name` varchar(200) default NULL,
  `date` int(11) default NULL,
  PRIMARY KEY  (`id_log_file`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `log_archive_host`
--

CREATE TABLE IF NOT EXISTS `log_archive_host` (
  `log_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) default NULL,
  `UPTimeScheduled` int(11) default NULL,
  `UPnbEvent` int(11) default NULL,
  `UPTimeAverageAck` int(11) NOT NULL,
  `UPTimeAverageRecovery` int(11) NOT NULL,
  `DOWNTimeScheduled` int(11) default NULL,
  `DOWNnbEvent` int(11) default NULL,
  `DOWNTimeAverageAck` int(11) NOT NULL,
  `DOWNTimeAverageRecovery` int(11) NOT NULL,
  `UNREACHABLETimeScheduled` int(11) default NULL,
  `UNREACHABLEnbEvent` int(11) default NULL,
  `UNREACHABLETimeAverageAck` int(11) NOT NULL,
  `UNREACHABLETimeAverageRecovery` int(11) NOT NULL,
  `date_end` int(11) default NULL,
  `date_start` int(11) default NULL,
  PRIMARY KEY  (`log_id`),
  UNIQUE KEY `log_id` (`log_id`),
  KEY `host_index` (`host_id`),
  KEY `date_end_index` (`date_end`),
  KEY `date_start_index` (`date_start`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `log_archive_service`
--

CREATE TABLE IF NOT EXISTS `log_archive_service` (
  `log_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) NOT NULL default '0',
  `service_id` int(11) NOT NULL default '0',
  `OKTimeScheduled` int(11) NOT NULL default '0',
  `OKnbEvent` int(11) NOT NULL default '0',
  `OKTimeAverageAck` int(11) NOT NULL,
  `OKTimeAverageRecovery` int(11) NOT NULL,
  `WARNINGTimeScheduled` int(11) NOT NULL default '0',
  `WARNINGnbEvent` int(11) NOT NULL default '0',
  `WARNINGTimeAverageAck` int(11) NOT NULL,
  `WARNINGTimeAverageRecovery` int(11) NOT NULL,
  `UNKNOWNTimeScheduled` int(11) NOT NULL default '0',
  `UNKNOWNnbEvent` int(11) NOT NULL default '0',
  `UNKNOWNTimeAverageAck` int(11) NOT NULL,
  `UNKNOWNTimeAverageRecovery` int(11) NOT NULL,
  `CRITICALTimeScheduled` int(11) NOT NULL default '0',
  `CRITICALnbEvent` int(11) NOT NULL default '0',
  `CRITICALTimeAverageAck` int(11) NOT NULL,
  `CRITICALTimeAverageRecovery` int(11) NOT NULL,
  `UNDETERMINATETimeScheduled` int(11) NOT NULL default '0',
  `UNDETERMINATETimeUnScheduled` int(11) NOT NULL default '0',
  `date_start` int(11) default NULL,
  `date_end` int(11) default NULL,
  PRIMARY KEY  (`log_id`),
  KEY `host_index` (`host_id`),
  KEY `service_index` (`service_id`),
  KEY `date_end_index` (`date_end`),
  KEY `date_start_index` (`date_start`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



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
  `msg_type` enum('0', '1', '2', '3', '4', '5', '6', '7', '8', '9') NOT NULL,
  PRIMARY KEY  (`log_id`),
  KEY `host_name` (`host_name`(64)),
  KEY `service_description` (`service_description`(64)),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `log_snmptt`
-- 


CREATE TABLE `log_snmptt` (
`trap_id` INT NULL AUTO_INCREMENT PRIMARY KEY ,
`trap_oid` TEXT NULL ,
`trap_ip` VARCHAR( 50 ) NULL ,
`trap_community` VARCHAR( 50 ) NULL ,
`trap_infos` TEXT NULL
) ENGINE = MYISAM ;

--CREATE TABLE `log_snmptt` (
--  `traps_id` int(11) NOT NULL auto_increment,
--  `traps_oid` varchar(255) default NULL,
--  `traps_ip` varchar(255) default NULL,
--  `traps_community` varchar(255) default NULL,
--  `traps_infos` varchar(255) default NULL,
--  PRIMARY KEY  (`traps_id`)
--) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `metrics`
-- 

CREATE TABLE `metrics` (
  `metric_id` int(11) NOT NULL auto_increment,
  `index_id` int(11) default NULL,
  `metric_name` varchar(100) default NULL,
  `unit_name` varchar(32) default NULL,
  `warn` float default NULL,
  `crit` float default NULL,
  `min` float default NULL,
  `max` float default NULL,
  `hidden` ENUM( '0', '1' ) NULL DEFAULT '0',
  `locked` ENUM( '0', '1' ) NULL DEFAULT '0',
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

CREATE TABLE `traps` (
`traps_id` INT NULL AUTO_INCREMENT PRIMARY KEY ,
`traps_oid` VARCHAR( 255 ) NULL ,
`traps_ip` VARCHAR( 255 ) NULL ,
`traps_community` VARCHAR( 255 ) NULL ,
`traps_infos` VARCHAR( 255 ) NULL
) ENGINE = MYISAM ;

ALTER TABLE `index_data` ADD INDEX ( `must_be_rebuild` );
ALTER TABLE `index_data` ADD INDEX ( `trashed` );
ALTER TABLE `index_data` ADD INDEX ( `host_name` );
ALTER TABLE `index_data` ADD INDEX ( `service_description` );


