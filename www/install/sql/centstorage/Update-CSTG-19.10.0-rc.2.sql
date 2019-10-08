-- migrate downtimes.start_time & downtimes.end_time column from int(11) to timestamp
ALTER TABLE `downtimes` ADD COLUMN `start_time2` TIMESTAMP NULL DEFAULT NULL AFTER `start_time`;
UPDATE `downtimes` SET `start_time2` = FROM_UNIXTIME(`start_time`);
ALTER TABLE `downtimes` DROP COLUMN `start_time`;
ALTER TABLE `downtimes` CHANGE `start_time2` `start_time` TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE `downtimes` ADD COLUMN `end_time2` TIMESTAMP NULL DEFAULT NULL AFTER `end_time`;
UPDATE `downtimes` SET `end_time2` = FROM_UNIXTIME(`end_time`);
ALTER TABLE `downtimes` DROP COLUMN `end_time`;
ALTER TABLE `downtimes` CHANGE `end_time2` `end_time` TIMESTAMP NULL DEFAULT NULL;