-- Table images
CREATE TABLE `binaries` (
    `binary_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(255) NOT NULL,
    `checksum` VARCHAR(255) NOT NULL,
    `mimetype` VARCHAR(255) NOT NULL,
    `filetype` TINYINT(1) NOT NULL DEFAULT 1,
    `binary_content` MEDIUMBLOB NOT NULL,
    PRIMARY KEY(`binary_id`),
    UNIQUE `binaries_idx01` (`checksum`, `mimetype`),
    UNIQUE `binaries_idx02` (`filename`, `filetype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `binary_type` (
    `binary_type_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_name` VARCHAR(255) NOT NULL,
    `module_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`binary_type_id`),
    UNIQUE (`type_name`),
    KEY (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `binary_type` ADD FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE;

INSERT INTO `binary_type` (`binary_type_id`, `type_name`, `module_id`) VALUES (1, 'Icons', 1);

CREATE TABLE `binary_type_binaries_relation` (
    `binary_type_id` INT UNSIGNED NOT NULL,
    `binary_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`binary_type_id`, `binary_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `binary_type_binaries_relation`
    ADD FOREIGN KEY (`binary_type_id`) REFERENCES `binary_type` (`binary_type_id`) ON DELETE CASCADE,
    ADD FOREIGN KEY (`binary_id`) REFERENCES `binaries` (`binary_id`) ON DELETE CASCADE;

-- TODO for after migration
-- DROP TABLE `view_img_dir_relation`;
-- DROP TABLE `view_img_dir`;
-- DROP TABLE `view_img`;
