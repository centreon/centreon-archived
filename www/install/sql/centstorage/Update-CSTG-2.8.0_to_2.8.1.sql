-- Update entry_time unique key in comments table
ALTER TABLE `comments` DROP KEY `entry_time`, ADD UNIQUE KEY `entry_time` (`entry_time`,`host_id`,`service_id`, `instance_id`, `internal_id`);
