-- Change version of Centreon
UPDATE `informations` SET `value` = '18.9.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.23' LIMIT 1;
