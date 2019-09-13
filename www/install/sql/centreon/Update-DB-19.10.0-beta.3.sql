--
-- Add new field for Remote Server option
--
ALTER TABLE nagios_server ADD COLUMN `remote_server_centcore_ssh_proxy` enum('0','1') NOT NULL DEFAULT '1';

-- Add severity preference on host-monitoring widget
INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES
('hostSeverityMulti', 1);

-- Remove broker correlation mechanism
ALTER TABLE `cfg_centreonbroker` DROP COLUMN `correlation_activate`;
DELETE FROM `cb_field` WHERE
  `displayname` = 'Correlation file'
  OR `description` LIKE 'File where correlation%'
  OR `displayname` = 'Correlation passive';
DELETE FROM `cb_type` WHERE `type_shortname` = 'correlation';
