-- OPTIMIZE Monitoring
ALTER TABLE services ADD INDEX last_hard_state_change (last_hard_state_change);

-- Add Timezone in hosts TABLE
ALTER TABLE `hosts` ADD COLUMN `timezone` varchar(64) DEFAULT NULL AFTER `statusmap_image`;
