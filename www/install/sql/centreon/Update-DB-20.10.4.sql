-- Add new column
ALTER TABLE `platform_topology` ADD COLUMN `pending` enum('0','1') DEFAULT ('1') AFTER `parent_id`;
