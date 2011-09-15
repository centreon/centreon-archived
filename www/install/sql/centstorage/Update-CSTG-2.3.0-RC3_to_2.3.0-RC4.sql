ALTER TABLE  `hoststateevents` CHANGE  `hoststateevents_id`  `hoststateevent_id` INT( 11 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE  `servicestateevents` CHANGE  `servicestateevents_id`  `servicestateevent_id` INT( 11 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hoststateevents` ADD `ack_time` INT NULL DEFAULT NULL;
ALTER TABLE `servicestateevents` ADD `ack_time` INT NULL DEFAULT NULL;