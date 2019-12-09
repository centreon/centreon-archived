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

-- Update topology of service grid / by host group / by service group
ALTER TABLE `nagios_server` ADD `gorgone_communication_type` enum('1','2') NOT NULL DEFAULT '1' AFTER `centreonconnector_path`;
ALTER TABLE `nagios_server` CHANGE `ssh_port` `gorgone_port` INT(11) NULL;
ALTER TABLE `nagios_server` CHANGE `remote_server_centcore_ssh_proxy` `remote_server_use_as_proxy` enum('0','1') NOT NULL DEFAULT '1';
ALTER TABLE `nagios_server` DROP COLUMN `ssh_private_key`;

