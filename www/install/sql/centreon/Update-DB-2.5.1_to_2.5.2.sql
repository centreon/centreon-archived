
ALTER TABLE `nagios_server` CHANGE `init_script_snmptt` `init_script_centreontrapd` VARCHAR(255);

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.5.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.5.1' LIMIT 1;
