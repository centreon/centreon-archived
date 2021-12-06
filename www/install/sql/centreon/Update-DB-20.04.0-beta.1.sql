-- Remove broker correlation mechanism
ALTER TABLE `cfg_centreonbroker` DROP COLUMN `correlation_activate`;
DELETE FROM `cb_field` WHERE
  `displayname` = 'Correlation file'
  OR `description` LIKE 'File where correlation%'
  OR `displayname` = 'Correlation passive';
DELETE FROM `cb_type` WHERE `type_shortname` = 'correlation';
-- Resolve radio button broker form
INSERT INTO cb_list_values (cb_list_id, value_name, value_value) VALUES
((SELECT cb_list_id FROM cb_list WHERE cb_field_id = (SELECT cb_field_id FROM cb_field WHERE fieldtype ='radio' AND fieldname ='error') LIMIT 1),'No','no'),
((SELECT cb_list_id FROM cb_list WHERE cb_field_id = (SELECT cb_field_id FROM cb_field WHERE fieldtype ='radio' AND fieldname ='error') LIMIT 1),'Yes','yes');

-- Update topology of service grid / by host group / by service group
UPDATE topology SET topology_url_opt = '&o=svcOV_pb' WHERE topology_page = 20204;
UPDATE topology SET topology_url_opt = '&o=svcOVHG_pb' WHERE topology_page = 20209;
UPDATE topology SET topology_url_opt = '&o=svcOVSG_pb' WHERE topology_page = 20212;

-- Add unified view page entry
INSERT INTO `topology` (`topology_name`, `topology_url`, `readonly`, `is_react`, `topology_parent`, `topology_page`, `topology_group`, `topology_order`) VALUES ('Events view (beta)', '/monitoring/events', '1', '1', 1, 104, 1, 2);

-- Delete legacy engine parameters
ALTER TABLE `cfg_nagios` DROP COLUMN `check_result_path`;
ALTER TABLE `cfg_nagios` DROP COLUMN `use_check_result_path`;
ALTER TABLE `cfg_nagios` DROP COLUMN `max_check_result_file_age`;

-- Update nagios_server to add gorgone connection
ALTER TABLE `nagios_server` ADD `gorgone_communication_type` enum('1','2') NOT NULL DEFAULT '1' AFTER `centreonconnector_path`;
ALTER TABLE `nagios_server` ADD `gorgone_port` INT(11) DEFAULT NULL AFTER `gorgone_communication_type`;
UPDATE `nagios_server` SET `gorgone_port` = `ssh_port`;
ALTER TABLE `nagios_server` CHANGE `remote_server_centcore_ssh_proxy` `remote_server_use_as_proxy` enum('0','1') NOT NULL DEFAULT '1';
ALTER TABLE `nagios_server` DROP COLUMN `ssh_private_key`;
UPDATE `nagios_server` SET `gorgone_communication_type` = '2';
-- Update options for gorgone
UPDATE options SET `key` = 'gorgone_illegal_characters' WHERE `key` = 'centcore_illegal_characters';
UPDATE options SET `key` = 'gorgone_cmd_timeout' WHERE `key` = 'centcore_cmd_timeout';
INSERT INTO `options` (`key`, `value`) SELECT 'gorgone_cmd_timeout', '5' FROM DUAL WHERE NOT EXISTS (SELECT `value` FROM `options` WHERE `key` = 'gorgone_cmd_timeout');
UPDATE topology SET topology_url_opt = '&o=gorgone', topology_name = 'Gorgone' WHERE topology_page = 50117;
UPDATE options SET `key` = 'debug_gorgone' WHERE `key` = 'debug_centcore';
DELETE FROM `options` WHERE `key` = 'enable_perfdata_sync';
DELETE FROM `options` WHERE `key` = 'enable_logs_sync';
-- Gorgone API default
INSERT INTO `options` (`key`, `value`) VALUES ('gorgone_api_address', '127.0.0.1');
INSERT INTO `options` (`key`, `value`) VALUES ('gorgone_api_port', '8085');
INSERT INTO `options` (`key`, `value`) VALUES ('gorgone_api_ssl', '0');
INSERT INTO `options` (`key`, `value`) VALUES ('gorgone_api_allow_self_signed', '1');
-- Add default value for enable_broker_stats if not set
INSERT INTO `options` (`key`, `value`) SELECT 'enable_broker_stats', '0' FROM DUAL WHERE NOT EXISTS (SELECT `value` FROM `options` WHERE `key` = 'enable_broker_stats');

-- Add missing index on ods_view_details
ALTER TABLE `ods_view_details` ADD KEY `index_id` (`index_id`);
