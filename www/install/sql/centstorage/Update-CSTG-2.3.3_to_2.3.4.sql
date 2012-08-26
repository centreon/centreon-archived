ALTER TABLE `centreon_acl` DROP `id`;
ALTER TABLE `index_data` ADD UNIQUE `host_service_unique_id` (`host_id`, `service_id`);
ALTER TABLE `metrics` ADD UNIQUE (`index_id`, `metric_name`);