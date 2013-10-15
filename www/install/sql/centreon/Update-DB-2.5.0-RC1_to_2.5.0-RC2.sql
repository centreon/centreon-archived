ALTER TABLE `on_demand_macro_host` ADD COLUMN `is_password` TINYINT(2) DEFAULT NULL AFTER `host_macro_value`;
ALTER TABLE `on_demand_macro_service` ADD COLUMN `is_password` TINYINT(2) DEFAULT NULL AFTER `svc_macro_value`;

UPDATE `informations` SET `value` = '2.5.0-RC2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.5.0-RC1' LIMIT 1;
