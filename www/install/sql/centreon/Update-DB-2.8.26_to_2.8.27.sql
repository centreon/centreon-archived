-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.27' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.26' LIMIT 1;

-- Add default illegal characters for centcore external commands
INSERT INTO `options` (`key`, `value`) VALUES ('centcore_illegal_characters', '`');