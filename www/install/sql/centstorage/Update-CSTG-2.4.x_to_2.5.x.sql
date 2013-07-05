-- /!\ WARNING /!\
-- This file must be renamed once we know the exact source and target versions.
-- /!\ WARNING /!\
ALTER TABLE `index_data` ADD COLUMN `rrd_retention` INT(11) DEFAULT NULL AFTER `to_delete`;

-- Add warn low
ALTER TABLE `metrics` ADD COLUMN `warn_low` float DEFAULT NULL AFTER `warn`;
ALTER TABLE `metrics` ADD COLUMN `warn_threshold_mode` enum('0','1') DEFAULT NULL AFTER `warn_low`;
ALTER TABLE `metrics` ADD COLUMN `crit_low` float DEFAULT NULL AFTER `crit`;
ALTER TABLE `metrics` ADD COLUMN `crit_threshold_mode` enum('0','1') DEFAULT NULL AFTER `crit_low`;

CREATE TABLE `log_traps_args` (
  `fk_log_traps` int(11) NOT NULL,
  `arg_number` int(11) DEFAULT NULL,
  `arg_oid` varchar(255) DEFAULT NULL,
  `arg_value` varchar(255) DEFAULT NULL,
  `trap_time` int(11) DEFAULT NULL,
  KEY `fk_log_traps` (`fk_log_traps`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

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
  `severity` varchar(255) DEFAULT NULL,
  `output_message` varchar(1024) DEFAULT NULL,
  KEY `trap_id` (`trap_id`),
  KEY `trap_time` (`trap_time`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;


-- Add Centreon Broker configuration
INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES 
(42, 'store_in_data_bin', 'Store in performance data in data_bin', 'It should be enabled to control whether or not Centreon Broker should insert performance data in the data_bin table.', 'radio', NULL),
(43, 'insert_in_index_data', 'Insert in index data', 'Whether or not Broker should create entries in the index_data table. This process should be done by Centreon and this option should only be enabled by advanced users knowing what they\'re doing', 'text', 'T=options:C=value:CK=key:K=index_data'),
(44, 'write_metrics', 'Write metrics', 'This can be used to disable graph update and therefore reduce I/O', 'radio', NULL),
(45, 'write_status', 'Write status', 'This can be used to disable graph update and therefore reduce I/O', 'radio', NULL);

INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`) VALUES
(1, 42, 'yes'),
(1, 44, 'yes'),
(1, 45, 'yes');

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(13, 42, 1, 5),
(13, 43, 1, 6),
(13, 44, 1, 5),
(13, 45, 1, 6);


ALTER TABLE `downtimes` ADD COLUMN `actual_start_time` int(11) DEFAULT NULL AFTER `start_time`;
ALTER TABLE `downtimes` ADD COLUMN `actual_end_time` int(11) DEFAULT NULL AFTER `actual_start_time`;
