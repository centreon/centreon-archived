-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.1' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.0' LIMIT 1;

-- Drop from nagios configuration
ALTER TABLE `cfg_nagios` DROP COLUMN precached_object_file;
ALTER TABLE `cfg_nagios` DROP COLUMN object_cache_file;