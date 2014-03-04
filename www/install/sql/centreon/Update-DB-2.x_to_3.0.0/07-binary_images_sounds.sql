-- Table images
CREATE TABLE `binaries` (
    `binary_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `checksum` VARCHAR(255) NOT NULL,
    `mimetype` VARCHAR(255) NOT NULL,
    `binary` MEDIUMBLOB NOT NULL,
    PRIMARY KEY(`binary_id`),
    KEY `checksum` (`checksum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `image_type` (
    `image_type_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_name` VARCHAR(255) NOT NULL,
    `module_id` INT NOT NULL,
    PRIMARY KEY (`image_type_id`),
    UNIQUE (`type_name`),
    KEY (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `image_type` ADD FOREIGN KEY (`module_id`) REFERENCES `module_informations` (`id`) ON DELETE CASCADE;

INSERT INTO `image_type` (`image_type_id`, `type_name`, `module_id`) VALUES (1, 'Icons', 1);

CREATE TABLE `image_type_view_img_relation` (
    `image_type_id` INT UNSIGNED NOT NULL,
    `img_id` INT NOT NULL,
    PRIMARY KEY (`image_type_id`, `img_id`),
    FOREIGN KEY (`img_id`) REFERENCES `view_img` (`img_id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `image_type_view_img_relation`
    ADD FOREIGN KEY (`image_type_id`) REFERENCES `image_type` (`image_type_id`) ON DELETE CASCADE,
    ADD FOREIGN KEY (`img_id`) REFERENCES `view_img` (`img_id`) ON DELETE CASCADE;

-- TODO for after migration
-- ALTER TABLE `view_img` ADD COLUMN `binary_id` INT UNSIGNED NOT NULL,
--    KEY (`binary_id`);
-- ALTER TABLE `view_img` ADD FOREIGN KEY (`binary_id`) REFERENCES `binaries` (`binary_id`) ON DELETE CASCADE;
-- DROP TABLE `view_img_dir_relation`;
-- DROP TABLE `view_img_dir`;
