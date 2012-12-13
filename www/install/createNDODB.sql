
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
  `host_name` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `service_description` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `group_id_by_name` (`group_id`,`host_name`(70),`service_description`(120)),
  KEY `group_id_by_id` (`group_id`,`host_id`,`service_id`),
  KEY `group_id_for_host` (`group_id`,`host_name`(70))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_acknowledgements` (
  `acknowledgement_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `entry_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `entry_time_usec` int(11) NOT NULL DEFAULT '0',
  `acknowledgement_type` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `state` smallint(6) NOT NULL DEFAULT '0',
  `author_name` varchar(64) NOT NULL DEFAULT '',
  `comment_data` varchar(255) NOT NULL DEFAULT '',
  `is_sticky` smallint(6) NOT NULL DEFAULT '0',
  `persistent_comment` smallint(6) NOT NULL DEFAULT '0',
  `notify_contacts` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acknowledgement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current and historical host and service acknowledgements';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_commands` (
  `command_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `command_line` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`command_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`object_id`,`config_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Command definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_commenthistory` (
  `commenthistory_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `entry_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `entry_time_usec` int(11) NOT NULL DEFAULT '0',
  `comment_type` smallint(6) NOT NULL DEFAULT '0',
  `entry_type` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `comment_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `internal_comment_id` int(11) NOT NULL DEFAULT '0',
  `author_name` varchar(64) NOT NULL DEFAULT '',
  `comment_data` varchar(255) NOT NULL DEFAULT '',
  `is_persistent` smallint(6) NOT NULL DEFAULT '0',
  `comment_source` smallint(6) NOT NULL DEFAULT '0',
  `expires` smallint(6) NOT NULL DEFAULT '0',
  `expiration_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deletion_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deletion_time_usec` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`commenthistory_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`comment_time`,`internal_comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical host and service comments';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `entry_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `entry_time_usec` int(11) NOT NULL DEFAULT '0',
  `comment_type` smallint(6) NOT NULL DEFAULT '0',
  `entry_type` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `comment_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `internal_comment_id` int(11) NOT NULL DEFAULT '0',
  `author_name` varchar(64) NOT NULL DEFAULT '',
  `comment_data` varchar(255) NOT NULL DEFAULT '',
  `is_persistent` smallint(6) NOT NULL DEFAULT '0',
  `comment_source` smallint(6) NOT NULL DEFAULT '0',
  `expires` smallint(6) NOT NULL DEFAULT '0',
  `expiration_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`comment_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`comment_time`,`internal_comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_configfiles` (
  `configfile_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `configfile_type` smallint(6) NOT NULL DEFAULT '0',
  `configfile_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`configfile_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`configfile_type`,`configfile_path`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Configuration files';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_configfilevariables` (
  `configfilevariable_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `configfile_id` int(11) NOT NULL DEFAULT '0',
  `varname` varchar(64) NOT NULL DEFAULT '',
  `varvalue` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`configfilevariable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Configuration file variables';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_conninfo` (
  `conninfo_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `agent_name` varchar(32) NOT NULL DEFAULT '',
  `agent_version` varchar(8) NOT NULL DEFAULT '',
  `disposition` varchar(16) NOT NULL DEFAULT '',
  `connect_source` varchar(16) NOT NULL DEFAULT '',
  `connect_type` varchar(16) NOT NULL DEFAULT '',
  `connect_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `disconnect_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_checkin_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bytes_processed` int(11) NOT NULL DEFAULT '0',
  `lines_processed` int(11) NOT NULL DEFAULT '0',
  `entries_processed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`conninfo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='NDO2DB daemon connection information';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contact_addresses` (
  `contact_address_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `address_number` smallint(6) NOT NULL DEFAULT '0',
  `address` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`contact_address_id`),
  UNIQUE KEY `contact_id` (`contact_id`,`address_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Contact addresses';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contact_notificationcommands` (
  `contact_notificationcommand_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `notification_type` smallint(6) NOT NULL DEFAULT '0',
  `command_object_id` int(11) NOT NULL DEFAULT '0',
  `command_args` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`contact_notificationcommand_id`),
  UNIQUE KEY `contact_id` (`contact_id`,`notification_type`,`command_object_id`,`command_args`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Contact host and service notification commands';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contactgroup_members` (
  `contactgroup_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `contactgroup_id` int(11) NOT NULL DEFAULT '0',
  `contact_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contactgroup_member_id`),
  UNIQUE KEY `instance_id` (`contactgroup_id`,`contact_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Contactgroup members';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contactgroups` (
  `contactgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `contactgroup_object_id` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`contactgroup_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`contactgroup_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Contactgroup definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contactnotificationmethods` (
  `contactnotificationmethod_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `contactnotification_id` int(11) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_time_usec` int(11) NOT NULL DEFAULT '0',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time_usec` int(11) NOT NULL DEFAULT '0',
  `command_object_id` int(11) NOT NULL DEFAULT '0',
  `command_args` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`contactnotificationmethod_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`contactnotification_id`,`start_time`,`start_time_usec`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical record of contact notification methods';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contactnotifications` (
  `contactnotification_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `notification_id` int(11) NOT NULL DEFAULT '0',
  `contact_object_id` int(11) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_time_usec` int(11) NOT NULL DEFAULT '0',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time_usec` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contactnotification_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`contact_object_id`,`start_time`,`start_time_usec`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical record of contact notifications';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contacts` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `contact_object_id` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(64) NOT NULL DEFAULT '',
  `email_address` varchar(255) NOT NULL DEFAULT '',
  `pager_address` varchar(64) NOT NULL DEFAULT '',
  `host_timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `service_timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `host_notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `service_notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `can_submit_commands` smallint(6) NOT NULL DEFAULT '0',
  `notify_service_recovery` smallint(6) NOT NULL DEFAULT '0',
  `notify_service_warning` smallint(6) NOT NULL DEFAULT '0',
  `notify_service_unknown` smallint(6) NOT NULL DEFAULT '0',
  `notify_service_critical` smallint(6) NOT NULL DEFAULT '0',
  `notify_service_flapping` smallint(6) NOT NULL DEFAULT '0',
  `notify_service_downtime` smallint(6) NOT NULL DEFAULT '0',
  `notify_host_recovery` smallint(6) NOT NULL DEFAULT '0',
  `notify_host_down` smallint(6) NOT NULL DEFAULT '0',
  `notify_host_unreachable` smallint(6) NOT NULL DEFAULT '0',
  `notify_host_flapping` smallint(6) NOT NULL DEFAULT '0',
  `notify_host_downtime` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`contact_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Contact definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contactstatus` (
  `contactstatus_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `contact_object_id` int(11) NOT NULL DEFAULT '0',
  `status_update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `host_notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `service_notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `last_host_notification` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_service_notification` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_attributes` int(11) NOT NULL DEFAULT '0',
  `modified_host_attributes` int(11) NOT NULL DEFAULT '0',
  `modified_service_attributes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contactstatus_id`),
  UNIQUE KEY `contact_object_id` (`contact_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Contact status';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_customvariables` (
  `customvariable_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `has_been_modified` smallint(6) NOT NULL DEFAULT '0',
  `varname` varchar(255) NOT NULL DEFAULT '',
  `varvalue` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`customvariable_id`),
  UNIQUE KEY `object_id_2` (`object_id`,`config_type`,`varname`),
  KEY `varname` (`varname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Custom variables';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_customvariablestatus` (
  `customvariablestatus_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `status_update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `has_been_modified` smallint(6) NOT NULL DEFAULT '0',
  `varname` varchar(255) NOT NULL DEFAULT '',
  `varvalue` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`customvariablestatus_id`),
  UNIQUE KEY `object_id_2` (`object_id`,`varname`),
  KEY `varname` (`varname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Custom variable status information';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_dbversion` (
  `name` varchar(10) NOT NULL DEFAULT '',
  `version` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_downtimehistory` (
  `downtimehistory_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `downtime_type` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `entry_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `author_name` varchar(64) NOT NULL DEFAULT '',
  `comment_data` varchar(255) NOT NULL DEFAULT '',
  `internal_downtime_id` int(11) NOT NULL DEFAULT '0',
  `triggered_by_id` int(11) NOT NULL DEFAULT '0',
  `is_fixed` smallint(6) NOT NULL DEFAULT '0',
  `duration` smallint(6) NOT NULL DEFAULT '0',
  `scheduled_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `scheduled_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `was_started` smallint(6) NOT NULL DEFAULT '0',
  `actual_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `actual_start_time_usec` int(11) NOT NULL DEFAULT '0',
  `actual_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `actual_end_time_usec` int(11) NOT NULL DEFAULT '0',
  `was_cancelled` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`downtimehistory_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`object_id`,`entry_time`,`internal_downtime_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical scheduled host and service downtime';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_eventhandlers` (
  `eventhandler_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `eventhandler_type` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `state` smallint(6) NOT NULL DEFAULT '0',
  `state_type` smallint(6) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_time_usec` int(11) NOT NULL DEFAULT '0',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time_usec` int(11) NOT NULL DEFAULT '0',
  `command_object_id` int(11) NOT NULL DEFAULT '0',
  `command_args` varchar(255) NOT NULL DEFAULT '',
  `command_line` varchar(255) NOT NULL DEFAULT '',
  `timeout` smallint(6) NOT NULL DEFAULT '0',
  `early_timeout` smallint(6) NOT NULL DEFAULT '0',
  `execution_time` double NOT NULL DEFAULT '0',
  `return_code` smallint(6) NOT NULL DEFAULT '0',
  `output` varchar(255) NOT NULL DEFAULT '',
  `long_output` varchar(8192) NOT NULL DEFAULT '',
  PRIMARY KEY (`eventhandler_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`object_id`,`start_time`,`start_time_usec`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical host and service event handlers';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_externalcommands` (
  `externalcommand_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `entry_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `command_type` smallint(6) NOT NULL DEFAULT '0',
  `command_name` varchar(128) NOT NULL DEFAULT '',
  `command_args` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`externalcommand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical record of processed external commands';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_flappinghistory` (
  `flappinghistory_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `event_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `event_time_usec` int(11) NOT NULL DEFAULT '0',
  `event_type` smallint(6) NOT NULL DEFAULT '0',
  `reason_type` smallint(6) NOT NULL DEFAULT '0',
  `flapping_type` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `percent_state_change` double NOT NULL DEFAULT '0',
  `low_threshold` double NOT NULL DEFAULT '0',
  `high_threshold` double NOT NULL DEFAULT '0',
  `comment_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `internal_comment_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`flappinghistory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current and historical record of host and service flapping';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_host_contactgroups` (
  `host_contactgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `host_id` int(11) NOT NULL DEFAULT '0',
  `contactgroup_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`host_contactgroup_id`),
  UNIQUE KEY `instance_id` (`host_id`,`contactgroup_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Host contact groups';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_host_contacts` (
  `host_contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `host_id` int(11) NOT NULL DEFAULT '0',
  `contact_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`host_contact_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`host_id`,`contact_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_host_parenthosts` (
  `host_parenthost_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `host_id` int(11) NOT NULL DEFAULT '0',
  `parent_host_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`host_parenthost_id`),
  UNIQUE KEY `instance_id` (`host_id`,`parent_host_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Parent hosts';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostchecks` (
  `hostcheck_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `host_object_id` int(11) NOT NULL DEFAULT '0',
  `check_type` smallint(6) NOT NULL DEFAULT '0',
  `is_raw_check` smallint(6) NOT NULL DEFAULT '0',
  `current_check_attempt` smallint(6) NOT NULL DEFAULT '0',
  `max_check_attempts` smallint(6) NOT NULL DEFAULT '0',
  `state` smallint(6) NOT NULL DEFAULT '0',
  `state_type` smallint(6) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_time_usec` int(11) NOT NULL DEFAULT '0',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time_usec` int(11) NOT NULL DEFAULT '0',
  `command_object_id` int(11) NOT NULL DEFAULT '0',
  `command_args` varchar(255) NOT NULL DEFAULT '',
  `command_line` varchar(255) NOT NULL DEFAULT '',
  `timeout` smallint(6) NOT NULL DEFAULT '0',
  `early_timeout` smallint(6) NOT NULL DEFAULT '0',
  `execution_time` double NOT NULL DEFAULT '0',
  `latency` double NOT NULL DEFAULT '0',
  `return_code` smallint(6) NOT NULL DEFAULT '0',
  `output` varchar(255) NOT NULL DEFAULT '',
  `long_output` varchar(8192) NOT NULL DEFAULT '',
  `perfdata` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`hostcheck_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`host_object_id`,`start_time`,`start_time_usec`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical host checks';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostdependencies` (
  `hostdependency_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `host_object_id` int(11) NOT NULL DEFAULT '0',
  `dependent_host_object_id` int(11) NOT NULL DEFAULT '0',
  `dependency_type` smallint(6) NOT NULL DEFAULT '0',
  `inherits_parent` smallint(6) NOT NULL DEFAULT '0',
  `timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `fail_on_up` smallint(6) NOT NULL DEFAULT '0',
  `fail_on_down` smallint(6) NOT NULL DEFAULT '0',
  `fail_on_unreachable` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hostdependency_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`host_object_id`,`dependent_host_object_id`,`dependency_type`,`inherits_parent`,`fail_on_up`,`fail_on_down`,`fail_on_unreachable`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Host dependency definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostescalation_contactgroups` (
  `hostescalation_contactgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `hostescalation_id` int(11) NOT NULL DEFAULT '0',
  `contactgroup_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hostescalation_contactgroup_id`),
  UNIQUE KEY `instance_id` (`hostescalation_id`,`contactgroup_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Host escalation contact groups';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostescalation_contacts` (
  `hostescalation_contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `hostescalation_id` int(11) NOT NULL DEFAULT '0',
  `contact_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hostescalation_contact_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`hostescalation_id`,`contact_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostescalations` (
  `hostescalation_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `host_object_id` int(11) NOT NULL DEFAULT '0',
  `timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `first_notification` smallint(6) NOT NULL DEFAULT '0',
  `last_notification` smallint(6) NOT NULL DEFAULT '0',
  `notification_interval` double NOT NULL DEFAULT '0',
  `escalate_on_recovery` smallint(6) NOT NULL DEFAULT '0',
  `escalate_on_down` smallint(6) NOT NULL DEFAULT '0',
  `escalate_on_unreachable` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hostescalation_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`host_object_id`,`timeperiod_object_id`,`first_notification`,`last_notification`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Host escalation definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostgroup_members` (
  `hostgroup_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `hostgroup_id` int(11) NOT NULL DEFAULT '0',
  `host_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hostgroup_member_id`),
  UNIQUE KEY `instance_id` (`hostgroup_id`,`host_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Hostgroup members';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostgroups` (
  `hostgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `hostgroup_object_id` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`hostgroup_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`hostgroup_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Hostgroup definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hosts` (
  `host_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `host_object_id` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(64) NOT NULL DEFAULT '',
  `display_name` varchar(64) NOT NULL DEFAULT '',
  `address` varchar(128) NOT NULL DEFAULT '',
  `check_command_object_id` int(11) NOT NULL DEFAULT '0',
  `check_command_args` varchar(255) NOT NULL DEFAULT '',
  `eventhandler_command_object_id` int(11) NOT NULL DEFAULT '0',
  `eventhandler_command_args` varchar(255) NOT NULL DEFAULT '',
  `notification_timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `check_timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `failure_prediction_options` varchar(64) NOT NULL DEFAULT '',
  `check_interval` double NOT NULL DEFAULT '0',
  `retry_interval` double NOT NULL DEFAULT '0',
  `max_check_attempts` smallint(6) NOT NULL DEFAULT '0',
  `first_notification_delay` double NOT NULL DEFAULT '0',
  `notification_interval` double NOT NULL DEFAULT '0',
  `notify_on_down` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_unreachable` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_recovery` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_flapping` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_downtime` smallint(6) NOT NULL DEFAULT '0',
  `stalk_on_up` smallint(6) NOT NULL DEFAULT '0',
  `stalk_on_down` smallint(6) NOT NULL DEFAULT '0',
  `stalk_on_unreachable` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_enabled` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_on_up` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_on_down` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_on_unreachable` smallint(6) NOT NULL DEFAULT '0',
  `low_flap_threshold` double NOT NULL DEFAULT '0',
  `high_flap_threshold` double NOT NULL DEFAULT '0',
  `process_performance_data` smallint(6) NOT NULL DEFAULT '0',
  `freshness_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `freshness_threshold` smallint(6) NOT NULL DEFAULT '0',
  `passive_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `event_handler_enabled` smallint(6) NOT NULL DEFAULT '0',
  `active_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `retain_status_information` smallint(6) NOT NULL DEFAULT '0',
  `retain_nonstatus_information` smallint(6) NOT NULL DEFAULT '0',
  `notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `obsess_over_host` smallint(6) NOT NULL DEFAULT '0',
  `failure_prediction_enabled` smallint(6) NOT NULL DEFAULT '0',
  `notes` varchar(255) NOT NULL DEFAULT '',
  `notes_url` varchar(255) NOT NULL DEFAULT '',
  `action_url` varchar(255) NOT NULL DEFAULT '',
  `icon_image` varchar(255) NOT NULL DEFAULT '',
  `icon_image_alt` varchar(255) NOT NULL DEFAULT '',
  `vrml_image` varchar(255) NOT NULL DEFAULT '',
  `statusmap_image` varchar(255) NOT NULL DEFAULT '',
  `have_2d_coords` smallint(6) NOT NULL DEFAULT '0',
  `x_2d` smallint(6) NOT NULL DEFAULT '0',
  `y_2d` smallint(6) NOT NULL DEFAULT '0',
  `have_3d_coords` smallint(6) NOT NULL DEFAULT '0',
  `x_3d` double NOT NULL DEFAULT '0',
  `y_3d` double NOT NULL DEFAULT '0',
  `z_3d` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`host_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`host_object_id`),
  KEY `host_object_id` (`host_object_id`),
  KEY `display_name` (`display_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Host definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hoststatus` (
  `hoststatus_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `host_object_id` int(11) NOT NULL DEFAULT '0',
  `status_update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `output` varchar(255) NOT NULL DEFAULT '',
  `long_output` varchar(8192) NOT NULL DEFAULT '',
  `perfdata` varchar(255) NOT NULL DEFAULT '',
  `current_state` smallint(6) NOT NULL DEFAULT '0',
  `has_been_checked` smallint(6) NOT NULL DEFAULT '0',
  `should_be_scheduled` smallint(6) NOT NULL DEFAULT '0',
  `current_check_attempt` smallint(6) NOT NULL DEFAULT '0',
  `max_check_attempts` smallint(6) NOT NULL DEFAULT '0',
  `last_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `next_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `check_type` smallint(6) NOT NULL DEFAULT '0',
  `last_state_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_hard_state_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_hard_state` smallint(6) NOT NULL DEFAULT '0',
  `last_time_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time_unreachable` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state_type` smallint(6) NOT NULL DEFAULT '0',
  `last_notification` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `next_notification` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `no_more_notifications` smallint(6) NOT NULL DEFAULT '0',
  `notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `problem_has_been_acknowledged` smallint(6) NOT NULL DEFAULT '0',
  `acknowledgement_type` smallint(6) NOT NULL DEFAULT '0',
  `current_notification_number` smallint(6) NOT NULL DEFAULT '0',
  `passive_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `active_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `event_handler_enabled` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_enabled` smallint(6) NOT NULL DEFAULT '0',
  `is_flapping` smallint(6) NOT NULL DEFAULT '0',
  `percent_state_change` double NOT NULL DEFAULT '0',
  `latency` double NOT NULL DEFAULT '0',
  `execution_time` double NOT NULL DEFAULT '0',
  `scheduled_downtime_depth` smallint(6) NOT NULL DEFAULT '0',
  `failure_prediction_enabled` smallint(6) NOT NULL DEFAULT '0',
  `process_performance_data` smallint(6) NOT NULL DEFAULT '0',
  `obsess_over_host` smallint(6) NOT NULL DEFAULT '0',
  `modified_host_attributes` int(11) NOT NULL DEFAULT '0',
  `event_handler` varchar(255) NOT NULL DEFAULT '',
  `check_command` varchar(255) NOT NULL DEFAULT '',
  `normal_check_interval` double NOT NULL DEFAULT '0',
  `retry_check_interval` double NOT NULL DEFAULT '0',
  `check_timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hoststatus_id`),
  UNIQUE KEY `object_id` (`host_object_id`),
  KEY `instance_id` (`instance_id`),
  KEY `status_update_time` (`status_update_time`),
  KEY `current_state` (`current_state`),
  KEY `check_type` (`check_type`),
  KEY `state_type` (`state_type`),
  KEY `last_state_change` (`last_state_change`),
  KEY `notifications_enabled` (`notifications_enabled`),
  KEY `problem_has_been_acknowledged` (`problem_has_been_acknowledged`),
  KEY `active_checks_enabled` (`active_checks_enabled`),
  KEY `passive_checks_enabled` (`passive_checks_enabled`),
  KEY `event_handler_enabled` (`event_handler_enabled`),
  KEY `flap_detection_enabled` (`flap_detection_enabled`),
  KEY `is_flapping` (`is_flapping`),
  KEY `percent_state_change` (`percent_state_change`),
  KEY `latency` (`latency`),
  KEY `execution_time` (`execution_time`),
  KEY `scheduled_downtime_depth` (`scheduled_downtime_depth`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current host status information';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_instances` (
  `instance_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `instance_name` varchar(64) NOT NULL DEFAULT '',
  `instance_description` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Location names of various Nagios installations';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_logentries` (
  `logentry_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) NOT NULL DEFAULT '0',
  `logentry_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `entry_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `entry_time_usec` int(11) NOT NULL DEFAULT '0',
  `logentry_type` int(11) NOT NULL DEFAULT '0',
  `logentry_data` varchar(255) NOT NULL DEFAULT '',
  `realtime_data` smallint(6) NOT NULL DEFAULT '0',
  `inferred_data_extracted` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`logentry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical record of log entries';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `notification_type` smallint(6) NOT NULL DEFAULT '0',
  `notification_reason` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_time_usec` int(11) NOT NULL DEFAULT '0',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time_usec` int(11) NOT NULL DEFAULT '0',
  `state` smallint(6) NOT NULL DEFAULT '0',
  `output` varchar(255) NOT NULL DEFAULT '',
  `long_output` varchar(8192) NOT NULL DEFAULT '',
  `escalated` smallint(6) NOT NULL DEFAULT '0',
  `contacts_notified` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`notification_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`object_id`,`start_time`,`start_time_usec`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical record of host and service notifications';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_objects` (
  `object_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `objecttype_id` smallint(6) NOT NULL DEFAULT '0',
  `name1` varchar(128) NOT NULL DEFAULT '',
  `name2` varchar(128) DEFAULT NULL,
  `is_active` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`),
  KEY `objecttype_id` (`objecttype_id`,`name1`,`name2`),
  KEY `object_id` (`object_id`,`name1`,`is_active`),
  KEY `name1` (`name1`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current and historical objects of all kinds';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_processevents` (
  `processevent_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `event_type` smallint(6) NOT NULL DEFAULT '0',
  `event_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `event_time_usec` int(11) NOT NULL DEFAULT '0',
  `process_id` int(11) NOT NULL DEFAULT '0',
  `program_name` varchar(16) NOT NULL DEFAULT '',
  `program_version` varchar(20) NOT NULL DEFAULT '',
  `program_date` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`processevent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical Nagios process events';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_programstatus` (
  `programstatus_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `status_update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `program_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `program_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_currently_running` smallint(6) NOT NULL DEFAULT '0',
  `process_id` int(11) NOT NULL DEFAULT '0',
  `daemon_mode` smallint(6) NOT NULL DEFAULT '0',
  `last_command_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_log_rotation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `active_service_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `passive_service_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `active_host_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `passive_host_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `event_handlers_enabled` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_enabled` smallint(6) NOT NULL DEFAULT '0',
  `failure_prediction_enabled` smallint(6) NOT NULL DEFAULT '0',
  `process_performance_data` smallint(6) NOT NULL DEFAULT '0',
  `obsess_over_hosts` smallint(6) NOT NULL DEFAULT '0',
  `obsess_over_services` smallint(6) NOT NULL DEFAULT '0',
  `modified_host_attributes` int(11) NOT NULL DEFAULT '0',
  `modified_service_attributes` int(11) NOT NULL DEFAULT '0',
  `global_host_event_handler` varchar(255) NOT NULL DEFAULT '',
  `global_service_event_handler` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`programstatus_id`),
  UNIQUE KEY `instance_id` (`instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current program status information';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_runtimevariables` (
  `runtimevariable_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `varname` varchar(64) NOT NULL DEFAULT '',
  `varvalue` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`runtimevariable_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`varname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Runtime variables from the Nagios daemon';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_scheduleddowntime` (
  `scheduleddowntime_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `downtime_type` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `entry_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `author_name` varchar(64) NOT NULL DEFAULT '',
  `comment_data` varchar(255) NOT NULL DEFAULT '',
  `internal_downtime_id` int(11) NOT NULL DEFAULT '0',
  `triggered_by_id` int(11) NOT NULL DEFAULT '0',
  `is_fixed` smallint(6) NOT NULL DEFAULT '0',
  `duration` smallint(6) NOT NULL DEFAULT '0',
  `scheduled_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `scheduled_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `was_started` smallint(6) NOT NULL DEFAULT '0',
  `actual_start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `actual_start_time_usec` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`scheduleddowntime_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`object_id`,`entry_time`,`internal_downtime_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current scheduled host and service downtime';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_service_contactgroups` (
  `service_contactgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `service_id` int(11) NOT NULL DEFAULT '0',
  `contactgroup_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`service_contactgroup_id`),
  UNIQUE KEY `instance_id` (`service_id`,`contactgroup_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Service contact groups';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_service_contacts` (
  `service_contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `service_id` int(11) NOT NULL DEFAULT '0',
  `contact_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`service_contact_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`service_id`,`contact_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicechecks` (
  `servicecheck_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `service_object_id` int(11) NOT NULL DEFAULT '0',
  `check_type` smallint(6) NOT NULL DEFAULT '0',
  `current_check_attempt` smallint(6) NOT NULL DEFAULT '0',
  `max_check_attempts` smallint(6) NOT NULL DEFAULT '0',
  `state` smallint(6) NOT NULL DEFAULT '0',
  `state_type` smallint(6) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_time_usec` int(11) NOT NULL DEFAULT '0',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time_usec` int(11) NOT NULL DEFAULT '0',
  `command_object_id` int(11) NOT NULL DEFAULT '0',
  `command_args` varchar(255) NOT NULL DEFAULT '',
  `command_line` varchar(255) NOT NULL DEFAULT '',
  `timeout` smallint(6) NOT NULL DEFAULT '0',
  `early_timeout` smallint(6) NOT NULL DEFAULT '0',
  `execution_time` double NOT NULL DEFAULT '0',
  `latency` double NOT NULL DEFAULT '0',
  `return_code` smallint(6) NOT NULL DEFAULT '0',
  `output` varchar(255) NOT NULL DEFAULT '',
  `long_output` varchar(8192) NOT NULL DEFAULT '',
  `perfdata` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`servicecheck_id`),
  KEY `instance_id` (`instance_id`),
  KEY `service_object_id` (`service_object_id`),
  KEY `start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical service checks';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicedependencies` (
  `servicedependency_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `service_object_id` int(11) NOT NULL DEFAULT '0',
  `dependent_service_object_id` int(11) NOT NULL DEFAULT '0',
  `dependency_type` smallint(6) NOT NULL DEFAULT '0',
  `inherits_parent` smallint(6) NOT NULL DEFAULT '0',
  `timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `fail_on_ok` smallint(6) NOT NULL DEFAULT '0',
  `fail_on_warning` smallint(6) NOT NULL DEFAULT '0',
  `fail_on_unknown` smallint(6) NOT NULL DEFAULT '0',
  `fail_on_critical` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`servicedependency_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`service_object_id`,`dependent_service_object_id`,`dependency_type`,`inherits_parent`,`fail_on_ok`,`fail_on_warning`,`fail_on_unknown`,`fail_on_critical`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Service dependency definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_serviceescalation_contactgroups` (
  `serviceescalation_contactgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `serviceescalation_id` int(11) NOT NULL DEFAULT '0',
  `contactgroup_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serviceescalation_contactgroup_id`),
  UNIQUE KEY `instance_id` (`serviceescalation_id`,`contactgroup_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Service escalation contact groups';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_serviceescalation_contacts` (
  `serviceescalation_contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `serviceescalation_id` int(11) NOT NULL DEFAULT '0',
  `contact_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serviceescalation_contact_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`serviceescalation_id`,`contact_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_serviceescalations` (
  `serviceescalation_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `service_object_id` int(11) NOT NULL DEFAULT '0',
  `timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `first_notification` smallint(6) NOT NULL DEFAULT '0',
  `last_notification` smallint(6) NOT NULL DEFAULT '0',
  `notification_interval` double NOT NULL DEFAULT '0',
  `escalate_on_recovery` smallint(6) NOT NULL DEFAULT '0',
  `escalate_on_warning` smallint(6) NOT NULL DEFAULT '0',
  `escalate_on_unknown` smallint(6) NOT NULL DEFAULT '0',
  `escalate_on_critical` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serviceescalation_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`service_object_id`,`timeperiod_object_id`,`first_notification`,`last_notification`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Service escalation definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicegroup_members` (
  `servicegroup_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `servicegroup_id` int(11) NOT NULL DEFAULT '0',
  `service_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`servicegroup_member_id`),
  UNIQUE KEY `instance_id` (`servicegroup_id`,`service_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Servicegroup members';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicegroups` (
  `servicegroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `servicegroup_object_id` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`servicegroup_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`servicegroup_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Servicegroup definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `host_object_id` int(11) NOT NULL DEFAULT '0',
  `service_object_id` int(11) NOT NULL DEFAULT '0',
  `display_name` varchar(64) NOT NULL DEFAULT '',
  `check_command_object_id` int(11) NOT NULL DEFAULT '0',
  `check_command_args` varchar(255) NOT NULL DEFAULT '',
  `eventhandler_command_object_id` int(11) NOT NULL DEFAULT '0',
  `eventhandler_command_args` varchar(255) NOT NULL DEFAULT '',
  `notification_timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `check_timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `failure_prediction_options` varchar(64) NOT NULL DEFAULT '',
  `check_interval` double NOT NULL DEFAULT '0',
  `retry_interval` double NOT NULL DEFAULT '0',
  `max_check_attempts` smallint(6) NOT NULL DEFAULT '0',
  `first_notification_delay` double NOT NULL DEFAULT '0',
  `notification_interval` double NOT NULL DEFAULT '0',
  `notify_on_warning` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_unknown` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_critical` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_recovery` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_flapping` smallint(6) NOT NULL DEFAULT '0',
  `notify_on_downtime` smallint(6) NOT NULL DEFAULT '0',
  `stalk_on_ok` smallint(6) NOT NULL DEFAULT '0',
  `stalk_on_warning` smallint(6) NOT NULL DEFAULT '0',
  `stalk_on_unknown` smallint(6) NOT NULL DEFAULT '0',
  `stalk_on_critical` smallint(6) NOT NULL DEFAULT '0',
  `is_volatile` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_enabled` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_on_ok` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_on_warning` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_on_unknown` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_on_critical` smallint(6) NOT NULL DEFAULT '0',
  `low_flap_threshold` double NOT NULL DEFAULT '0',
  `high_flap_threshold` double NOT NULL DEFAULT '0',
  `process_performance_data` smallint(6) NOT NULL DEFAULT '0',
  `freshness_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `freshness_threshold` smallint(6) NOT NULL DEFAULT '0',
  `passive_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `event_handler_enabled` smallint(6) NOT NULL DEFAULT '0',
  `active_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `retain_status_information` smallint(6) NOT NULL DEFAULT '0',
  `retain_nonstatus_information` smallint(6) NOT NULL DEFAULT '0',
  `notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `obsess_over_service` smallint(6) NOT NULL DEFAULT '0',
  `failure_prediction_enabled` smallint(6) NOT NULL DEFAULT '0',
  `notes` varchar(255) NOT NULL DEFAULT '',
  `notes_url` varchar(255) NOT NULL DEFAULT '',
  `action_url` varchar(255) NOT NULL DEFAULT '',
  `icon_image` varchar(255) NOT NULL DEFAULT '',
  `icon_image_alt` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`service_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`service_object_id`),
  KEY `host_object_id` (`host_object_id`),
  KEY `service_object_id` (`service_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Service definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicestatus` (
  `servicestatus_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `service_object_id` int(11) NOT NULL DEFAULT '0',
  `status_update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `output` varchar(255) NOT NULL DEFAULT '',
  `long_output` varchar(8192) NOT NULL DEFAULT '',
  `perfdata` varchar(255) NOT NULL DEFAULT '',
  `current_state` smallint(6) NOT NULL DEFAULT '0',
  `has_been_checked` smallint(6) NOT NULL DEFAULT '0',
  `should_be_scheduled` smallint(6) NOT NULL DEFAULT '0',
  `current_check_attempt` smallint(6) NOT NULL DEFAULT '0',
  `max_check_attempts` smallint(6) NOT NULL DEFAULT '0',
  `last_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `next_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `check_type` smallint(6) NOT NULL DEFAULT '0',
  `last_state_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_hard_state_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_hard_state` smallint(6) NOT NULL DEFAULT '0',
  `last_time_ok` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time_warning` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time_unknown` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time_critical` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state_type` smallint(6) NOT NULL DEFAULT '0',
  `last_notification` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `next_notification` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `no_more_notifications` smallint(6) NOT NULL DEFAULT '0',
  `notifications_enabled` smallint(6) NOT NULL DEFAULT '0',
  `problem_has_been_acknowledged` smallint(6) NOT NULL DEFAULT '0',
  `acknowledgement_type` smallint(6) NOT NULL DEFAULT '0',
  `current_notification_number` smallint(6) NOT NULL DEFAULT '0',
  `passive_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `active_checks_enabled` smallint(6) NOT NULL DEFAULT '0',
  `event_handler_enabled` smallint(6) NOT NULL DEFAULT '0',
  `flap_detection_enabled` smallint(6) NOT NULL DEFAULT '0',
  `is_flapping` smallint(6) NOT NULL DEFAULT '0',
  `percent_state_change` double NOT NULL DEFAULT '0',
  `latency` double NOT NULL DEFAULT '0',
  `execution_time` double NOT NULL DEFAULT '0',
  `scheduled_downtime_depth` smallint(6) NOT NULL DEFAULT '0',
  `failure_prediction_enabled` smallint(6) NOT NULL DEFAULT '0',
  `process_performance_data` smallint(6) NOT NULL DEFAULT '0',
  `obsess_over_service` smallint(6) NOT NULL DEFAULT '0',
  `modified_service_attributes` int(11) NOT NULL DEFAULT '0',
  `event_handler` varchar(255) NOT NULL DEFAULT '',
  `check_command` varchar(255) NOT NULL DEFAULT '',
  `normal_check_interval` double NOT NULL DEFAULT '0',
  `retry_check_interval` double NOT NULL DEFAULT '0',
  `check_timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`servicestatus_id`),
  UNIQUE KEY `object_id` (`service_object_id`),
  KEY `current_state` (`current_state`),
  KEY `instance_id` (`instance_id`),
  KEY `status_update_time` (`status_update_time`),
  KEY `check_type` (`check_type`),
  KEY `state_type` (`state_type`),
  KEY `last_state_change` (`last_state_change`),
  KEY `notifications_enabled` (`notifications_enabled`),
  KEY `problem_has_been_acknowledged` (`problem_has_been_acknowledged`),
  KEY `active_checks_enabled` (`active_checks_enabled`),
  KEY `passive_checks_enabled` (`passive_checks_enabled`),
  KEY `event_handler_enabled` (`event_handler_enabled`),
  KEY `flap_detection_enabled` (`flap_detection_enabled`),
  KEY `is_flapping` (`is_flapping`),
  KEY `percent_state_change` (`percent_state_change`),
  KEY `latency` (`latency`),
  KEY `execution_time` (`execution_time`),
  KEY `scheduled_downtime_depth` (`scheduled_downtime_depth`),
  KEY `service_object_id` (`service_object_id`,`current_state`,`state_type`,`problem_has_been_acknowledged`,`scheduled_downtime_depth`),
  KEY `service_object_id_2` (`service_object_id`,`current_state`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current service status information';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_statehistory` (
  `statehistory_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `state_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state_time_usec` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `state_change` smallint(6) NOT NULL DEFAULT '0',
  `state` smallint(6) NOT NULL DEFAULT '0',
  `state_type` smallint(6) NOT NULL DEFAULT '0',
  `current_check_attempt` smallint(6) NOT NULL DEFAULT '0',
  `max_check_attempts` smallint(6) NOT NULL DEFAULT '0',
  `last_state` smallint(6) NOT NULL DEFAULT '-1',
  `last_hard_state` smallint(6) NOT NULL DEFAULT '-1',
  `output` varchar(255) NOT NULL DEFAULT '',
  `long_output` varchar(8192) NOT NULL DEFAULT '',
  PRIMARY KEY (`statehistory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical host and service state changes';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_systemcommands` (
  `systemcommand_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_time_usec` int(11) NOT NULL DEFAULT '0',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time_usec` int(11) NOT NULL DEFAULT '0',
  `command_line` varchar(255) NOT NULL DEFAULT '',
  `timeout` smallint(6) NOT NULL DEFAULT '0',
  `early_timeout` smallint(6) NOT NULL DEFAULT '0',
  `execution_time` double NOT NULL DEFAULT '0',
  `return_code` smallint(6) NOT NULL DEFAULT '0',
  `output` varchar(255) NOT NULL DEFAULT '',
  `long_output` varchar(8192) NOT NULL DEFAULT '',
  PRIMARY KEY (`systemcommand_id`),
  KEY `instance_id` (`instance_id`),
  KEY `start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical system commands that are executed';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_timedeventqueue` (
  `timedeventqueue_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `event_type` smallint(6) NOT NULL DEFAULT '0',
  `queued_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `queued_time_usec` int(11) NOT NULL DEFAULT '0',
  `scheduled_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recurring_event` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`timedeventqueue_id`),
  KEY `instance_id` (`instance_id`),
  KEY `instance_id_2` (`instance_id`),
  KEY `event_type` (`event_type`),
  KEY `scheduled_time` (`scheduled_time`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current Nagios event queue';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_timedevents` (
  `timedevent_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `event_type` smallint(6) NOT NULL DEFAULT '0',
  `queued_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `queued_time_usec` int(11) NOT NULL DEFAULT '0',
  `event_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `event_time_usec` int(11) NOT NULL DEFAULT '0',
  `scheduled_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recurring_event` smallint(6) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `deletion_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deletion_time_usec` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`timedevent_id`),
  KEY `instance_id` (`instance_id`),
  KEY `event_type` (`event_type`),
  KEY `scheduled_time` (`scheduled_time`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Historical events from the Nagios event queue';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_timeperiod_timeranges` (
  `timeperiod_timerange_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `timeperiod_id` int(11) NOT NULL DEFAULT '0',
  `day` smallint(6) NOT NULL DEFAULT '0',
  `start_sec` int(11) NOT NULL DEFAULT '0',
  `end_sec` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`timeperiod_timerange_id`),
  UNIQUE KEY `instance_id` (`timeperiod_id`,`day`,`start_sec`,`end_sec`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Timeperiod definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_timeperiods` (
  `timeperiod_id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` smallint(6) NOT NULL DEFAULT '0',
  `config_type` smallint(6) NOT NULL DEFAULT '0',
  `timeperiod_object_id` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`timeperiod_id`),
  UNIQUE KEY `instance_id` (`instance_id`,`config_type`,`timeperiod_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Timeperiod definitions';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

