-- 17/04/2007
ALTER TABLE `traps` CHANGE `traps_status` `traps_status` ENUM( '-1', '0', '1', '2', '3' ) NULL DEFAULT NULL 
ALTER TABLE `traps` ADD `manufacturer_id` INT( 11 ) NOT NULL ;