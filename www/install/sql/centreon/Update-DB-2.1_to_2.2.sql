
UPDATE `informations` SET `value` = '2.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1' LIMIT 1;
