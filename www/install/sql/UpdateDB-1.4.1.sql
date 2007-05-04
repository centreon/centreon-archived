-- 17/04/2007
ALTER TABLE `traps` ADD `traps_status` ENUM( '-1', '0', '1', '2', '3' ) NULL DEFAULT NULL ;
ALTER TABLE `traps` ADD `manufacturer_id` INT( 11 ) NOT NULL ;
ALTER TABLE `traps` CHANGE `traps_comments` `traps_comments` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;

DELETE FROM `topology` WHERE `topology`.`topology_page` = 40202 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40201 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40203 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` = 40208 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` IS NULL AND `topology`.`topology_parent` = 402 AND `topology`.`topology_group` = 41 LIMIT 1;
DELETE FROM `topology` WHERE `topology`.`topology_page` IS NULL AND `topology`.`topology_parent` = 402 AND `topology`.`topology_group` = 42 LIMIT 1;
