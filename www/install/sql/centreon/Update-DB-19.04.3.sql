-- Change traps_execution_command from varchar(255) to text
ALTER TABLE `traps` MODIFY COLUMN `traps_execution_command` text DEFAULT NULL;

-- Add trap regexp matching
ALTER TABLE `traps` ADD `traps_mode` ENUM('0', '1') DEFAULT '0' AFTER `traps_oid`;

-- Add trap filter
ALTER TABLE `traps` MODIFY COLUMN `traps_exec_interval_type` ENUM('0','1','2','3') NULL DEFAULT '0';
