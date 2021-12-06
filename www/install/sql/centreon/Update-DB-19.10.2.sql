--new updated field of pollers-
SET SESSION innodb_strict_mode=OFF;
ALTER TABLE `nagios_server` ADD COLUMN `updated` enum('0','1') NOT NULL DEFAULT '0';
SET SESSION innodb_strict_mode=ON;
