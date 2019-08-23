--
-- Add new field for Remote Server option
--
ALTER TABLE nagios_server ADD COLUMN `remote_server_centcore_ssh_proxy` enum('0','1') NOT NULL DEFAULT '1';

-- Remove broker init script name from general monitoring options
DELETE FROM `options` WHERE `key` = 'broker_correlator_script';
