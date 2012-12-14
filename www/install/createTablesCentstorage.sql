
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `centreon_acl` (
  `host_id` int(11) DEFAULT NULL,
  `host_name` varchar(255) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `service_description` varchar(255) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `group_id_by_name` (`host_name`(70),`service_description`(120),`group_id`),
  KEY `group_id_by_id` (`host_id`,`service_id`,`group_id`),
  KEY `group_id_for_host` (`host_name`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `centreon_acl` WRITE;
/*!40000 ALTER TABLE `centreon_acl` DISABLE KEYS */;
/*!40000 ALTER TABLE `centreon_acl` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `RRDdatabase_path` varchar(255) DEFAULT NULL,
  `RRDdatabase_status_path` varchar(255) DEFAULT NULL,
  `RRDdatabase_nagios_stats_path` varchar(255) DEFAULT NULL,
  `len_storage_rrd` int(11) DEFAULT NULL,
  `len_storage_mysql` int(11) DEFAULT NULL,
  `autodelete_rrd_db` enum('0','1') DEFAULT NULL,
  `sleep_time` int(11) DEFAULT '10',
  `purge_interval` int(11) DEFAULT '2',
  `storage_type` int(11) DEFAULT '2',
  `average` int(11) DEFAULT NULL,
  `auto_drop` enum('0','1') NOT NULL DEFAULT '0',
  `drop_file` varchar(255) DEFAULT NULL,
  `perfdata_file` varchar(255) DEFAULT NULL,
  `archive_log` enum('0','1') NOT NULL DEFAULT '0',
  `archive_retention` int(11) DEFAULT '31',
  `reporting_retention` int(11) DEFAULT '365',
  `nagios_log_file` varchar(255) DEFAULT NULL,
  `last_line_read` int(11) DEFAULT '31',
  `audit_log_option` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (1,'@CENTSTORAGE_RRD@/metrics/','@CENTSTORAGE_RRD@/status/','@CENTSTORAGE_RRD@/nagios-perf/',180,180,'1',10,360,2,NULL,'0','@MONITORING_VAR_LOG@/service-perfdata.tmp','@MONITORING_VAR_LOG@/service-perfdata','1',31,365,'@MONITORING_VAR_LOG@/nagios.log',0,'1');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_bin` (
  `id_metric` int(11) DEFAULT NULL,
  `ctime` int(11) DEFAULT NULL,
  `value` float DEFAULT NULL,
  `status` enum('0','1','2','3','4') DEFAULT NULL,
  KEY `index_metric` (`id_metric`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `data_bin` WRITE;
/*!40000 ALTER TABLE `data_bin` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_bin` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_stats_daily` (
  `data_stats_daily_id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_id` int(11) DEFAULT NULL,
  `min` int(11) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  `average` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `day_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`data_stats_daily_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `data_stats_daily` WRITE;
/*!40000 ALTER TABLE `data_stats_daily` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_stats_daily` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_stats_monthly` (
  `data_stats_monthly_id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_id` int(11) DEFAULT NULL,
  `min` int(11) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  `average` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `month_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`data_stats_monthly_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `data_stats_monthly` WRITE;
/*!40000 ALTER TABLE `data_stats_monthly` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_stats_monthly` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_stats_yearly` (
  `data_stats_yearly_id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_id` int(11) DEFAULT NULL,
  `min` int(11) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  `average` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `year_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`data_stats_yearly_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `data_stats_yearly` WRITE;
/*!40000 ALTER TABLE `data_stats_yearly` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_stats_yearly` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `index_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host_name` varchar(255) DEFAULT NULL,
  `host_id` int(11) DEFAULT NULL,
  `service_description` varchar(255) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `check_interval` int(11) DEFAULT NULL,
  `special` enum('0','1') DEFAULT '0',
  `hidden` enum('0','1') DEFAULT '0',
  `locked` enum('0','1') DEFAULT '0',
  `trashed` enum('0','1') DEFAULT '0',
  `must_be_rebuild` enum('0','1','2') DEFAULT '0',
  `storage_type` enum('0','1','2') DEFAULT '2',
  `to_delete` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_service_unique_id` (`host_id`,`service_id`),
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `host_id` (`host_id`),
  KEY `service_id` (`service_id`),
  KEY `must_be_rebuild` (`must_be_rebuild`),
  KEY `trashed` (`trashed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `index_data` WRITE;
/*!40000 ALTER TABLE `index_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `index_data` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instance` (
  `instance_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_name` varchar(254) DEFAULT NULL,
  `instance_alias` varchar(254) DEFAULT NULL,
  `log_flag` int(11) DEFAULT NULL,
  `log_md5` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`instance_id`),
  UNIQUE KEY `instance_name` (`instance_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `instance` WRITE;
/*!40000 ALTER TABLE `instance` DISABLE KEYS */;
/*!40000 ALTER TABLE `instance` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `host_name` varchar(255) DEFAULT NULL,
  `service_description` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `output` text,
  `notification_cmd` varchar(255) DEFAULT NULL,
  `notification_contact` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `retry` int(255) NOT NULL,
  `msg_type` enum('0','1','2','3','4','5','6','7','8','9','10','11') NOT NULL,
  `instance` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`log_id`),
  KEY `host_name` (`host_name`(64)),
  KEY `service_description` (`service_description`(64)),
  KEY `status` (`status`),
  KEY `instance` (`instance`),
  KEY `ctime` (`ctime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_action` (
  `action_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_log_date` int(11) NOT NULL,
  `object_type` varchar(255) NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_name` varchar(255) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `log_contact_id` int(11) NOT NULL,
  PRIMARY KEY (`action_log_id`),
  KEY `log_contact_id` (`log_contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_action` WRITE;
/*!40000 ALTER TABLE `log_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_action` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_action_modification` (
  `modification_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(255) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  `action_log_id` int(11) NOT NULL,
  PRIMARY KEY (`modification_id`),
  KEY `action_log_id` (`action_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_action_modification` WRITE;
/*!40000 ALTER TABLE `log_action_modification` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_action_modification` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_archive_host` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) DEFAULT NULL,
  `UPTimeScheduled` int(11) DEFAULT NULL,
  `UPnbEvent` int(11) DEFAULT NULL,
  `UPTimeAverageAck` int(11) NOT NULL,
  `UPTimeAverageRecovery` int(11) NOT NULL,
  `DOWNTimeScheduled` int(11) DEFAULT NULL,
  `DOWNnbEvent` int(11) DEFAULT NULL,
  `DOWNTimeAverageAck` int(11) NOT NULL,
  `DOWNTimeAverageRecovery` int(11) NOT NULL,
  `UNREACHABLETimeScheduled` int(11) DEFAULT NULL,
  `UNREACHABLEnbEvent` int(11) DEFAULT NULL,
  `UNREACHABLETimeAverageAck` int(11) NOT NULL,
  `UNREACHABLETimeAverageRecovery` int(11) NOT NULL,
  `UNDETERMINEDTimeScheduled` int(11) DEFAULT NULL,
  `MaintenanceTime` int(11) DEFAULT '0',
  `date_end` int(11) DEFAULT NULL,
  `date_start` int(11) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  UNIQUE KEY `log_id` (`log_id`),
  KEY `host_index` (`host_id`),
  KEY `date_end_index` (`date_end`),
  KEY `date_start_index` (`date_start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_archive_host` WRITE;
/*!40000 ALTER TABLE `log_archive_host` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_archive_host` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_archive_last_status` (
  `host_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `host_name` varchar(255) DEFAULT NULL,
  `service_description` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `ctime` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_archive_last_status` WRITE;
/*!40000 ALTER TABLE `log_archive_last_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_archive_last_status` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_archive_service` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0',
  `service_id` int(11) NOT NULL DEFAULT '0',
  `OKTimeScheduled` int(11) NOT NULL DEFAULT '0',
  `OKnbEvent` int(11) NOT NULL DEFAULT '0',
  `OKTimeAverageAck` int(11) NOT NULL,
  `OKTimeAverageRecovery` int(11) NOT NULL,
  `WARNINGTimeScheduled` int(11) NOT NULL DEFAULT '0',
  `WARNINGnbEvent` int(11) NOT NULL DEFAULT '0',
  `WARNINGTimeAverageAck` int(11) NOT NULL,
  `WARNINGTimeAverageRecovery` int(11) NOT NULL,
  `UNKNOWNTimeScheduled` int(11) NOT NULL DEFAULT '0',
  `UNKNOWNnbEvent` int(11) NOT NULL DEFAULT '0',
  `UNKNOWNTimeAverageAck` int(11) NOT NULL,
  `UNKNOWNTimeAverageRecovery` int(11) NOT NULL,
  `CRITICALTimeScheduled` int(11) NOT NULL DEFAULT '0',
  `CRITICALnbEvent` int(11) NOT NULL DEFAULT '0',
  `CRITICALTimeAverageAck` int(11) NOT NULL,
  `CRITICALTimeAverageRecovery` int(11) NOT NULL,
  `UNDETERMINEDTimeScheduled` int(11) NOT NULL DEFAULT '0',
  `MaintenanceTime` int(11) DEFAULT '0',
  `date_start` int(11) DEFAULT NULL,
  `date_end` int(11) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `host_index` (`host_id`),
  KEY `service_index` (`service_id`),
  KEY `date_end_index` (`date_end`),
  KEY `date_start_index` (`date_start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_archive_service` WRITE;
/*!40000 ALTER TABLE `log_archive_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_archive_service` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_snmptt` (
  `trap_id` int(11) NOT NULL AUTO_INCREMENT,
  `trap_oid` text,
  `trap_ip` varchar(50) DEFAULT NULL,
  `trap_community` varchar(50) DEFAULT NULL,
  `trap_infos` text,
  PRIMARY KEY (`trap_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_snmptt` WRITE;
/*!40000 ALTER TABLE `log_snmptt` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_snmptt` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metrics` (
  `metric_id` int(11) NOT NULL AUTO_INCREMENT,
  `index_id` int(11) DEFAULT NULL,
  `metric_name` varchar(255) DEFAULT NULL,
  `data_source_type` enum('0','1','2','3') DEFAULT NULL,
  `unit_name` varchar(32) DEFAULT NULL,
  `warn` float DEFAULT NULL,
  `crit` float DEFAULT NULL,
  `hidden` enum('0','1') DEFAULT '0',
  `min` float DEFAULT NULL,
  `max` float DEFAULT NULL,
  `locked` enum('0','1') DEFAULT NULL,
  `to_delete` int(1) DEFAULT '0',
  PRIMARY KEY (`metric_id`),
  UNIQUE KEY `index_id` (`index_id`,`metric_name`),
  KEY `index` (`index_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `metrics` WRITE;
/*!40000 ALTER TABLE `metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `metrics` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_stats` (
  `instance_id` int(11) NOT NULL,
  `stat_key` varchar(255) NOT NULL,
  `stat_value` varchar(255) NOT NULL,
  `stat_label` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `nagios_stats` WRITE;
/*!40000 ALTER TABLE `nagios_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_stats` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rebuild` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `index_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `centreon_instance` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `rebuild` WRITE;
/*!40000 ALTER TABLE `rebuild` DISABLE KEYS */;
/*!40000 ALTER TABLE `rebuild` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicestateevents` (
  `servicestateevent_id` int(11) NOT NULL AUTO_INCREMENT,
  `end_time` int(11) DEFAULT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `start_time` int(11) NOT NULL,
  `state` tinyint(11) NOT NULL,
  `last_update` tinyint(4) NOT NULL DEFAULT '0',
  `in_downtime` tinyint(4) NOT NULL,
  `ack_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`servicestateevent_id`),
  UNIQUE KEY `host_id` (`host_id`,`service_id`,`start_time`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `servicestateevents` WRITE;
/*!40000 ALTER TABLE `servicestateevents` DISABLE KEYS */;
/*!40000 ALTER TABLE `servicestateevents` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `lineRead` int(11) DEFAULT NULL,
  `valueReccorded` int(11) DEFAULT NULL,
  `last_insert_duration` int(11) DEFAULT NULL,
  `average_duration` int(11) DEFAULT NULL,
  `last_nb_line` int(11) DEFAULT NULL,
  `cpt` int(11) DEFAULT NULL,
  `last_restart` int(11) DEFAULT NULL,
  `average` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `statistics` WRITE;
/*!40000 ALTER TABLE `statistics` DISABLE KEYS */;
INSERT INTO `statistics` VALUES (1,0,1,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `statistics` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

