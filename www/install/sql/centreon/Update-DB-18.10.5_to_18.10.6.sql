-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.6' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.5' LIMIT 1;

-- Change traps_execution_command from varchar(255) to text
ALTER TABLE `traps` MODIFY COLUMN `traps_execution_command` text DEFAULT NULL;
