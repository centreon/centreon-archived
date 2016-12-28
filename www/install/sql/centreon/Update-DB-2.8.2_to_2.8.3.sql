-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.3' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.2' LIMIT 1;

-- broker option
DELETE FROM `options` WHERE `key` = 'broker';
INSERT INTO `options` (`key`, `value`) VALUES ('broker', 'broker');
