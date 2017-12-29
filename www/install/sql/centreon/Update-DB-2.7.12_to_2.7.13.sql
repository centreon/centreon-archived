-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.13' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.12' LIMIT 1;
