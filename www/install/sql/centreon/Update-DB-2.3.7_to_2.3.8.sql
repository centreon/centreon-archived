UPDATE `informations` SET `value` = '2.3.8' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.7' LIMIT 1;
