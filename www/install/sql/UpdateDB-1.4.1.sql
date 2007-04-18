-- 17/04/2007
ALTER TABLE `traps` ADD `traps_status` ENUM( '-1', '0', '1', '2', '3' ) NULL DEFAULT NULL ;
ALTER TABLE `traps` ADD `manufacturer_id` INT( 11 ) NOT NULL ;
ALTER TABLE `traps` CHANGE `traps_comments` `traps_comments` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;