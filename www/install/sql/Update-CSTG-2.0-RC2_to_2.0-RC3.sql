
ALTER TABLE `index_data` ADD `hidden` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `special` , ADD `locked` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `hidden` ;