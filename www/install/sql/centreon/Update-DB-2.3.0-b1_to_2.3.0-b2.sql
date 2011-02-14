
ALTER TABLE nagios_server ADD is_default INT DEFAULT '0' AFTER localhost;

UPDATE `informations` SET `value` = '2.3.0-b2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-b1' LIMIT 1;
 