-- Add a group for options
ALTER TABLE `options` ADD COLUMN `group` VARCHAR(255) NOT NULL DEFAULT 'default' FIRST;
