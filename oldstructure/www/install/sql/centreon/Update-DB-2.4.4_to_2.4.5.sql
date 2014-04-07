-- Ticket #4201
INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`) VALUE (2, 'BBDO Protocol', 'bbdo');

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

-- Ticket #3765
ALTER TABLE `cfg_centreonbroker` ADD COLUMN `config_write_timestamp` enum('0','1') DEFAULT '1' AFTER `config_filename`;

-- Ticket #4651
ALTER TABLE `cfg_centreonbroker` ADD COLUMN `config_write_thread_id` enum('0','1') DEFAULT '1' AFTER `config_write_timestamp`;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.4.5' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.4' LIMIT 1;
