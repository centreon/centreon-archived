-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.11' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.10' LIMIT 1;
