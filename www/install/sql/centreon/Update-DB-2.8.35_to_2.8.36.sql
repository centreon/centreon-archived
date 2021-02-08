-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.36' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.35' LIMIT 1;
