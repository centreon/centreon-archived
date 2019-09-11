--
-- Add new field for Remote Server option
--
ALTER TABLE nagios_server ADD COLUMN `remote_server_centcore_ssh_proxy` enum('0','1') NOT NULL DEFAULT '1';

-- Add severity preference on host-monitoring widget
INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES
('hostSeverityMulti', 1);

-- Create rs_poller_relation for the additional relationship between poller and remote servers
CREATE TABLE `rs_poller_relation` (
  `remote_server_id` int(11) NOT NULL DEFAULT '0',
  `poller_server_id` int(11) NOT NULL DEFAULT '0',
  KEY `remote_server_id` (`remote_server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relation Table For centreon pollers and remote servers';
