-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.10' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.9' LIMIT 1;

DELETE FROM nagios_macro WHERE macro_name IN ('$_HOSTLOCATION$', '$_HOSTHOST_ID$', '$_SERVICESERVICE_ID$');
INSERT INTO `nagios_macro` (`macro_name`) VALUES ('$HOSTID$');
INSERT INTO `nagios_macro` (`macro_name`) VALUES ('$SERVICEID$');
INSERT INTO `nagios_macro` (`macro_name`) VALUES ('$HOSTTIMEZONE$');