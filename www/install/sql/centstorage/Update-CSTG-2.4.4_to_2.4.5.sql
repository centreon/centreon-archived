
ALTER TABLE `index_data` ADD COLUMN `rrd_retention` INT(11) DEFAULT NULL AFTER `to_delete`;

-- Add warn low
ALTER TABLE `metrics` ADD COLUMN `warn_low` float DEFAULT NULL AFTER `warn`;
ALTER TABLE `metrics` ADD COLUMN `warn_threshold_mode` enum('0','1') DEFAULT NULL AFTER `warn_low`;
ALTER TABLE `metrics` ADD COLUMN `crit_low` float DEFAULT NULL AFTER `crit`;
ALTER TABLE `metrics` ADD COLUMN `crit_threshold_mode` enum('0','1') DEFAULT NULL AFTER `crit_low`;

ALTER TABLE `downtimes` ADD COLUMN `actual_start_time` int(11) DEFAULT NULL AFTER `start_time`;
ALTER TABLE `downtimes` ADD COLUMN `actual_end_time` int(11) DEFAULT NULL AFTER `actual_start_time`;
