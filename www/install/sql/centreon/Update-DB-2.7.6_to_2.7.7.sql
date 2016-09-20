-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.7' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.6' LIMIT 1;
