--- CREATE TABLES FOR VAULT CONFIGURATION ---
CREATE TABLE IF NOT EXISTS `vault_configuration` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `type_id` INT UNSIGNED NOT NULL,
  `url` VARCHAR(1024) NOT NULL,
  `port` SMALLINT UNSIGNED NOT NULL,
  `storage` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vault_configuration` (`url`, `port`, `storage`),
  CONSTRAINT `vault_configuration_type_id`
    FOREIGN KEY (`type_id`)
    REFERENCES `vault` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vault` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
