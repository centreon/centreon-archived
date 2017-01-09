UPDATE `informations` SET `value` = '2.0-RC6' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.0-RC5' LIMIT 1;
