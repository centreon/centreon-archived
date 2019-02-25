-- Add size to max for CPU Graph template
UPDATE `service` SET `service_alias` = 'Swap' WHERE `service_description` = 'SNMP-Linux-Swap';

-- Add possibility to lock a service template
ALTER TABLE `service` ADD `service_locked` BOOLEAN DEFAULT 0 AFTER `command_command_id_arg2`;
