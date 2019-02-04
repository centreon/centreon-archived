-- Add new configuration for Centeron Broker : Use multiple thread to connect to database
INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES
(75, 'connections_count', 'Connections count', 'Number of opened connection to the database.', 'int', NULL),
(76, 'enable_command_cache', 'Enable command cache', 'Cache the executed command for better insert performance. Warning: This options use more memory.', 'radio', NULL);

INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`) VALUES
(1, 76, 'no');

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(14, 75, 1, 18),
(16, 75, 1, 20),
(16, 76, 1, 21);

-- New configuration options for Centreon Engine
ALTER TABLE `cfg_nagios` ADD COLUMN `enable_macros_filter` ENUM('0', '1') DEFAULT '0';
ALTER TABLE `cfg_nagios` ADD COLUMN `event_broker_to_log` INT DEFAULT -1;
ALTER TABLE `cfg_nagios` ADD COLUMN `macros_filter` TEXT DEFAULT '';

-- Change version of Centreon
UPDATE `informations` SET `value` = '19.04.1' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.4' LIMIT 1;
