ALTER TABLE `contact` CHANGE `contact_location` `contact_location` int(11) DEFAULT NULL;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.6' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.5' LIMIT 1;
