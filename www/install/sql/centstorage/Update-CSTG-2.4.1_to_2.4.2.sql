ALTER TABLE `downtimes` ADD INDEX `downtimeManager_hostList` (`host_id`, `start_time`);
alter table instance add last_ctime int(11) default 0 after log_md5;
