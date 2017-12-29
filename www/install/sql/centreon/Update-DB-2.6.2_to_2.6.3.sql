-- Change version of Centreon
UPDATE `informations` SET `value` = '2.6.3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.2' LIMIT 1;
