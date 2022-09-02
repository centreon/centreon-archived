ALTER TABLE downtimes
    MODIFY COLUMN `entry_time` INT(11) UNSIGNED DEFAULT NULL,
    MODIFY COLUMN `deletion_time` INT(11) UNSIGNED DEFAULT NULL,
    MODIFY COLUMN `duration` INT(11) UNSIGNED DEFAULT NULL,
    MODIFY COLUMN `end_time` INT(11) UNSIGNED DEFAULT NULL,
    MODIFY COLUMN `start_time` INT(11) UNSIGNED DEFAULT NULL,
    MODIFY COLUMN `actual_start_time` INT(11) UNSIGNED DEFAULT NULL,
    MODIFY COLUMN `actual_end_time` INT(11) UNSIGNED DEFAULT NULL;

CREATE INDEX `sg_name_idx` ON servicegroups(`name`);
CREATE INDEX `hg_name_idx` ON hostgroups(`name`);
CREATE INDEX `instances_name_idx` ON instances(`name`);