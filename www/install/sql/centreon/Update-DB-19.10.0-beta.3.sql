--
-- Add new field for Remote Server option
--
ALTER TABLE nagios_server ADD COLUMN `remote_server_centcore_ssh_proxy` enum('0','1') NOT NULL DEFAULT '1';

-- Add fields to manage restart/reload commands of centengine/cbd
ALTER TABLE `nagios_server` DROP COLUMN `init_script`;
ALTER TABLE `nagios_server` ADD COLUMN `engine_restart_command` varchar(255) DEFAULT 'service centengine restart' AFTER `monitoring_engine`;
ALTER TABLE `nagios_server` ADD COLUMN `engine_reload_command` varchar(255) DEFAULT 'service centengine reload' AFTER `engine_restart_command`;
ALTER TABLE `nagios_server` ADD COLUMN `broker_reload_command` varchar(255) DEFAULT 'service cbd reload' AFTER `nagios_perfdata`;

-- Remove broker init script name from general monitoring options
DELETE FROM `options` WHERE `key` = 'broker_correlator_script';
