--new updated field of pollers-
ALTER TABLE `nagios_server` ADD COLUMN `updated` enum('0','1') NOT NULL DEFAULT '0';
