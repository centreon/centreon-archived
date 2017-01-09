
ALTER TABLE `contact` CHANGE `contact_register` `contact_register` TINYINT( 6 ) NOT NULL DEFAULT '1';

ALTER TABLE `cfg_centreonbroker` ADD COLUMN `config_filename` VARCHAR(255) NOT NULL AFTER `config_name`;

UPDATE `contact` SET contact_register = '2' WHERE contact_register = '1';
UPDATE `contact` SET contact_register = '1' WHERE contact_register = '0';
UPDATE `contact` SET contact_register = '0' WHERE contact_register = '2';

UPDATE `informations` SET `value` = '2.3.0-RC1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-b4' LIMIT 1;