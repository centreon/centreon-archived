-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.10' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.9' LIMIT 1;

DELETE FROM nagios_macro WHERE macro_name IN ('$_HOSTLOCATION$', '$_HOSTHOST_ID$', '$_SERVICESERVICE_ID$');
INSERT INTO `nagios_macro` (`macro_name`) VALUES ('$HOSTID$');
INSERT INTO `nagios_macro` (`macro_name`) VALUES ('$SERVICEID$');
INSERT INTO `nagios_macro` (`macro_name`) VALUES ('$HOSTTIMEZONE$');

ALTER TABLE `cfg_nagios` DROP COLUMN `log_initial_states`;
ALTER TABLE `cfg_nagios` ADD COLUMN `use_timezone` int(11) unsigned DEFAULT NULL AFTER `nagios_name`,
ADD CONSTRAINT `cfg_nagios_ibfk_27` FOREIGN KEY (`use_timezone`) REFERENCES `timezone` (`timezone_id`) ON DELETE CASCADE;

ALTER TABLE `service` DROP COLUMN `service_inherit_contacts_from_host`;