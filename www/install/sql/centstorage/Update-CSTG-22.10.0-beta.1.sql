ALTER TABLE `hosts`
    MODIFY COLUMN `notification_number` UNSIGNED BIGINT(20) DEFAULT NULL;

ALTER TABLE `services`
    MODIFY COLUMN `notification_number` UNSIGNED BIGINT(20) DEFAULT NULL;