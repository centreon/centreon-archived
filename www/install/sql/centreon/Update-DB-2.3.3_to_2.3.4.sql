
UPDATE `informations` SET `value` = '2.3.4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.3' LIMIT 1;