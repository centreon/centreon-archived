-- Remove broker correlation mechanism
ALTER TABLE `cfg_centreonbroker` DROP COLUMN `correlation_activate`;
DELETE FROM `cb_field` WHERE
  `displayname` = 'Correlation file'
  OR `description` LIKE 'File where correlation%'
  OR `displayname` = 'Correlation passive';
DELETE FROM `cb_type` WHERE `type_shortname` = 'correlation';

-- Update topology of service grid / by host group / by service group
UPDATE topology SET topology_url_opt = '&o=svcOV_pb' WHERE topology_page = 20204;
UPDATE topology SET topology_url_opt = '&o=svcOVHG_pb' WHERE topology_page = 20209;
UPDATE topology SET topology_url_opt = '&o=svcOVSG_pb' WHERE topology_page = 20212;

-- Delete legacy engine parameters
ALTER TABLE `cfg_nagios` DROP COLUMN `check_result_path`;
ALTER TABLE `cfg_nagios` DROP COLUMN `use_check_result_path`;
ALTER TABLE `cfg_nagios` DROP COLUMN `max_check_result_file_age`;

-- Update nagios_server to add gorgone connection
ALTER TABLE `nagios_server` ADD `gorgone_communication_type` enum('1','2') NOT NULL DEFAULT '1' AFTER `centreonconnector_path`;
ALTER TABLE `nagios_server` CHANGE `ssh_port` `gorgone_port` INT(11) NULL;
ALTER TABLE `nagios_server` CHANGE `remote_server_centcore_ssh_proxy` `remote_server_use_as_proxy` enum('0','1') NOT NULL DEFAULT '1';
ALTER TABLE `nagios_server` DROP COLUMN `ssh_private_key`;
UPDATE `nagios_server` SET `gorgone_communication_type` = '2';
-- Update options for gorgone
UPDATE options SET `key` = 'gorgone_illegal_characters' WHERE `key` = 'centcore_illegal_characters';
UPDATE options SET `key` = 'gorgone_cmd_timeout' WHERE `key` = 'centcore_cmd_timeout';
UPDATE topology SET topology_url_opt = '&o=gorgone', topology_name = 'Gorgone' WHERE topology_page = 50117;
UPDATE options SET `key` = 'debug_gorgone' WHERE `key` = 'debug_centcore';
DELETE FROM `options` WHERE `key` = 'enable_perfdata_sync';
DELETE FROM `options` WHERE `key` = 'enable_logs_sync';