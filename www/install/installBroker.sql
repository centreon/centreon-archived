
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

LOCK TABLES `acknowledgements` WRITE;
/*!40000 ALTER TABLE `acknowledgements` DISABLE KEYS */;
/*!40000 ALTER TABLE `acknowledgements` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `customvariables` WRITE;
/*!40000 ALTER TABLE `customvariables` DISABLE KEYS */;
/*!40000 ALTER TABLE `customvariables` ENABLE KEYS */;
UNLOCK TABLES;
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
  `started` tinyint(1) DEFAULT NULL,
  `triggered_by` int(11) DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`downtime_id`),
  UNIQUE KEY `entry_time` (`entry_time`,`host_id`,`service_id`),
  KEY `host_id` (`host_id`),
  KEY `instance_id` (`instance_id`),
  KEY `entry_time_2` (`entry_time`),
  CONSTRAINT `downtimes_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `downtimes_ibfk_2` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `downtimes` WRITE;
/*!40000 ALTER TABLE `downtimes` DISABLE KEYS */;
/*!40000 ALTER TABLE `downtimes` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `eventhandlers` WRITE;
/*!40000 ALTER TABLE `eventhandlers` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventhandlers` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `flappingstatuses` WRITE;
/*!40000 ALTER TABLE `flappingstatuses` DISABLE KEYS */;
/*!40000 ALTER TABLE `flappingstatuses` ENABLE KEYS */;
UNLOCK TABLES;
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
  PRIMARY KEY (`hostgroup_id`),
  UNIQUE KEY `name` (`name`,`instance_id`),
  KEY `instance_id` (`instance_id`),
  CONSTRAINT `hostgroups_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `hostgroups` WRITE;
/*!40000 ALTER TABLE `hostgroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `hostgroups` ENABLE KEYS */;
UNLOCK TABLES;
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
  CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `hosts` WRITE;
/*!40000 ALTER TABLE `hosts` DISABLE KEYS */;
/*!40000 ALTER TABLE `hosts` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `hosts_hostgroups` WRITE;
/*!40000 ALTER TABLE `hosts_hostgroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `hosts_hostgroups` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `hosts_hosts_dependencies` WRITE;
/*!40000 ALTER TABLE `hosts_hosts_dependencies` DISABLE KEYS */;
/*!40000 ALTER TABLE `hosts_hosts_dependencies` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `hosts_hosts_parents` WRITE;
/*!40000 ALTER TABLE `hosts_hosts_parents` DISABLE KEYS */;
/*!40000 ALTER TABLE `hosts_hosts_parents` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `hoststateevents` WRITE;
/*!40000 ALTER TABLE `hoststateevents` DISABLE KEYS */;
/*!40000 ALTER TABLE `hoststateevents` ENABLE KEYS */;
UNLOCK TABLES;
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
  PRIMARY KEY (`instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `instances` WRITE;
/*!40000 ALTER TABLE `instances` DISABLE KEYS */;
/*!40000 ALTER TABLE `instances` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `issues` WRITE;
/*!40000 ALTER TABLE `issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `issues` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `issues_issues_parents` WRITE;
/*!40000 ALTER TABLE `issues_issues_parents` DISABLE KEYS */;
/*!40000 ALTER TABLE `issues_issues_parents` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schemaversion` (
  `software` varchar(128) NOT NULL,
  `version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `schemaversion` WRITE;
/*!40000 ALTER TABLE `schemaversion` DISABLE KEYS */;
INSERT INTO `schemaversion` VALUES ('centreon-broker',1);
/*!40000 ALTER TABLE `schemaversion` ENABLE KEYS */;
UNLOCK TABLES;
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
  PRIMARY KEY (`servicegroup_id`),
  KEY `instance_id` (`instance_id`),
  CONSTRAINT `servicegroups_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instances` (`instance_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `servicegroups` WRITE;
/*!40000 ALTER TABLE `servicegroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `servicegroups` ENABLE KEYS */;
UNLOCK TABLES;
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
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `services_servicegroups` WRITE;
/*!40000 ALTER TABLE `services_servicegroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `services_servicegroups` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `services_services_dependencies` WRITE;
/*!40000 ALTER TABLE `services_services_dependencies` DISABLE KEYS */;
/*!40000 ALTER TABLE `services_services_dependencies` ENABLE KEYS */;
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

