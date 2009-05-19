ALTER TABLE `centreon_acl` ADD host_id INT(11) default NULL AFTER `id` ;
ALTER TABLE `centreon_acl` ADD service_id INT(11) default NULL AFTER `host_name`;
ALTER TABLE `nagios_servicestatus` ADD INDEX (`current_state`);