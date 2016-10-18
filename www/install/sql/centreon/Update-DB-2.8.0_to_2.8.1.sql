-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.1' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.0' LIMIT 1;

-- Drop from nagios configuration
ALTER TABLE `cfg_nagios` DROP COLUMN precached_object_file;
ALTER TABLE `cfg_nagios` DROP COLUMN object_cache_file;

-- Create downtime cache table for recurrent downtimes
CREATE TABLE IF NOT EXISTS `downtime_cache` (
  `downtime_cache_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`downtime_cache_id`),
  `downtime_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11),
  `start_timestamp` int(11) NOT NULL,
  `end_timestamp` int(11) NOT NULL,
  `start_hour` varchar(255) NOT NULL,
  `end_hour` varchar(255) NOT NULL,
  CONSTRAINT `downtime_cache_ibfk_1` FOREIGN KEY (`downtime_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_cache_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_cache_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add correlation output for Centreon broker
INSERT INTO `cb_module` (`name`, `libname`, `loading_pos`, `is_bundle`, `is_activated`) VALUES ('Correlation', 'correlation.so', 30, 0, 1);
INSERT INTO `cb_type` (`type_name`, `type_shortname`, `cb_module_id`)
  VALUES ('Correlation', 'correlation', (SELECT `cb_module_id` FROM `cb_module` WHERE `libname` = 'correlation.so'));
INSERT INTO `cb_field` (`fieldname`, `displayname`, `description`, `fieldtype`, `external`)
  VALUES ('passive', 'Correlation passive', 'The passive mode is for the secondary Centreon Broker.', 'radio', NULL);
INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`)
  VALUES (1, (SELECT `cb_field_id` FROM `cb_field` WHERE `displayname` = 'Correlation passive'), 'no');
INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`, `cb_type_uniq`)
  VALUES (1, (SELECT `cb_type_id` FROM `cb_type` WHERE `type_shortname` = 'correlation'), 1);
INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
((SELECT `cb_type_id` FROM `cb_type` WHERE `type_shortname` = 'correlation'), 29, 1, 1),
((SELECT `cb_type_id` FROM `cb_type` WHERE `type_shortname` = 'correlation'), (SELECT `cb_field_id` FROM `cb_field` WHERE `displayname` = 'Correlation passive'), 0, 2);
