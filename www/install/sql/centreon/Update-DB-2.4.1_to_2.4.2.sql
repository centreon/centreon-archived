-- Change ip_address length to support ipv6 address
ALTER TABLE `session` MODIFY COLUMN ip_address varchar(45);

UPDATE `informations` SET `value` = '2.4.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.1' LIMIT 1;
