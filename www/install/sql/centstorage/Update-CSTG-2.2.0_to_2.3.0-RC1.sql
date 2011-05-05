ALTER TABLE `index_data` CHANGE `host_name` `host_name` varchar(255) DEFAULT NULL;
ALTER TABLE `index_data` CHANGE `service_description` `service_description` varchar(255) DEFAULT NULL;

ALTER TABLE  `log_archive_host` ADD  `MaintenanceTime` INT NULL DEFAULT  '0' AFTER  `UNDETERMINEDTimeScheduled` ;
ALTER TABLE  `log_archive_service` ADD  `MaintenanceTime` INT NULL DEFAULT  '0' AFTER  `UNDETERMINEDTimeScheduled` ;

CREATE TABLE IF NOT EXISTS IF NOT EXISTS `centreon_acl` (
  `id` int(11) NOT NULL auto_increment,
  `host_id` int(11) default NULL,
  `host_name` varchar(255) default NULL,
  `service_id` int(11) default NULL,
  `service_description` varchar(255) default NULL,
  `group_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `group_id_by_name` (`host_name`(70),`service_description`(120),`group_id`),
  KEY `group_id_by_id` (`host_id`,`service_id`,`group_id`),
  KEY `group_id_for_host` (`host_name`,`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `metrics` ADD `data_source_type` ENUM( '0', '1', '2', '3' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' AFTER `metric_name` ;

-- ------------------------------------
--                                   --
-- Centreon Broker's database schema --
--                                   --
-- ------------------------------------

-- acknowledgements
-- comments
-- customvariables
-- downtimes
-- eventhandlers
-- flappingstatuses
-- hosts
-- hostgroups
-- hosts_hostgroups
-- hosts_hosts_dependencies
-- hosts_hosts_parents
-- hoststateevents
-- instances
-- issues
-- issues_issues_parents
-- logs
-- modules
-- notifications
-- schemaversion
-- services
-- servicegroups
-- services_servicegroups
-- services_services_dependencies
-- servicestateevents


--
-- Holds the current version of the database schema.
--
CREATE TABLE IF NOT EXISTS schemaversion (
  software varchar(128) NOT NULL,
  version int NOT NULL
) ENGINE=InnoDB;
INSERT INTO schemaversion (software, version) VALUES ('centreon-broker', 1);


--
-- Store information about Nagios instances.
--
CREATE TABLE IF NOT EXISTS instances (
  instance_id int NOT NULL,
  name varchar(255) NOT NULL default 'localhost',

  active_host_checks boolean default NULL,
  active_service_checks boolean default NULL,
  address varchar(128) default NULL,
  check_hosts_freshness boolean default NULL,
  check_services_freshness boolean default NULL,
  daemon_mode boolean default NULL,
  description varchar(128) default NULL,
  end_time int default NULL,
  engine varchar(64) default NULL,
  event_handlers boolean default NULL,
  failure_prediction boolean default NULL,
  flap_detection boolean default NULL,
  global_host_event_handler text default NULL,
  global_service_event_handler text default NULL,
  last_alive int default NULL,
  last_command_check int default NULL,
  last_log_rotation int default NULL,
  modified_host_attributes int default NULL,
  modified_service_attributes int default NULL,
  notifications boolean default NULL,
  obsess_over_hosts boolean default NULL,
  obsess_over_services boolean default NULL,
  passive_host_checks boolean default NULL,
  passive_service_checks boolean default NULL,
  pid int default NULL,
  process_perfdata boolean default NULL,
  running boolean default NULL,
  start_time int default NULL,
  version varchar(16) default NULL,

  PRIMARY KEY (instance_id)
) ENGINE=InnoDB;


--
-- Monitored hosts.
--
CREATE TABLE IF NOT EXISTS hosts (
  host_id int NOT NULL,
  name varchar(255) NOT NULL,
  instance_id int NOT NULL,

  acknowledged boolean default NULL,
  acknowledgement_type smallint default NULL,
  action_url varchar(255) default NULL,
  active_checks boolean default NULL,
  address varchar(75) default NULL,
  alias varchar(100) default NULL,
  check_attempt smallint default NULL,
  check_command text default NULL,
  check_freshness boolean default NULL,
  check_interval double default NULL,
  check_period varchar(75) default NULL,
  check_type smallint default NULL,
  checked boolean default NULL,
  command_line text default NULL,
  default_active_checks boolean default NULL,
  default_event_handler_enabled boolean default NULL,
  default_failure_prediction boolean default NULL,
  default_flap_detection boolean default NULL,
  default_notify boolean default NULL,
  default_passive_checks boolean default NULL,
  default_process_perfdata boolean default NULL,
  display_name varchar(100) default NULL,
  enabled bool NOT NULL default true,
  event_handler varchar(255) default NULL,
  event_handler_enabled boolean default NULL,
  execution_time double default NULL,
  failure_prediction boolean default NULL,
  first_notification_delay double default NULL,
  flap_detection boolean default NULL,
  flap_detection_on_down boolean default NULL,
  flap_detection_on_unreachable boolean default NULL,
  flap_detection_on_up boolean default NULL,
  flapping boolean default NULL,
  freshness_threshold double default NULL,
  high_flap_threshold double default NULL,
  icon_image varchar(255) default NULL,
  icon_image_alt varchar(255) default NULL,
  last_check int default NULL,
  last_hard_state smallint default NULL,
  last_hard_state_change int default NULL,
  last_notification int default NULL,
  last_state_change int default NULL,
  last_time_down int default NULL,
  last_time_unreachable int default NULL,
  last_time_up int default NULL,
  last_update int default NULL,
  latency double default NULL,
  low_flap_threshold double default NULL,
  max_check_attempts smallint default NULL,
  modified_attributes int default NULL,
  next_check int default NULL,
  next_host_notification int default NULL,
  no_more_notifications boolean default NULL,
  notes varchar(255) default NULL,
  notes_url varchar(255) default NULL,
  notification_interval double default NULL,
  notification_number smallint default NULL,
  notification_period varchar(75) default NULL,
  notify boolean default NULL,
  notify_on_down boolean default NULL,
  notify_on_downtime boolean default NULL,
  notify_on_flapping boolean default NULL,
  notify_on_recovery boolean default NULL,
  notify_on_unreachable boolean default NULL,
  obsess_over_host boolean default NULL,
  output text default NULL,
  passive_checks boolean default NULL,
  percent_state_change double default NULL,
  perfdata text default NULL,
  process_perfdata boolean default NULL,
  retain_nonstatus_information boolean default NULL,
  retain_status_information boolean default NULL,
  retry_interval double default NULL,
  scheduled_downtime_depth smallint default NULL,
  should_be_scheduled boolean default NULL,
  stalk_on_down boolean default NULL,
  stalk_on_unreachable boolean default NULL,
  stalk_on_up boolean default NULL,
  state smallint default NULL,
  state_type smallint default NULL,
  statusmap_image varchar(255) default NULL,

  UNIQUE (host_id),
  UNIQUE (instance_id, name),
  FOREIGN KEY (instance_id) REFERENCES instances (instance_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Host groups.
--
CREATE TABLE IF NOT EXISTS hostgroups (
  hostgroup_id int NOT NULL auto_increment,
  instance_id int NOT NULL,
  name varchar(255) NOT NULL,

  action_url varchar(160) default NULL,
  alias varchar(255) default NULL,
  notes varchar(160) default NULL,
  notes_url varchar(160) default NULL,

  PRIMARY KEY (hostgroup_id),
  UNIQUE (name, instance_id),
  FOREIGN KEY (instance_id) REFERENCES instances (instance_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Relationships between hosts and host groups.
--
CREATE TABLE IF NOT EXISTS hosts_hostgroups (
  host_id int NOT NULL,
  hostgroup_id int NOT NULL,

  UNIQUE (host_id, hostgroup_id),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE,
  FOREIGN KEY (hostgroup_id) REFERENCES hostgroups (hostgroup_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Hosts dependencies.
--
CREATE TABLE IF NOT EXISTS hosts_hosts_dependencies (
  dependent_host_id int NOT NULL,
  host_id int NOT NULL,

  dependency_period varchar(75) default NULL,
  execution_failure_options varchar(15) default NULL,
  inherits_parent boolean default NULL,
  notification_failure_options varchar(15) default NULL,

  UNIQUE (dependent_host_id, host_id),
  FOREIGN KEY (dependent_host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE,
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Hosts parenting relationships.
--
CREATE TABLE IF NOT EXISTS hosts_hosts_parents (
  child_id int NOT NULL,
  parent_id int NOT NULL,

  UNIQUE (child_id, parent_id),
  FOREIGN KEY (child_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Monitored services.
--
CREATE TABLE IF NOT EXISTS services (
  host_id int NOT NULL,
  description varchar(255) NOT NULL,
  service_id int NOT NULL,

  acknowledged boolean default NULL,
  acknowledgement_type smallint default NULL,
  action_url varchar(255) default NULL,
  active_checks boolean default NULL,
  check_attempt smallint default NULL,
  check_command text default NULL,
  check_freshness boolean default NULL,
  check_interval double default NULL,
  check_period varchar(75) default NULL,
  check_type smallint default NULL,
  checked boolean default NULL,
  command_line text default NULL,
  default_active_checks boolean default NULL,
  default_event_handler_enabled boolean default NULL,
  default_failure_prediction boolean default NULL,
  default_flap_detection boolean default NULL,
  default_notify boolean default NULL,
  default_passive_checks boolean default NULL,
  default_process_perfdata boolean default NULL,
  display_name varchar(160) default NULL,
  enabled bool NOT NULL default true,
  event_handler varchar(255) default NULL,
  event_handler_enabled boolean default NULL,
  execution_time double default NULL,
  failure_prediction boolean default NULL,
  failure_prediction_options varchar(64) default NULL,
  first_notification_delay double default NULL,
  flap_detection boolean default NULL,
  flap_detection_on_critical boolean default NULL,
  flap_detection_on_ok boolean default NULL,
  flap_detection_on_unknown boolean default NULL,
  flap_detection_on_warning boolean default NULL,
  flapping boolean default NULL,
  freshness_threshold double default NULL,
  high_flap_threshold double default NULL,
  icon_image varchar(255) default NULL,
  icon_image_alt varchar(255) default NULL,
  last_check int default NULL,
  last_hard_state smallint default NULL,
  last_hard_state_change int default NULL,
  last_notification int default NULL,
  last_state_change int default NULL,
  last_time_critical int default NULL,
  last_time_ok int default NULL,
  last_time_unknown int default NULL,
  last_time_warning int default NULL,
  last_update int default NULL,
  latency double default NULL,
  low_flap_threshold double default NULL,
  max_check_attempts smallint default NULL,
  modified_attributes int default NULL,
  next_check int default NULL,
  next_notification int default NULL,
  no_more_notifications boolean default NULL,
  notes varchar(255) default NULL,
  notes_url varchar(255) default NULL,
  notification_interval double default NULL,
  notification_number smallint default NULL,
  notification_period varchar(75) default NULL,
  notify boolean default NULL,
  notify_on_critical boolean default NULL,
  notify_on_downtime boolean default NULL,
  notify_on_flapping boolean default NULL,
  notify_on_recovery boolean default NULL,
  notify_on_unknown boolean default NULL,
  notify_on_warning boolean default NULL,
  obsess_over_service boolean default NULL,
  output text default NULL,
  passive_checks boolean default NULL,
  percent_state_change double default NULL,
  perfdata text default NULL,
  process_perfdata boolean default NULL,
  retain_nonstatus_information boolean default NULL,
  retain_status_information boolean default NULL,
  retry_interval double default NULL,
  scheduled_downtime_depth smallint default NULL,
  should_be_scheduled boolean default NULL,
  stalk_on_critical boolean default NULL,
  stalk_on_ok boolean default NULL,
  stalk_on_unknown boolean default NULL,
  stalk_on_warning boolean default NULL,
  state smallint default NULL,
  state_type smallint default NULL,
  volatile boolean default NULL,

  UNIQUE (host_id, service_id),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Groups of services.
--
CREATE TABLE IF NOT EXISTS servicegroups (
  servicegroup_id int NOT NULL auto_increment,
  instance_id int NOT NULL,
  name varchar(255) NOT NULL,

  action_url varchar(160) default NULL,
  alias varchar(255) default NULL,
  notes varchar(160) default NULL,
  notes_url varchar(160) default NULL,

  PRIMARY KEY (servicegroup_id),
  FOREIGN KEY (instance_id) REFERENCES instances (instance_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Relationships between services and service groups.
--
CREATE TABLE IF NOT EXISTS services_servicegroups (
  host_id int NOT NULL,
  service_id int NOT NULL,
  servicegroup_id int NOT NULL,

  UNIQUE (host_id, service_id, servicegroup_id),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE,
  FOREIGN KEY (servicegroup_id) REFERENCES servicegroups (servicegroup_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Services dependencies.
--
CREATE TABLE IF NOT EXISTS services_services_dependencies (
  dependent_host_id int NOT NULL,
  dependent_service_id int NOT NULL,
  host_id int NOT NULL,
  service_id int NOT NULL,

  dependency_period varchar(75) default NULL,
  execution_failure_options varchar(15) default NULL,
  inherits_parent boolean default NULL,
  notification_failure_options varchar(15) default NULL,

  UNIQUE (dependent_host_id, dependent_service_id, host_id, service_id),
  FOREIGN KEY (dependent_host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE,
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Holds acknowledgedments information.
--
CREATE TABLE IF NOT EXISTS acknowledgements (
  acknowledgement_id int NOT NULL auto_increment,
  entry_time int NOT NULL,
  host_id int NOT NULL,
  service_id int default NULL,

  author varchar(64) default NULL,
  comment_data varchar(255) default NULL,
  instance_id int default NULL,
  notify_contacts boolean default NULL,
  persistent_comment boolean default NULL,
  state smallint default NULL,
  sticky boolean default NULL,
  type smallint default NULL,

  PRIMARY KEY (acknowledgement_id),
  UNIQUE (entry_time, host_id, service_id),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE,
  FOREIGN KEY (instance_id) REFERENCES instances (instance_id)
    ON DELETE SET NULL
) ENGINE=InnoDB;


--
-- Holds comments information.
--
CREATE TABLE IF NOT EXISTS comments (
  comment_id int NOT NULL auto_increment,
  entry_time int NOT NULL,
  host_id int NOT NULL,
  service_id int default NULL,

  author varchar(64) default NULL,
  data text default NULL,
  deletion_time int default NULL,
  entry_type smallint default NULL,
  expire_time int default NULL,
  expires boolean default NULL,
  instance_id int default NULL,
  internal_id int NOT NULL,
  persistent boolean default NULL,
  source smallint default NULL,
  type smallint default NULL,

  PRIMARY KEY (comment_id),
  UNIQUE (entry_time, host_id, service_id),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE,
  FOREIGN KEY (instance_id) REFERENCES instances (instance_id)
    ON DELETE SET NULL
) ENGINE=InnoDB;


--
-- Custom variables.
--
CREATE TABLE IF NOT EXISTS customvariables (
  customvariable_id int NOT NULL auto_increment,
  host_id int default NULL,
  name varchar(255) default NULL,
  service_id int default NULL,

  default_value varchar(255) default NULL,
  modified boolean default NULL,
  type smallint default NULL,
  update_time int default NULL,
  value varchar(255) default NULL,

  PRIMARY KEY (customvariable_id),
  UNIQUE (host_id, name, service_id)
) ENGINE=InnoDB;


--
-- Downtimes.
--
CREATE TABLE IF NOT EXISTS downtimes (
  downtime_id int NOT NULL auto_increment,
  entry_time int default NULL,
  host_id int NOT NULL,
  service_id int default NULL,

  author varchar(64) default NULL,
  cancelled boolean default NULL,
  comment_data text default NULL,
  duration int default NULL,
  end_time int default NULL,
  fixed boolean default NULL,
  instance_id int default NULL,
  internal_id int default NULL,
  start_time int default NULL,
  started boolean default NULL,
  triggered_by int default NULL,
  type smallint default NULL,

  PRIMARY KEY (downtime_id),
  UNIQUE (entry_time, host_id, service_id),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE,
  FOREIGN KEY (instance_id) REFERENCES instances (instance_id)
    ON DELETE SET NULL
) ENGINE=InnoDB;


--
-- Event handlers.
--
CREATE TABLE IF NOT EXISTS eventhandlers (
  eventhandler_id int NOT NULL auto_increment,
  host_id int default NULL,
  service_id int default NULL,
  start_time int default NULL,

  command_args varchar(255) default NULL,
  command_line varchar(255) default NULL,
  early_timeout smallint default NULL,
  end_time int default NULL,
  execution_time double default NULL,
  output varchar(255) default NULL,
  return_code smallint default NULL,
  state smallint default NULL,
  state_type smallint default NULL,
  timeout smallint default NULL,
  type smallint default NULL,

  PRIMARY KEY (eventhandler_id),
  UNIQUE (host_id, service_id, start_time),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Historization of flapping statuses.
--
CREATE TABLE IF NOT EXISTS flappingstatuses (
  flappingstatus_id int NOT NULL auto_increment,
  host_id int default NULL,
  service_id int default NULL,
  event_time int default NULL,

  comment_time int default NULL,
  event_type smallint default NULL,
  high_threshold double default NULL,
  internal_comment_id int default NULL,
  low_threshold double default NULL,
  percent_state_change double default NULL,
  reason_type smallint default NULL,
  type smallint default NULL,

  PRIMARY KEY (flappingstatus_id),
  UNIQUE (host_id, service_id, event_time),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Correlated issues.
--
CREATE TABLE IF NOT EXISTS issues (
  issue_id int NOT NULL auto_increment,
  host_id int default NULL,
  service_id int default NULL,
  start_time int NOT NULL,

  ack_time int default NULL,
  end_time int default NULL,

  PRIMARY KEY (issue_id),
  UNIQUE (host_id, service_id, start_time),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Issues parenting.
--
CREATE TABLE IF NOT EXISTS issues_issues_parents (
  child_id int NOT NULL,
  end_time int default NULL,
  start_time int NOT NULL,
  parent_id int NOT NULL,

  FOREIGN KEY (child_id) REFERENCES issues (issue_id)
    ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES issues (issue_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
-- Nagios logs.
--
CREATE TABLE IF NOT EXISTS logs (
  log_id int NOT NULL auto_increment,

  ctime int default NULL,
  host_id int default NULL,
  host_name varchar(255) default NULL,
  instance_name varchar(255) NOT NULL,
  issue_id int default NULL,
  msg_type tinyint default NULL,
  notification_cmd varchar(255) default NULL,
  notification_contact varchar(255) default NULL,
  output text default NULL,
  retry int default NULL,
  service_description varchar(255) default NULL,
  service_id int default NULL,
  status enum('0', '1', '2', '3', '4') default NULL,
  type smallint default NULL,

  PRIMARY KEY (log_id),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE SET NULL
) ENGINE=MyISAM;


--
-- Nagios modules.
--
CREATE TABLE IF NOT EXISTS modules (
  module_id int NOT NULL auto_increment,
  instance_id int NOT NULL,

  args varchar(255) default NULL,
  filename varchar(255) default NULL,
  loaded boolean default NULL,
  should_be_loaded boolean default NULL,

  PRIMARY KEY (module_id),
  FOREIGN KEY (instance_id) REFERENCES instances (instance_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
--  Notifications.
--
CREATE TABLE IF NOT EXISTS notifications (
  notification_id int NOT NULL auto_increment,
  host_id int default NULL,
  service_id int default NULL,
  start_time int default NULL,

  ack_author varchar(255) default NULL,
  ack_data text default NULL,
  command_name varchar(255) default NULL,
  contact_name varchar(255) default NULL,
  contacts_notified boolean default NULL,
  end_time int default NULL,
  escalated boolean default NULL,
  output text default NULL,
  reason_type int default NULL,
  state int default NULL,
  type int default NULL,

  PRIMARY KEY (notification_id),
  UNIQUE (host_id, service_id, start_time),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
--  Host states.
--
CREATE TABLE IF NOT EXISTS hoststateevents (
  hoststateevent_id int NOT NULL auto_increment,
  host_id int NOT NULL,
  start_time int NOT NULL,

  end_time int default NULL,
  in_downtime boolean default NULL,
  last_update int default NULL,
  state int default NULL,

  PRIMARY KEY (hoststateevent_id),
  UNIQUE (host_id, start_time),
  FOREIGN KEY (host_id) REFERENCES hosts (host_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


--
--  Service states.
--
CREATE TABLE IF NOT EXISTS servicestateevents (
  servicestateevent_id int NOT NULL auto_increment,
  host_id int NOT NULL,
  service_id int NOT NULL,
  start_time int NOT NULL,

  end_time int default NULL,
  in_downtime boolean default NULL,
  last_update int default NULL,
  state int default NULL,

  PRIMARY KEY (servicestateevent_id),
  UNIQUE (host_id, service_id, start_time),
  FOREIGN KEY (host_id, service_id) REFERENCES services (host_id, service_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


ALTER TABLE `log` ADD COLUMN `instance` int(11) NOT NULL default '1' AFTER `msg_type`;
ALTER TABLE `log` ADD INDEX(`instance`);