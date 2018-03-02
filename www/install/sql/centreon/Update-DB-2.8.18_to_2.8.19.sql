-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.19' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.18' LIMIT 1;

-- Add index to ws_token
ALTER TABLE `ws_token` ADD INDEX `index1` (`generate_date`);