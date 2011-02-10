ALTER TABLE `traps` ADD `traps_advanced_treatment` enum('0','1') default '0' AFTER `traps_submit_result_enable`; 
 
UPDATE `informations` SET `value` = '2.2.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.2.0-RC1' LIMIT 1;