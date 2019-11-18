-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.8' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.7' LIMIT 1;

-- fix default contact_autologin_key
UPDATE `contact` SET `contact_autologin_key` = NULL WHERE `contact_autologin_key` ='';

-- add columns for engine configuration
ALTER TABLE `cfg_nagios` ADD COLUMN `enable_macros_filter` ENUM('0', '1') DEFAULT '0';
ALTER TABLE `cfg_nagios` ADD COLUMN `macros_filter` TEXT DEFAULT '';
