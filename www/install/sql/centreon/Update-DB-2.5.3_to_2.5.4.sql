
-- Change version of Centreon
UPDATE `informations` SET `value` = '2.5.4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.5.3' LIMIT 1;
