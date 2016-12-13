-- Change version of Centreon
UPDATE `informations` SET `value` = '2.9.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.1' LIMIT 1;

ALTER TABLE `nagios_server` 
ADD COLUMN `use_sudo` int(11) NULL DEFAULT '1' AFTER `is_default`;