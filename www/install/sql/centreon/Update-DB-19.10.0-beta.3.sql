--
-- Add new field for Remote Server option
--
ALTER TABLE nagios_server ADD COLUMN `remote_server_centcore_ssh_proxy` enum('0','1') NOT NULL DEFAULT '1';

-- Add severity preference on host-monitoring widget
INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES
('hostSeverityMulti', 1);

INSERT INTO `options` (`key`, `value`) VALUES ('inheritance_mode', '1');