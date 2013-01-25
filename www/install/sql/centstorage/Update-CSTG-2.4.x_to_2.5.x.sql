-- /!\ WARNING /!\
-- This file must be renamed once we know the exact source and target versions.
-- /!\ WARNING /!\
ALTER TABLE `index_data` ADD COLUMN `rrd_retention` INT(11) DEFAULT NULL AFTER `to_delete`;
