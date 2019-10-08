-- migrate downtimes.end_time & downtimes.duration columns from int(11) to bigint(20)
ALTER TABLE `downtimes` MODIFY COLUMN `end_time` BIGINT(20) NULL DEFAULT NULL;
ALTER TABLE `downtimes` MODIFY COLUMN `duration` BIGINT(20) NULL DEFAULT NULL;
