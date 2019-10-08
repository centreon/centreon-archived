-- migrate downtimes.end_time column from int(11) to bigint(20)
ALTER TABLE `downtimes` MODIFY COLUMN `end_time` BIGINT(20) NULL DEFAULT NULL;
