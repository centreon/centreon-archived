
ALTER TABLE `contact` CHANGE `contact_enable_notifications` `contact_enable_notifications` ENUM(  '0',  '1',  '2' ) DEFAULT '2';

UPDATE `informations` SET `value` = '2.4.0-RC8' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC7' LIMIT 1;