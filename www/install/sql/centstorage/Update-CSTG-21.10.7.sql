ALTER TABLE services
    MODIFY COLUMN `action_url` VARCHAR(2048) DEFAULT NULL,
    MODIFY COLUMN `notes_url` VARCHAR(2048) DEFAULT NULL,
    MODIFY COLUMN `notes` VARCHAR(512) DEFAULT NULL;

ALTER TABLE hosts
    MODIFY COLUMN `action_url` VARCHAR(2048) DEFAULT NULL,
    MODIFY COLUMN `notes_url` VARCHAR(2048) DEFAULT NULL,
    MODIFY COLUMN `notes` VARCHAR(512) DEFAULT NULL;

