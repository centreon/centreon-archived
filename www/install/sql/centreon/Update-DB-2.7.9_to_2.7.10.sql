-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.10' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.9' LIMIT 1;
