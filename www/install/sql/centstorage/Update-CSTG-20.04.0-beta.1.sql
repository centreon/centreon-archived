DELETE `comments` FROM `comments`
LEFT OUTER JOIN (
	SELECT MIN(comment_id) as comment_id, `entry_time`,`host_id`,`service_id`,`instance_id`,`internal_id`
	FROM `comments`
	GROUP BY `entry_time`,`host_id`,`service_id`,`instance_id`,`internal_id`
) AS t1
ON t1.comment_id = comments.comment_id
WHERE t1.comment_id IS NULL;

UPDATE `comments` SET `service_id` = 0 WHERE `service_id` IS NULL;
ALTER TABLE `comments` MODIFY COLUMN `service_id` int(11) NOT NULL DEFAULT 0;

DELETE `downtimes` FROM `downtimes`
LEFT OUTER JOIN (
	SELECT MIN(downtime_id) as downtime_id, `entry_time`,`host_id`,`service_id`,`instance_id`,`internal_id`
	FROM `downtimes`
	GROUP BY `entry_time`,`host_id`,`service_id`,`instance_id`,`internal_id`
) AS t1
ON t1.downtime_id = downtimes.downtime_id
WHERE t1.downtime_id IS NULL;

UPDATE `downtimes` SET `service_id` = 0 WHERE `service_id` IS NULL;
ALTER TABLE `downtimes`
    MODIFY COLUMN `service_id` int(11) NOT NULL DEFAULT 0,
    DROP INDEX `entry_time`,
    ADD UNIQUE KEY `entry_time` (`entry_time`, `host_id`, `service_id`, `instance_id`, `internal_id`);
