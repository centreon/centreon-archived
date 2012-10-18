-- Delete OLD field into a storage configuration 
DELETE FROM cb_field WHERE cb_field_id IN (16, 17);

UPDATE `informations` SET `value` = '2.4.0-RC6' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC5' LIMIT 1;
