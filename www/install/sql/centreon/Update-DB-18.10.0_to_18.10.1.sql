-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.1' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.0' LIMIT 1;
