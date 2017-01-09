-- Change ip_address length to support ipv6 address
ALTER TABLE `session` MODIFY COLUMN ip_address varchar(45);

-- Fixes column name
ALTER TABLE `timeperiod_exclude_relations` CHANGE COLUMN `include_id` `exclude_id` int(11) AUTO_INCREMENT;

-- Delete an old cron - #4283
DELETE FROM cron_operation WHERE name LIKE 'archiveDayLog';

-- Delete mysterious page - #4355
DELETE FROM `topology` WHERE `topology_page` = 50802 AND `topology_name` = 'Configuration';

UPDATE `informations` SET `value` = '2.4.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.1' LIMIT 1;
