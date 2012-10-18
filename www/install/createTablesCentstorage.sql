
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de donn�es: `centreon2_centstorage`
--

-- --------------------------------------------------------

--
-- Structure de la table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL auto_increment,
  `RRDdatabase_path` varchar(255) default NULL,
  `RRDdatabase_status_path` varchar(255) default NULL,
  `RRDdatabase_nagios_stats_path` varchar(255) default NULL,
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
  `reporting_retention` int(11) default '365',
  `nagios_log_file` varchar(255) default NULL,
  `last_line_read` int(11) default '31',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `centreon_acl`
--

CREATE TABLE IF NOT EXISTS `centreon_acl` (  
  `host_id` int(11) default NULL,
  `host_name` varchar(255) default NULL,
  `service_id` int(11) default NULL,
  `service_description` varchar(255) default NULL,
  `group_id` int(11) default NULL,  
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `group_id_by_name` (`host_name`(70),`service_description`(120),`group_id`),
  KEY `group_id_by_id` (`host_id`,`service_id`,`group_id`),
  KEY `group_id_for_host` (`host_name`,`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `data_bin`
--

CREATE TABLE IF NOT EXISTS `data_bin` (
  `id_metric` int(11) default NULL,
  `ctime` int(11) default NULL,
  `value` float default NULL,
  `status` enum('0','1','2','3','4') default NULL,
  KEY `index_metric` (`id_metric`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `data_stats_daily`
--

CREATE TABLE IF NOT EXISTS `data_stats_daily` (
  `data_stats_daily_id` int(11) NOT NULL auto_increment,
  `metric_id` int(11) default NULL,
  `min` int(11) default NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `count` int(11) default NULL,
  `day_time` int(11) default NULL,
  PRIMARY KEY  (`data_stats_daily_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `data_stats_monthly`
--

CREATE TABLE IF NOT EXISTS `data_stats_monthly` (
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

CREATE TABLE IF NOT EXISTS `data_stats_yearly` (
  `data_stats_yearly_id` int(11) NOT NULL auto_increment,
  `metric_id` int(11) default NULL,
  `min` int(11) default NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `count` int(11) default NULL,
  `year_time` int(11) default NULL,
  PRIMARY KEY  (`data_stats_yearly_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `index_data`
--

CREATE TABLE IF NOT EXISTS `index_data` (
  `id` int(11) NOT NULL auto_increment,
  `host_name` varchar(255) default NULL,
  `host_id` int(11) default NULL,
  `service_description` varchar(255) default NULL,
  `service_id` int(11) default NULL,
  `check_interval` int(11) default NULL,
  `special` enum('0','1') default '0',
  `hidden` enum('0','1') default '0',
  `locked` enum('0','1') default '0',
  `trashed` enum('0','1') default '0',
  `must_be_rebuild` enum('0','1','2') default '0',
  `storage_type` enum('0','1','2') default '2',
  PRIMARY KEY  (`id`),
  UNIQUE `host_service_unique_id` (`host_id`, `service_id`),
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `host_id` (`host_id`),
  KEY `service_id` (`service_id`),
  KEY `must_be_rebuild` (`must_be_rebuild`),
  KEY `trashed` (`trashed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `instance`
--

CREATE TABLE IF NOT EXISTS `instance` (
  `instance_id` int(11) NOT NULL auto_increment,
  `instance_name` varchar(254) default NULL,
  `instance_alias` varchar(254) default NULL,
  `log_flag` int(11) default NULL,
  `log_md5` varchar(255) default NULL,
  PRIMARY KEY  (`instance_id`),
  UNIQUE KEY `instance_name` (`instance_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
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
  `msg_type` enum('0','1','2','3','4','5','6','7','8','9','10','11') NOT NULL,
  `instance` int(11) NOT NULL default '1',
  PRIMARY KEY  (`log_id`),
  KEY `host_name` (`host_name`(64)),
  KEY `service_description` (`service_description`(64)),
  KEY `status` (`status`),
  KEY `instance` (`instance`),
  KEY `ctime` (`ctime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `log_archive_host`
-- 

CREATE TABLE `log_archive_host` (
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
  `UNDETERMINEDTimeScheduled` int(11) default NULL,
  `MaintenanceTime` int(11) default '0',
  `date_end` int(11) default NULL,
  `date_start` int(11) default NULL,
  PRIMARY KEY  (`log_id`),
  UNIQUE KEY `log_id` (`log_id`),
  KEY `host_index` (`host_id`),
  KEY `date_end_index` (`date_end`),
  KEY `date_start_index` (`date_start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `log_archive_service`
-- 

CREATE TABLE `log_archive_service` (
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
  `UNDETERMINEDTimeScheduled` int(11) NOT NULL default '0',
  `MaintenanceTime` int(11) default '0',
  `date_start` int(11) default NULL,
  `date_end` int(11) default NULL,
  PRIMARY KEY  (`log_id`),
  KEY `host_index` (`host_id`),
  KEY `service_index` (`service_id`),
  KEY `date_end_index` (`date_end`),
  KEY `date_start_index` (`date_start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `log_archive_last_status`
-- 

CREATE TABLE `log_archive_last_status` (
  `host_id` int(11) default NULL,
  `service_id` int(11) default NULL,
  `host_name` varchar(255) default NULL,
  `service_description` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `ctime` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `log_snmptt`
--

CREATE TABLE IF NOT EXISTS `log_snmptt` (
  `trap_id` int(11) NOT NULL auto_increment,
  `trap_oid` text,
  `trap_ip` varchar(50) default NULL,
  `trap_community` varchar(50) default NULL,
  `trap_infos` text,
  PRIMARY KEY  (`trap_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `metrics`
--

CREATE TABLE IF NOT EXISTS `metrics` (
  `metric_id` int(11) NOT NULL auto_increment,
  `index_id` int(11) default NULL,
  `metric_name` varchar(255) default NULL,
  `data_source_type` enum('0','1','2','3') DEFAULT NULL,
  `unit_name` varchar(32) default NULL,
  `warn` float default NULL,
  `crit` float default NULL,
  `hidden` enum('0','1') default '0',
  `min` float default NULL,
  `max` float default NULL,
  `locked` enum('0','1') default NULL,
  PRIMARY KEY  (`metric_id`),
  UNIQUE (`index_id`, `metric_name`),
  KEY `index` (`index_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `statistics`
--

CREATE TABLE IF NOT EXISTS `statistics` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

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

-- 
-- Structure de la table `nagios_stats`
-- 

CREATE TABLE IF NOT EXISTS `nagios_stats` (
  `instance_id` int(11) NOT NULL,
  `stat_key` varchar(255) NOT NULL,
  `stat_value` varchar(255) NOT NULL,
  `stat_label` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `hoststateevents`
--

CREATE TABLE IF NOT EXISTS `hoststateevents` (
  `hoststateevent_id` int(11) NOT NULL auto_increment,
  `end_time` int(11) default NULL,
  `host_id` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `state` tinyint(11) NOT NULL,
  `last_update` tinyint(4) NOT NULL default '0',
  `in_downtime` tinyint(4) NOT NULL,
  `ack_time` int(11) DEFAULT NULL,
  PRIMARY KEY  (`hoststateevent_id`),
  UNIQUE (host_id, start_time),
    KEY `start_time` (`start_time`), 
    KEY `end_time` (`end_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Table structure for table `rebuild`
--

CREATE TABLE `rebuild` (
   `id` INT NOT NULL AUTO_INCREMENT ,
   `index_id` INT NULL ,
   `status` INT NULL ,
   `centreon_instance` INT NULL ,
   PRIMARY KEY ( `id` )
) ENGINE = InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `servicestateevents`
--

CREATE TABLE IF NOT EXISTS `servicestateevents` (
  `servicestateevent_id` int(11) NOT NULL auto_increment,
  `end_time` int(11) default NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11) default NULL,
  `start_time` int(11) NOT NULL,
  `state` tinyint(11) NOT NULL,
  `last_update` tinyint(4) NOT NULL default '0',
  `in_downtime` tinyint(4) NOT NULL,
  `ack_time` int(11) DEFAULT NULL,
  PRIMARY KEY  (`servicestateevent_id`),
  UNIQUE (host_id, service_id, start_time),
    KEY `start_time` (`start_time`), 
    KEY `end_time` (`end_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Data 
--

INSERT INTO `config` (`id`, `RRDdatabase_path`, `RRDdatabase_status_path`, `RRDdatabase_nagios_stats_path`, `len_storage_rrd`, `len_storage_mysql`, `autodelete_rrd_db`, `sleep_time`, `purge_interval`, `storage_type`, `average`, `auto_drop`, `drop_file`, `perfdata_file`, `archive_log`, `archive_retention`, `reporting_retention`, `nagios_log_file`, `last_line_read`) VALUES(1, '@CENTSTORAGE_RRD@/metrics/', '@CENTSTORAGE_RRD@/status/', '@CENTSTORAGE_RRD@/nagios-perf/', 180, 180, '1', 10, 360, 2, NULL, '0', '@MONITORING_VAR_LOG@/service-perfdata.tmp', '@MONITORING_VAR_LOG@/service-perfdata', '1', 31, 365, '@MONITORING_VAR_LOG@/nagios.log', 0);

INSERT INTO `statistics` (`id`, `ctime`, `lineRead`, `valueReccorded`, `last_insert_duration`, `average_duration`, `last_nb_line`, `cpt`, `last_restart`, `average`) VALUES(1, 0, 1, 0, 0, 0, 0, 0, 0, 0);
