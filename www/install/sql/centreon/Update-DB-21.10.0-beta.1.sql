-- Drop legacy API authentication table

DROP TABLE `ws_token`;

-- Create authentication tables and insert local configuration

CREATE TABLE `provider_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT 1,
  `is_forced` BOOLEAN NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `provider_configuration` (type, name, is_active, is_forced)
VALUES ('local', 'local', true, true);

CREATE TABLE `security_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `creation_date` bigint UNSIGNED NOT NULL,
  `expiration_date` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `token_index` (`token`),
  INDEX `expiration_index` (`expiration_date`),
  UNIQUE KEY `unique_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `security_authentication_tokens` (
  `token` varchar(255) NOT NULL,
  `provider_token_id` int(11) DEFAULT NULL,
  `provider_token_refresh_id` int(11) DEFAULT NULL,
  `provider_configuration_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`token`),
  KEY `security_authentication_tokens_token_fk` (`token`),
  KEY `security_authentication_tokens_provider_token_id_fk` (`provider_token_id`),
  KEY `security_authentication_tokens_provider_token_refresh_id_fk` (`provider_token_refresh_id`),
  KEY `security_authentication_tokens_configuration_id_fk` (`provider_configuration_id`),
  KEY `security_authentication_tokens_user_id_fk` (`user_id`),
  CONSTRAINT `security_authentication_tokens_configuration_id_fk` FOREIGN KEY (`provider_configuration_id`)
  REFERENCES `provider_configuration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_provider_token_id_fk` FOREIGN KEY (`provider_token_id`)
  REFERENCES `security_token` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_provider_token_refresh_id_fk` FOREIGN KEY (`provider_token_refresh_id`)
  REFERENCES `security_token` (`id`) ON DELETE SET NULL,
  CONSTRAINT `security_authentication_tokens_user_id_fk` FOREIGN KEY (`user_id`)
  REFERENCES `contact` (`contact_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `session` MODIFY `last_reload` BIGINT UNSIGNED;

-- Add one-click export button column to contact
ALTER TABLE `contact` ADD COLUMN `enable_one_click_export` enum('0','1') DEFAULT '0';
