-- Add size to max for CPU Graph template
UPDATE `service` SET `service_alias` = 'Swap' WHERE `service_description` = 'SNMP-Linux-Swap';

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.4.4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.3' LIMIT 1;
