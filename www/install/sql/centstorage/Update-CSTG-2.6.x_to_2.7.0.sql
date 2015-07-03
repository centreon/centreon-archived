ALTER TABLE `hoststateevents` ADD COLUMN `in_ack` tinyint(4) DEFAULT '0';
ALTER TABLE `servicestateevents` ADD COLUMN `in_ack` tinyint(4) DEFAULT '0';

