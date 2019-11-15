UPDATE `comments` SET `service_id` = 0 WHERE `service_id` IS NULL;
ALTER TABLE `comments` MODIFY COLUMN `service_id` int(11) NOT NULL DEFAULT 0;

UPDATE `downtimes` SET `service_id` = 0 WHERE `service_id` IS NULL;
ALTER TABLE `downtimes`
    MODIFY COLUMN `service_id` int(11) NOT NULL DEFAULT 0,
    DROP INDEX `entry_time`,
    ADD UNIQUE KEY `entry_time` (`entry_time`, `host_id`, `service_id`, `instance_id`, `internal_id`);
