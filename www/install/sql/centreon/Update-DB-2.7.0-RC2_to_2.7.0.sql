-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.0-RC2' LIMIT 1;

--Insert default directory images
INSERT INTO `options` (`key`, `value`)
SELECT * FROM (SELECT 'nagios_path_img', '@INSTALL_DIR_CENTREON@/www/img/media/') AS tmp
WHERE NOT EXISTS (SELECT `key` FROM `options` WHERE `key` = 'nagios_path_img' ) LIMIT 1;