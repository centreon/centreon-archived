-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.6' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.5' LIMIT 1;

-- Change traps_execution_command from varchar(255) to text
ALTER TABLE `traps` MODIFY COLUMN `traps_execution_command` text DEFAULT NULL;

--
-- Change IP field from varchar(16) to varchar(255)
--
ALTER TABLE `remote_servers` MODIFY COLUMN `ip` VARCHAR(255) NOT NULL;

-- Add trap regexp matching
ALTER TABLE `traps` ADD COLUMN IF NOT EXISTS `traps_mode` ENUM('0', '1') DEFAULT '0' AFTER `traps_oid`;

-- Add trap filter
ALTER TABLE `traps` MODIFY COLUMN `traps_exec_interval_type` ENUM('0','1','2','3') NULL DEFAULT '0';
