
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
  `group_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  UNIQUE KEY (`group_id`,`host_id`,`service_id`),
  KEY `index1` (`host_id`,`service_id`,`group_id`)
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
  `archive_log` enum('0','1') NOT NULL DEFAULT '0',
  `archive_retention` int(11) DEFAULT '31',
  `reporting_retention` int(11) DEFAULT '365',
  `nagios_log_file` varchar(255) DEFAULT NULL,
  `last_line_read` int(11) DEFAULT '31',
  `audit_log_option` enum('0','1') NOT NULL DEFAULT '1',
  `len_storage_downtimes` int(11) DEFAULT NULL,
  `len_storage_comments` int(11) DEFAULT NULL,
  `audit_log_retention` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (1,'@centreon_varlib@/metrics/','@centreon_varlib@/status/','@centreon_varlib@/nagios-perf/',180,365,'1',10,360,2,NULL,'1',31,365,'@monitoring_varlog@/centengine.log.log',0,'1', 0, 0, 0);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `data_stats_yearly` WRITE;
/*!40000 ALTER TABLE `data_stats_yearly` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_stats_yearly` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `index_data` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `rrd_retention` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_service_unique_id` (`host_id`,`service_id`),
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `host_id` (`host_id`),
  KEY `service_id` (`service_id`),
  KEY `must_be_rebuild` (`must_be_rebuild`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `index_data` WRITE;
/*!40000 ALTER TABLE `index_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `index_data` ENABLE KEYS */;
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
  KEY `log_contact_id` (`log_contact_id`),
  KEY `action_log_date` (`action_log_date`),
  KEY `action_object_date` (`object_type`,`action_log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `field_value` text NOT NULL,
  `action_log_id` int(11) NOT NULL,
  PRIMARY KEY (`modification_id`),
  KEY `action_log_id` (`action_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_action_modification` WRITE;
/*!40000 ALTER TABLE `log_action_modification` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_action_modification` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_archive_last_status` WRITE;
/*!40000 ALTER TABLE `log_archive_last_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_archive_last_status` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metrics` (
  `metric_id` int(11) NOT NULL AUTO_INCREMENT,
  `index_id` BIGINT UNSIGNED DEFAULT NULL,
  `metric_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `data_source_type` enum('0','1','2','3') DEFAULT NULL,
  `unit_name` varchar(32) DEFAULT NULL,
  `current_value` float DEFAULT NULL,
  `warn` float DEFAULT NULL,
  `warn_low` float DEFAULT NULL,
  `warn_threshold_mode` boolean NULL,
  `crit` float DEFAULT NULL,
  `crit_low` float DEFAULT NULL,
  `crit_threshold_mode` boolean NULL,
  `hidden` enum('0','1') DEFAULT '0',
  `min` float DEFAULT NULL,
  `max` float DEFAULT NULL,
  `locked` enum('0','1') DEFAULT NULL,
  `to_delete` int(1) DEFAULT '0',
  PRIMARY KEY (`metric_id`),
  UNIQUE KEY `index_id` (`index_id`,`metric_name`),
  KEY `index` (`index_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `nagios_stats` WRITE;
/*!40000 ALTER TABLE `nagios_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_stats` ENABLE KEYS */;
UNLOCK TABLES;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_traps` (
  `trap_id` int(11) NOT NULL AUTO_INCREMENT,
  `trap_time` int(11) DEFAULT NULL,
  `timeout` enum('0','1') DEFAULT '0' DEFAULT NULL,
  `host_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `agent_host_name` varchar(255) DEFAULT NULL,
  `agent_ip_address` varchar(255) DEFAULT NULL,
  `trap_oid` varchar(512) DEFAULT NULL,
  `trap_name` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `severity_id` int(11) DEFAULT NULL,
  `severity_name` varchar(255) DEFAULT NULL,
  `output_message` varchar(2048) DEFAULT NULL,
  KEY `trap_id` (`trap_id`),
  KEY `trap_time` (`trap_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_traps` WRITE;
/*!40000 ALTER TABLE `log_traps` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_traps` ENABLE KEYS */;
UNLOCK TABLES;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_traps_args` (
  `fk_log_traps` int(11) NOT NULL,
  `arg_number` int(11) DEFAULT NULL,
  `arg_oid` varchar(255) DEFAULT NULL,
  `arg_value` varchar(255) DEFAULT NULL,
  `trap_time` int(11) DEFAULT NULL,
  KEY `fk_log_traps` (`fk_log_traps`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log_traps_args` WRITE;
/*!40000 ALTER TABLE `log_traps_args` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_traps_args` ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE `severities` (
  `severity_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` bigint(20) unsigned NOT NULL,
  `type` tinyint(4) unsigned NOT NULL COMMENT '0=service, 1=host',
  `name` varchar(255) NOT NULL,
  `level` int(11) unsigned NOT NULL,
  `icon_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`severity_id`),
  UNIQUE KEY `severities_id_type_uindex` (`id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `resources` (
  `resource_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '0=service, 1=host',
  `status` tinyint(3) unsigned DEFAULT NULL COMMENT 'service: 0=OK, 1=WARNING, 2=CRITICAL, 3=UNKNOWN, 4=PENDING\nhost: 0=UP, 1=DOWN, 2=UNREACHABLE, 4=PENDING',
  `status_ordered` tinyint(3) unsigned DEFAULT NULL COMMENT '0=OK=UP\n1=PENDING\n2=UNKNOWN=UNREACHABLE\n3=WARNING\n4=CRITICAL=DOWN',
  `in_downtime` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `acknowledged` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `status_confirmed` tinyint(1) DEFAULT NULL COMMENT '0=FALSE=SOFT\n1=TRUE=HARD',
  `check_attempts` tinyint(3) unsigned DEFAULT NULL,
  `max_check_attempts` tinyint(3) unsigned DEFAULT NULL,
  `poller_id` bigint(20) unsigned NOT NULL,
  `severity_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `notes_url` varchar(255) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `has_graph` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `passive_checks_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `active_checks_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `last_check_type` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0=active check, 1=passive check',
  `last_check` bigint(20) unsigned DEFAULT NULL COMMENT 'the last check timestamp',
  `last_status_change` bigint(20) unsigned DEFAULT NULL COMMENT 'the last status change timestamp',
  `output` text DEFAULT NULL,
  PRIMARY KEY (`resource_id`),
  UNIQUE KEY `resources_id_parent_id_type_uindex` (`id`,`parent_id`,`type`),
  KEY `resources_severities_severity_id_fk` (`severity_id`),
  CONSTRAINT `resources_severities_severity_id_fk` FOREIGN KEY (`severity_id`) REFERENCES `severities` (`severity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tags` (
  `tag_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` bigint(20) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '0=servicegroup, 1=hostgroup, 2=servicecategory, 3=hostcategory',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tags_id_type_uindex` (`id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `resources_tags` (
  `tag_id` bigint(20) unsigned NOT NULL,
  `resource_id` bigint(20) unsigned NOT NULL,
  KEY `resources_tags_resources_resource_id_fk` (`resource_id`),
  KEY `resources_tags_tag_id_fk` (`tag_id`),
  CONSTRAINT `resources_tags_resources_resource_id_fk` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resources_tags_tag_id_fk` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

