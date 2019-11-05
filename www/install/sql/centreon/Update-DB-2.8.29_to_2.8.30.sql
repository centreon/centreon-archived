-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.30' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.29' LIMIT 1;

-- fix default contact_autologin_key
UPDATE `contact` SET `contact_autologin_key` = NULL WHERE `contact_autologin_key` =''