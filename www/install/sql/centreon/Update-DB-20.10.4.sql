-- Add new column
ALTER TABLE `cfg_nagios` ADD COLUMN `postpone_notification_to_timeperiod` boolean DEFAULT false AFTER `nagios_group`;
