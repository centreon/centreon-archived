--
-- Update for reporting
--

ALTER TABLE  `log_archive_last_status` ADD  `host_id` INT NULL;
ALTER TABLE  `log_archive_last_status` ADD  `service_id` INT NULL ;
ALTER TABLE `log_archive_last_status` DROP `id` ;

ALTER TABLE `log_archive_host`  ENGINE = MYISAM;
ALTER TABLE `log_archive_service`  ENGINE = MYISAM;
