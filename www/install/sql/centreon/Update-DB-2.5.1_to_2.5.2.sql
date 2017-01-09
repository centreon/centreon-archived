
-- Change version of Centreon
UPDATE `informations` SET `value` = '2.5.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.5.1' LIMIT 1;
