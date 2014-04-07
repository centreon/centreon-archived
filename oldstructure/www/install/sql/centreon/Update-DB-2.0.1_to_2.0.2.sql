
UPDATE `informations` SET `value` = '2.0.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.0.1' LIMIT 1;

ALTER TABLE `nagios_server` ADD `nagios_perfdata` VARCHAR( 255 ) NULL ;
UPDATE nagios_server SET nagios_perfdata = (SELECT service_perfdata_file FROM `cfg_nagios` WHERE service_perfdata_file IS NOT NULL LIMIT 1);
