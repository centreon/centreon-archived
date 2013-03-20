ALTER TABLE `downtimes` ADD INDEX `downtimeManager_hostList` (`host_id`, `start_time`);

-- Ticket #4299 --
ALTER TABLE nagios_acknowledgements ADD INDEX idx_reporting_ack (object_id, acknowledgement_id, entry_time);
ALTER TABLE nagios_downtimehistory ADD INDEX idx_reporting_donwtime (was_started, actual_start_time, actual_end_time);

