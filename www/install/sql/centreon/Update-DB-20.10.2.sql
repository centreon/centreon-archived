-- Add new column
ALTER TABLE `cfg_centreonbroker` ADD COLUMN `pool_size` INT(11) DEFAULT NULL AFTER `daemon`;
