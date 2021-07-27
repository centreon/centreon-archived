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

CREATE TABLE IF NOT EXISTS `password_security_policy` (
  `password_length` int(11) UNSIGNED NOT NULL DEFAULT 12,
  `uppercase_characters` enum('0', '1') NOT NULL DEFAULT '1',
  `lowercase_characters` enum('0', '1') NOT NULL DEFAULT '1',
  `integer_characters` enum('0', '1') NOT NULL DEFAULT '1',
  `special_characters` enum('0', '1') NOT NULL DEFAULT '1',
  `attempts` int(11) UNSIGNED NOT NULL DEFAULT 5,
  `blocking_duration` int(11) UNSIGNED NOT NULL DEFAULT 900,
  `password_expiration` int(11) UNSIGNED NOT NULL DEFAULT 7776000,
  `delay_before_new_password` int(11) UNSIGNED NOT NULL DEFAULT 3600
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `password_security_policy`
(`password_length`, `uppercase_characters`, `lowercase_characters`, `integer_characters`, `special_characters`,
`attempts`, `blocking_duration`, `password_expiration`, `delay_before_new_password`)
VALUES (12, '1', '1', '1', '1', 5, 900, 7776000, 3600);

ALTER TABLE `session` MODIFY `last_reload` BIGINT UNSIGNED