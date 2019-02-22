-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.4' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.3' LIMIT 1;
