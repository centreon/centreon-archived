-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.2' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.1' LIMIT 1;
