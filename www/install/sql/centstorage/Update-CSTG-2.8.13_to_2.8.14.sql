ALTER TABLE `comments`
  DROP INDEX `entry_time`,
  ADD UNIQUE KEY `entry_time` (`entry_time`,`host_id`,`service_id`, `instance_id`, `internal_id`);

ALTER TABLE `downtimes`
  DROP INDEX `entry_time`,
  ADD UNIQUE KEY `entry_time` (`entry_time`,`instance_id`,`internal_id`);

