
UPDATE `informations` SET `value` = '2.1.12' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1.11' LIMIT 1;