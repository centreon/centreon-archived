-- Change traps_execution_command from varchar(255) to text
ALTER TABLE `traps` MODIFY COLUMN `traps_execution_command` text DEFAULT NULL;
