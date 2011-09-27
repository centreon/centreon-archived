ALTER TABLE `host` CHANGE `host_register` `host_register` ENUM('0','1','2') NOT NULL DEFAULT '0';
ALTER TABLE `service` CHANGE `service_register` `service_register` ENUM('0','1','2') NOT NULL DEFAULT '0';

UPDATE `informations` SET `value` = '2.2.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.2.1' LIMIT 1;