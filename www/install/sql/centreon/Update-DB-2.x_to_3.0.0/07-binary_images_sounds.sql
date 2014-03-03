-- Table images
CREATE TABLE `binaries` (
    `binary_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(255) NOT NULL,
    `checksum` VARCHAR(255) NOT NULL,
    `mimetype` VARCHAR(255) NOT NULL,
    `filetype` TINYINT(1) NOT NULL DEFAULT 1,
    `binary` MEDIUMBLOB NOT NULL,
    PRIMARY KEY(`binary_id`),
    KEY `file` (`filetype`, `filename`),
    KEY `checksum` (`checksum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
