ALTER TABLE `hoststateevents` ADD UNIQUE (`host_id` , `start_time`);
ALTER TABLE `hoststateevents` ADD INDEX ( `end_time` ); 
ALTER TABLE `hoststateevents` ADD INDEX ( `start_time` ); 

ALTER TABLE `servicestateevents` ADD UNIQUE (`host_id` , `service_id` , `start_time`);
ALTER TABLE `servicestateevents` ADD INDEX ( `end_time` );
ALTER TABLE `servicestateevents` ADD INDEX ( `start_time` );

-- ALTER TABLE `hoststateevents` DROP FOREIGN KEY `hoststateevents_ibfk_1`;
-- ALTER TABLE `servicestateevents` DROP FOREIGN KEY `servicestateevents_ibfk_1`;
