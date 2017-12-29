
ALTER TABLE `logs` ADD INDEX(`host_id`, `service_id`, `msg_type`, `ctime`, `status`);
ALTER TABLE `logs` ADD INDEX(`host_id`, `msg_type`, `ctime`, `status`);
