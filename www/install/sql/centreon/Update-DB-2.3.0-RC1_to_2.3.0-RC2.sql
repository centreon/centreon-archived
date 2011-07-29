ALTER TABLE `host` CHANGE `host_register` `host_register` ENUM('0','1','2','3') NOT NULL DEFAULT '0';
ALTER TABLE `service` CHANGE `service_register` `service_register` ENUM('0','1','2','3') NOT NULL DEFAULT '0';

UPDATE `informations` SET `value` = '2.3.0-RC2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-RC1' LIMIT 1;