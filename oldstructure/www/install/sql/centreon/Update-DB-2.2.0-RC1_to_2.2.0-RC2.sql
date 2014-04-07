ALTER TABLE `traps` ADD `traps_advanced_treatment` enum('0','1') default '0' AFTER `traps_submit_result_enable`;

-- Delete the Directories page for Media
DELETE FROM `topology` WHERE `topology_page` = 5010202;

-- Add the default engine
INSERT INTO `options` (`key`, `value`) VALUES ('monitoring_engine', 'NAGIOS'); 

UPDATE `informations` SET `value` = '2.2.0-RC2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.2.0-RC1' LIMIT 1;