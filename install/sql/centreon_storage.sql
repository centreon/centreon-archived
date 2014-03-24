-- MySQL dump 10.14  Distrib 5.5.31-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: centreon_storage
-- ------------------------------------------------------
-- Server version	5.5.31-MariaDB

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

--
-- Table structure for table `acknowledgements`
--

DROP TABLE IF EXISTS `acknowledgements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acknowledgements` (
  `acknowledgement_id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_time` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `author` varchar(64) DEFAULT NULL,
  `comment_data` varchar(255) DEFAULT NULL,
  `deletion_time` int(11) DEFAULT NULL,
  `instance_id` int(11) DEFAULT NULL,
  `notify_contacts` tinyint(1) DEFAULT NULL,
  `persistent_comment` tinyint(1) DEFAULT NULL,
  `state` smallint(6) DEFAULT NULL,
  `sticky` tinyint(1) DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`acknowledgement_id`),
  UNIQUE KEY `entry_time` (`entry_time`,`host_id`,`service_id`),
  KEY `host_id` (`host_id`),
  KEY `instance_id` (`instance_id`),
  KEY `entry_time_2` (`entry_time`),
  CONSTRAINT `acknowledgements_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `acknowledgements_ibfk_2` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `centreon_acl`
--

DROP TABLE IF EXISTS `centreon_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `centreon_acl` (
  `host_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  KEY `group_id_by_id` (`host_id`,`group_id`,`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_time` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `author` varchar(64) DEFAULT NULL,
  `data` text,
  `deletion_time` int(11) DEFAULT NULL,
  `entry_type` smallint(6) DEFAULT NULL,
  `expire_time` int(11) DEFAULT NULL,
  `expires` tinyint(1) DEFAULT NULL,
  `instance_id` int(11) DEFAULT NULL,
  `internal_id` int(11) NOT NULL,
  `persistent` tinyint(1) DEFAULT NULL,
  `source` smallint(6) DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`comment_id`),
  UNIQUE KEY `entry_time` (`entry_time`,`host_id`,`service_id`),
  KEY `internal_id` (`internal_id`),
  KEY `host_id` (`host_id`),
  KEY `instance_id` (`instance_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customvariables`
--

DROP TABLE IF EXISTS `customvariables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customvariables` (
  `customvariable_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `default_value` varchar(255) DEFAULT NULL,
  `modified` tinyint(1) DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`customvariable_id`),
  UNIQUE KEY `host_id` (`host_id`,`name`,`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=355 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_bin`
--

DROP TABLE IF EXISTS `data_bin`;
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

--
-- Table structure for table `downtimes`
--

DROP TABLE IF EXISTS `downtimes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downtimes` (
  `downtime_id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_time` int(11) DEFAULT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `author` varchar(64) DEFAULT NULL,
  `cancelled` tinyint(1) DEFAULT NULL,
  `comment_data` text,
  `deletion_time` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `end_time` int(11) DEFAULT NULL,
  `fixed` tinyint(1) DEFAULT NULL,
  `instance_id` int(11) DEFAULT NULL,
  `internal_id` int(11) DEFAULT NULL,
  `start_time` int(11) DEFAULT NULL,
  `actual_start_time` int(11) DEFAULT NULL,
  `actual_end_time` int(11) DEFAULT NULL,
  `started` tinyint(1) DEFAULT NULL,
  `triggered_by` int(11) DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`downtime_id`),
  UNIQUE KEY `entry_time` (`entry_time`,`host_id`,`service_id`),
  KEY `host_id` (`host_id`),
  KEY `instance_id` (`instance_id`),
  KEY `entry_time_2` (`entry_time`),
  KEY `downtimeManager_hostList` (`host_id`,`start_time`),
  CONSTRAINT `downtimes_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `downtimes_ibfk_2` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eventhandlers`
--

DROP TABLE IF EXISTS `eventhandlers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventhandlers` (
  `eventhandler_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `start_time` int(11) DEFAULT NULL,
  `command_args` varchar(255) DEFAULT NULL,
  `command_line` varchar(255) DEFAULT NULL,
  `early_timeout` smallint(6) DEFAULT NULL,
  `end_time` int(11) DEFAULT NULL,
  `execution_time` double DEFAULT NULL,
  `output` varchar(255) DEFAULT NULL,
  `return_code` smallint(6) DEFAULT NULL,
  `state` smallint(6) DEFAULT NULL,
  `state_type` smallint(6) DEFAULT NULL,
  `timeout` smallint(6) DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`eventhandler_id`),
  UNIQUE KEY `host_id` (`host_id`,`service_id`,`start_time`),
  CONSTRAINT `eventhandlers_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `flappingstatuses`
--

DROP TABLE IF EXISTS `flappingstatuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flappingstatuses` (
  `flappingstatus_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `event_time` int(11) DEFAULT NULL,
  `comment_time` int(11) DEFAULT NULL,
  `event_type` smallint(6) DEFAULT NULL,
  `high_threshold` double DEFAULT NULL,
  `internal_comment_id` int(11) DEFAULT NULL,
  `low_threshold` double DEFAULT NULL,
  `percent_state_change` double DEFAULT NULL,
  `reason_type` smallint(6) DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`flappingstatus_id`),
  UNIQUE KEY `host_id` (`host_id`,`service_id`,`event_time`),
  CONSTRAINT `flappingstatuses_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hostgroups`
--

DROP TABLE IF EXISTS `hostgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostgroups` (
  `hostgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `action_url` varchar(160) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `notes` varchar(160) DEFAULT NULL,
  `notes_url` varchar(160) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`hostgroup_id`),
  UNIQUE KEY `name` (`name`,`instance_id`),
  KEY `instance_id` (`instance_id`),
  CONSTRAINT `hostgroups_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hosts`
--

DROP TABLE IF EXISTS `hosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hosts` (
  `host_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `instance_id` int(11) NOT NULL,
  `acknowledged` tinyint(1) DEFAULT NULL,
  `acknowledgement_type` smallint(6) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `active_checks` tinyint(1) DEFAULT NULL,
  `address` varchar(75) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `check_attempt` smallint(6) DEFAULT NULL,
  `check_command` text,
  `check_freshness` tinyint(1) DEFAULT NULL,
  `check_interval` double DEFAULT NULL,
  `check_period` varchar(75) DEFAULT NULL,
  `check_type` smallint(6) DEFAULT NULL,
  `checked` tinyint(1) DEFAULT NULL,
  `command_line` text,
  `default_active_checks` tinyint(1) DEFAULT NULL,
  `default_event_handler_enabled` tinyint(1) DEFAULT NULL,
  `default_failure_prediction` tinyint(1) DEFAULT NULL,
  `default_flap_detection` tinyint(1) DEFAULT NULL,
  `default_notify` tinyint(1) DEFAULT NULL,
  `default_passive_checks` tinyint(1) DEFAULT NULL,
  `default_process_perfdata` tinyint(1) DEFAULT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `event_handler` varchar(255) DEFAULT NULL,
  `event_handler_enabled` tinyint(1) DEFAULT NULL,
  `execution_time` double DEFAULT NULL,
  `failure_prediction` tinyint(1) DEFAULT NULL,
  `first_notification_delay` double DEFAULT NULL,
  `flap_detection` tinyint(1) DEFAULT NULL,
  `flap_detection_on_down` tinyint(1) DEFAULT NULL,
  `flap_detection_on_unreachable` tinyint(1) DEFAULT NULL,
  `flap_detection_on_up` tinyint(1) DEFAULT NULL,
  `flapping` tinyint(1) DEFAULT NULL,
  `freshness_threshold` double DEFAULT NULL,
  `high_flap_threshold` double DEFAULT NULL,
  `icon_image` varchar(255) DEFAULT NULL,
  `icon_image_alt` varchar(255) DEFAULT NULL,
  `last_check` int(11) DEFAULT NULL,
  `last_hard_state` smallint(6) DEFAULT NULL,
  `last_hard_state_change` int(11) DEFAULT NULL,
  `last_notification` int(11) DEFAULT NULL,
  `last_state_change` int(11) DEFAULT NULL,
  `last_time_down` int(11) DEFAULT NULL,
  `last_time_unreachable` int(11) DEFAULT NULL,
  `last_time_up` int(11) DEFAULT NULL,
  `last_update` int(11) DEFAULT NULL,
  `latency` double DEFAULT NULL,
  `low_flap_threshold` double DEFAULT NULL,
  `max_check_attempts` smallint(6) DEFAULT NULL,
  `modified_attributes` int(11) DEFAULT NULL,
  `next_check` int(11) DEFAULT NULL,
  `next_host_notification` int(11) DEFAULT NULL,
  `no_more_notifications` tinyint(1) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `notes_url` varchar(255) DEFAULT NULL,
  `notification_interval` double DEFAULT NULL,
  `notification_number` smallint(6) DEFAULT NULL,
  `notification_period` varchar(75) DEFAULT NULL,
  `notify` tinyint(1) DEFAULT NULL,
  `notify_on_down` tinyint(1) DEFAULT NULL,
  `notify_on_downtime` tinyint(1) DEFAULT NULL,
  `notify_on_flapping` tinyint(1) DEFAULT NULL,
  `notify_on_recovery` tinyint(1) DEFAULT NULL,
  `notify_on_unreachable` tinyint(1) DEFAULT NULL,
  `obsess_over_host` tinyint(1) DEFAULT NULL,
  `output` text,
  `passive_checks` tinyint(1) DEFAULT NULL,
  `percent_state_change` double DEFAULT NULL,
  `perfdata` text,
  `process_perfdata` tinyint(1) DEFAULT NULL,
  `retain_nonstatus_information` tinyint(1) DEFAULT NULL,
  `retain_status_information` tinyint(1) DEFAULT NULL,
  `retry_interval` double DEFAULT NULL,
  `scheduled_downtime_depth` smallint(6) DEFAULT NULL,
  `should_be_scheduled` tinyint(1) DEFAULT NULL,
  `stalk_on_down` tinyint(1) DEFAULT NULL,
  `stalk_on_unreachable` tinyint(1) DEFAULT NULL,
  `stalk_on_up` tinyint(1) DEFAULT NULL,
  `state` smallint(6) DEFAULT NULL,
  `state_type` smallint(6) DEFAULT NULL,
  `statusmap_image` varchar(255) DEFAULT NULL,
  UNIQUE KEY `host_id` (`host_id`),
  KEY `instance_id` (`instance_id`),
  KEY `host_name` (`name`),
  CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hosts_hostgroups`
--

DROP TABLE IF EXISTS `hosts_hostgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hosts_hostgroups` (
  `host_id` int(11) NOT NULL,
  `hostgroup_id` int(11) NOT NULL,
  UNIQUE KEY `host_id` (`host_id`,`hostgroup_id`),
  KEY `hostgroup_id` (`hostgroup_id`),
  CONSTRAINT `hosts_hostgroups_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `hosts_hostgroups_ibfk_2` FOREIGN KEY (`hostgroup_id`) REFERENCES `hostgroups` (`hostgroup_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hosts_hosts_dependencies`
--

DROP TABLE IF EXISTS `hosts_hosts_dependencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hosts_hosts_dependencies` (
  `dependent_host_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `dependency_period` varchar(75) DEFAULT NULL,
  `execution_failure_options` varchar(15) DEFAULT NULL,
  `inherits_parent` tinyint(1) DEFAULT NULL,
  `notification_failure_options` varchar(15) DEFAULT NULL,
  UNIQUE KEY `dependent_host_id` (`dependent_host_id`,`host_id`),
  KEY `host_id` (`host_id`),
  CONSTRAINT `hosts_hosts_dependencies_ibfk_1` FOREIGN KEY (`dependent_host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `hosts_hosts_dependencies_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hosts_hosts_parents`
--

DROP TABLE IF EXISTS `hosts_hosts_parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hosts_hosts_parents` (
  `child_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  UNIQUE KEY `child_id` (`child_id`,`parent_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `hosts_hosts_parents_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `hosts_hosts_parents_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hoststateevents`
--

DROP TABLE IF EXISTS `hoststateevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hoststateevents` (
  `hoststateevent_id` int(11) NOT NULL AUTO_INCREMENT,
  `end_time` int(11) DEFAULT NULL,
  `host_id` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `state` tinyint(11) NOT NULL,
  `last_update` tinyint(4) NOT NULL DEFAULT '0',
  `in_downtime` tinyint(4) NOT NULL,
  `ack_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`hoststateevent_id`),
  UNIQUE KEY `host_id` (`host_id`,`start_time`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `index_data`
--

DROP TABLE IF EXISTS `index_data`;
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
  `rrd_retention` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_service_unique_id` (`host_id`,`service_id`),
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `host_id` (`host_id`),
  KEY `service_id` (`service_id`),
  KEY `must_be_rebuild` (`must_be_rebuild`),
  KEY `trashed` (`trashed`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `instances`
--

DROP TABLE IF EXISTS `instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instances` (
  `instance_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'localhost',
  `active_host_checks` tinyint(1) DEFAULT NULL,
  `active_service_checks` tinyint(1) DEFAULT NULL,
  `address` varchar(128) DEFAULT NULL,
  `check_hosts_freshness` tinyint(1) DEFAULT NULL,
  `check_services_freshness` tinyint(1) DEFAULT NULL,
  `daemon_mode` tinyint(1) DEFAULT NULL,
  `description` varchar(128) DEFAULT NULL,
  `end_time` int(11) DEFAULT NULL,
  `engine` varchar(64) DEFAULT NULL,
  `event_handlers` tinyint(1) DEFAULT NULL,
  `failure_prediction` tinyint(1) DEFAULT NULL,
  `flap_detection` tinyint(1) DEFAULT NULL,
  `global_host_event_handler` text,
  `global_service_event_handler` text,
  `last_alive` int(11) DEFAULT NULL,
  `last_command_check` int(11) DEFAULT NULL,
  `last_log_rotation` int(11) DEFAULT NULL,
  `modified_host_attributes` int(11) DEFAULT NULL,
  `modified_service_attributes` int(11) DEFAULT NULL,
  `notifications` tinyint(1) DEFAULT NULL,
  `obsess_over_hosts` tinyint(1) DEFAULT NULL,
  `obsess_over_services` tinyint(1) DEFAULT NULL,
  `passive_host_checks` tinyint(1) DEFAULT NULL,
  `passive_service_checks` tinyint(1) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `process_perfdata` tinyint(1) DEFAULT NULL,
  `running` tinyint(1) DEFAULT NULL,
  `start_time` int(11) DEFAULT NULL,
  `version` varchar(16) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `issues`
--

DROP TABLE IF EXISTS `issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issues` (
  `issue_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `start_time` int(11) NOT NULL,
  `ack_time` int(11) DEFAULT NULL,
  `end_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`issue_id`),
  UNIQUE KEY `host_id` (`host_id`,`service_id`,`start_time`),
  KEY `start_time` (`start_time`),
  CONSTRAINT `issues_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `issues_issues_parents`
--

DROP TABLE IF EXISTS `issues_issues_parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issues_issues_parents` (
  `child_id` int(11) NOT NULL,
  `end_time` int(11) DEFAULT NULL,
  `start_time` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  KEY `child_id` (`child_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `issues_issues_parents_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `issues` (`issue_id`) ON DELETE CASCADE,
  CONSTRAINT `issues_issues_parents_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `issues` (`issue_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_action`
--

DROP TABLE IF EXISTS `log_action`;
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
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_action_modification`
--

DROP TABLE IF EXISTS `log_action_modification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_action_modification` (
  `modification_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(255) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  `action_log_id` int(11) NOT NULL,
  PRIMARY KEY (`modification_id`),
  KEY `action_log_id` (`action_log_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1602 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_archive_host`
--

DROP TABLE IF EXISTS `log_archive_host`;
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
) ENGINE=MyISAM AUTO_INCREMENT=656 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_archive_last_status`
--

DROP TABLE IF EXISTS `log_archive_last_status`;
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

--
-- Table structure for table `log_archive_service`
--

DROP TABLE IF EXISTS `log_archive_service`;
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
) ENGINE=MyISAM AUTO_INCREMENT=5201 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_traps`
--

DROP TABLE IF EXISTS `log_traps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_traps` (
  `trap_id` int(11) NOT NULL AUTO_INCREMENT,
  `trap_time` int(11) DEFAULT NULL,
  `timeout` enum('0','1') DEFAULT NULL,
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

--
-- Table structure for table `log_traps_args`
--

DROP TABLE IF EXISTS `log_traps_args`;
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

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) DEFAULT NULL,
  `host_id` int(11) DEFAULT NULL,
  `host_name` varchar(255) DEFAULT NULL,
  `instance_name` varchar(255) NOT NULL,
  `issue_id` int(11) DEFAULT NULL,
  `msg_type` tinyint(4) DEFAULT NULL,
  `notification_cmd` varchar(255) DEFAULT NULL,
  `notification_contact` varchar(255) DEFAULT NULL,
  `output` text,
  `retry` int(11) DEFAULT NULL,
  `service_description` varchar(255) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `host_name` (`host_name`(64)),
  KEY `service_description` (`service_description`(64)),
  KEY `status` (`status`),
  KEY `instance_name` (`instance_name`),
  KEY `ctime` (`ctime`),
  KEY `rq1` (`host_id`,`service_id`,`msg_type`,`status`,`ctime`),
  KEY `rq2` (`host_id`,`msg_type`,`status`,`ctime`),
  KEY `host_id` (`host_id`,`service_id`,`msg_type`,`ctime`,`status`),
  KEY `host_id_2` (`host_id`,`msg_type`,`ctime`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=4472 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `metrics`
--

DROP TABLE IF EXISTS `metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metrics` (
  `metric_id` int(11) NOT NULL AUTO_INCREMENT,
  `index_id` int(11) DEFAULT NULL,
  `metric_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `data_source_type` enum('0','1','2','3') DEFAULT NULL,
  `unit_name` varchar(32) DEFAULT NULL,
  `current_value` float DEFAULT NULL,
  `warn` float DEFAULT NULL,
  `warn_low` float DEFAULT NULL,
  `warn_threshold_mode` enum('0','1') DEFAULT NULL,
  `crit` float DEFAULT NULL,
  `crit_low` float DEFAULT NULL,
  `crit_threshold_mode` enum('0','1') DEFAULT NULL,
  `hidden` enum('0','1') DEFAULT '0',
  `min` float DEFAULT NULL,
  `max` float DEFAULT NULL,
  `locked` enum('0','1') DEFAULT NULL,
  `to_delete` int(1) DEFAULT '0',
  PRIMARY KEY (`metric_id`),
  UNIQUE KEY `index_id` (`index_id`,`metric_name`),
  KEY `index` (`index_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `module_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) NOT NULL,
  `args` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `loaded` tinyint(1) DEFAULT NULL,
  `should_be_loaded` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`module_id`),
  KEY `instance_id` (`instance_id`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=341 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `start_time` int(11) DEFAULT NULL,
  `ack_author` varchar(255) DEFAULT NULL,
  `ack_data` text,
  `command_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contacts_notified` tinyint(1) DEFAULT NULL,
  `end_time` int(11) DEFAULT NULL,
  `escalated` tinyint(1) DEFAULT NULL,
  `output` text,
  `reason_type` int(11) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  UNIQUE KEY `host_id` (`host_id`,`service_id`,`start_time`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schemaversion`
--

DROP TABLE IF EXISTS `schemaversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schemaversion` (
  `software` varchar(128) NOT NULL,
  `version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servicegroups`
--

DROP TABLE IF EXISTS `servicegroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicegroups` (
  `servicegroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `action_url` varchar(160) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `notes` varchar(160) DEFAULT NULL,
  `notes_url` varchar(160) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`servicegroup_id`),
  KEY `instance_id` (`instance_id`),
  CONSTRAINT `servicegroups_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `host_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `service_id` int(11) NOT NULL,
  `acknowledged` tinyint(1) DEFAULT NULL,
  `acknowledgement_type` smallint(6) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `active_checks` tinyint(1) DEFAULT NULL,
  `check_attempt` smallint(6) DEFAULT NULL,
  `check_command` text,
  `check_freshness` tinyint(1) DEFAULT NULL,
  `check_interval` double DEFAULT NULL,
  `check_period` varchar(75) DEFAULT NULL,
  `check_type` smallint(6) DEFAULT NULL,
  `checked` tinyint(1) DEFAULT NULL,
  `command_line` text,
  `default_active_checks` tinyint(1) DEFAULT NULL,
  `default_event_handler_enabled` tinyint(1) DEFAULT NULL,
  `default_failure_prediction` tinyint(1) DEFAULT NULL,
  `default_flap_detection` tinyint(1) DEFAULT NULL,
  `default_notify` tinyint(1) DEFAULT NULL,
  `default_passive_checks` tinyint(1) DEFAULT NULL,
  `default_process_perfdata` tinyint(1) DEFAULT NULL,
  `display_name` varchar(160) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `event_handler` varchar(255) DEFAULT NULL,
  `event_handler_enabled` tinyint(1) DEFAULT NULL,
  `execution_time` double DEFAULT NULL,
  `failure_prediction` tinyint(1) DEFAULT NULL,
  `failure_prediction_options` varchar(64) DEFAULT NULL,
  `first_notification_delay` double DEFAULT NULL,
  `flap_detection` tinyint(1) DEFAULT NULL,
  `flap_detection_on_critical` tinyint(1) DEFAULT NULL,
  `flap_detection_on_ok` tinyint(1) DEFAULT NULL,
  `flap_detection_on_unknown` tinyint(1) DEFAULT NULL,
  `flap_detection_on_warning` tinyint(1) DEFAULT NULL,
  `flapping` tinyint(1) DEFAULT NULL,
  `freshness_threshold` double DEFAULT NULL,
  `high_flap_threshold` double DEFAULT NULL,
  `icon_image` varchar(255) DEFAULT NULL,
  `icon_image_alt` varchar(255) DEFAULT NULL,
  `last_check` int(11) DEFAULT NULL,
  `last_hard_state` smallint(6) DEFAULT NULL,
  `last_hard_state_change` int(11) DEFAULT NULL,
  `last_notification` int(11) DEFAULT NULL,
  `last_state_change` int(11) DEFAULT NULL,
  `last_time_critical` int(11) DEFAULT NULL,
  `last_time_ok` int(11) DEFAULT NULL,
  `last_time_unknown` int(11) DEFAULT NULL,
  `last_time_warning` int(11) DEFAULT NULL,
  `last_update` int(11) DEFAULT NULL,
  `latency` double DEFAULT NULL,
  `low_flap_threshold` double DEFAULT NULL,
  `max_check_attempts` smallint(6) DEFAULT NULL,
  `modified_attributes` int(11) DEFAULT NULL,
  `next_check` int(11) DEFAULT NULL,
  `next_notification` int(11) DEFAULT NULL,
  `no_more_notifications` tinyint(1) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `notes_url` varchar(255) DEFAULT NULL,
  `notification_interval` double DEFAULT NULL,
  `notification_number` smallint(6) DEFAULT NULL,
  `notification_period` varchar(75) DEFAULT NULL,
  `notify` tinyint(1) DEFAULT NULL,
  `notify_on_critical` tinyint(1) DEFAULT NULL,
  `notify_on_downtime` tinyint(1) DEFAULT NULL,
  `notify_on_flapping` tinyint(1) DEFAULT NULL,
  `notify_on_recovery` tinyint(1) DEFAULT NULL,
  `notify_on_unknown` tinyint(1) DEFAULT NULL,
  `notify_on_warning` tinyint(1) DEFAULT NULL,
  `obsess_over_service` tinyint(1) DEFAULT NULL,
  `output` text,
  `passive_checks` tinyint(1) DEFAULT NULL,
  `percent_state_change` double DEFAULT NULL,
  `perfdata` text,
  `process_perfdata` tinyint(1) DEFAULT NULL,
  `retain_nonstatus_information` tinyint(1) DEFAULT NULL,
  `retain_status_information` tinyint(1) DEFAULT NULL,
  `retry_interval` double DEFAULT NULL,
  `scheduled_downtime_depth` smallint(6) DEFAULT NULL,
  `should_be_scheduled` tinyint(1) DEFAULT NULL,
  `stalk_on_critical` tinyint(1) DEFAULT NULL,
  `stalk_on_ok` tinyint(1) DEFAULT NULL,
  `stalk_on_unknown` tinyint(1) DEFAULT NULL,
  `stalk_on_warning` tinyint(1) DEFAULT NULL,
  `state` smallint(6) DEFAULT NULL,
  `state_type` smallint(6) DEFAULT NULL,
  `volatile` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `host_id` (`host_id`,`service_id`),
  KEY `service_id` (`service_id`),
  KEY `service_description` (`description`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services_servicegroups`
--

DROP TABLE IF EXISTS `services_servicegroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services_servicegroups` (
  `host_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `servicegroup_id` int(11) NOT NULL,
  UNIQUE KEY `host_id` (`host_id`,`service_id`,`servicegroup_id`),
  KEY `servicegroup_id` (`servicegroup_id`),
  CONSTRAINT `services_servicegroups_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `services_servicegroups_ibfk_2` FOREIGN KEY (`servicegroup_id`) REFERENCES `servicegroups` (`servicegroup_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services_services_dependencies`
--

DROP TABLE IF EXISTS `services_services_dependencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services_services_dependencies` (
  `dependent_host_id` int(11) NOT NULL,
  `dependent_service_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `dependency_period` varchar(75) DEFAULT NULL,
  `execution_failure_options` varchar(15) DEFAULT NULL,
  `inherits_parent` tinyint(1) DEFAULT NULL,
  `notification_failure_options` varchar(15) DEFAULT NULL,
  UNIQUE KEY `dependent_host_id` (`dependent_host_id`,`dependent_service_id`,`host_id`,`service_id`),
  KEY `host_id` (`host_id`),
  CONSTRAINT `services_services_dependencies_ibfk_1` FOREIGN KEY (`dependent_host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `services_services_dependencies_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servicestateevents`
--

DROP TABLE IF EXISTS `servicestateevents`;
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
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-06-10 19:06:11
