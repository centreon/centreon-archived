ALTER TABLE `centreon_acl` ADD host_id INT(11) default NULL AFTER `id` ;
ALTER TABLE `centreon_acl` ADD service_id INT(11) default NULL AFTER `host_name`;
ALTER TABLE `nagios_servicestatus` ADD INDEX (`current_state`);
ALTER TABLE `nagios_hostchecks` ADD COLUMN `long_output` varchar(8192) NOT NULL default '' AFTER `output`;

ALTER TABLE `nagios_hoststatus` ADD COLUMN `long_output` varchar(8192) NOT NULL default '' AFTER `output`;
ALTER TABLE `nagios_servicechecks` ADD COLUMN `long_output` varchar(8192) NOT NULL default '' AFTER `output`;
ALTER TABLE `nagios_servicestatus` ADD COLUMN `long_output` varchar(8192) NOT NULL default '' AFTER `output`;
ALTER TABLE `nagios_statehistory` ADD COLUMN `long_output` varchar(8192) NOT NULL default '' AFTER `output`;

ALTER TABLE `nagios_eventhandlers` ADD COLUMN `long_output` varchar(8192) NOT NULL default '' AFTER `output`;
ALTER TABLE `nagios_systemcommands` ADD COLUMN `long_output` varchar(8192) NOT NULL default '' AFTER `output`;
ALTER TABLE `nagios_notifications` ADD COLUMN `long_output` varchar(8192) NOT NULL default '' AFTER `output`;

ALTER TABLE `nagios_hosts` ADD INDEX ( `host_object_id` );

ALTER TABLE `nagios_hoststatus` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `status_update_time` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `current_state` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `check_type` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `state_type` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `last_state_change` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `notifications_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `problem_has_been_acknowledged` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `active_checks_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `passive_checks_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `event_handler_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `flap_detection_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `is_flapping` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `percent_state_change` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `latency` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `execution_time` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `scheduled_downtime_depth` );

ALTER TABLE `nagios_services` ADD INDEX ( `service_object_id` );

ALTER TABLE `nagios_servicestatus` ADD INDEX ( `instance_id` ); 
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `status_update_time` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `current_state` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `check_type` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `state_type` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `last_state_change` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `notifications_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `problem_has_been_acknowledged` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `active_checks_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `passive_checks_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `event_handler_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `flap_detection_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `is_flapping` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `percent_state_change` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `latency` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `execution_time` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `scheduled_downtime_depth` );


ALTER TABLE `nagios_timedeventqueue` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_timedeventqueue` ADD INDEX ( `event_type` );
ALTER TABLE `nagios_timedeventqueue` ADD INDEX ( `scheduled_time` );
ALTER TABLE `nagios_timedeventqueue` ADD INDEX ( `object_id` );

ALTER TABLE `nagios_timedevents` DROP INDEX `instance_id` ;
ALTER TABLE `nagios_timedevents` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_timedevents` ADD INDEX ( `event_type` );
ALTER TABLE `nagios_timedevents` ADD INDEX ( `scheduled_time` );
ALTER TABLE `nagios_timedevents` ADD INDEX ( `object_id` );

ALTER TABLE `nagios_systemcommands` DROP INDEX `instance_id`;  
ALTER TABLE `nagios_systemcommands` ADD INDEX ( `instance_id` );

ALTER TABLE `nagios_servicechecks` DROP INDEX `instance_id`;
ALTER TABLE `nagios_servicechecks` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_servicechecks` ADD INDEX ( `service_object_id` );
ALTER TABLE `nagios_servicechecks` ADD INDEX ( `start_time` );

ALTER TABLE `nagios_configfilevariables` DROP INDEX `instance_id`;

ALTER TABLE `nagios_objects` ADD INDEX ( `name1` , `objecttype_id` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `service_object_id` , `current_state` , `state_type` , `problem_has_been_acknowledged` , `scheduled_downtime_depth` )  ;
ALTER TABLE `nagios_objects` ADD INDEX ( `object_id` , `name1` , `is_active` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `service_object_id` , `current_state` );
ALTER TABLE `nagios_objects` DROP INDEX `name1`;

ALTER TABLE `centreon_acl` ADD INDEX ( `host_id` , `service_id` , `group_id` );
s