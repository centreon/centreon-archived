-- Change version of Centreon
UPDATE `informations` SET `value` = '19.04.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.5' LIMIT 1;
