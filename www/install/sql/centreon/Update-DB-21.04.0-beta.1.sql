-- Create authentication tables and insert local configuration

CREATE TABLE `provider_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `isActive` BOOLEAN NOT NULL DEFAULT 1,
  `isForced` BOOLEAN NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `provider_configuration` (type, name, isActive, isForced)
VALUES ('local', 'local', true, false);

CREATE TABLE `security_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `creation_date` int(11) NOT NULL,
  `expiration_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `security_authentication_tokens` (
  `token` varchar(255) NOT NULL,
  `provider_token_id` int(11) DEFAULT NULL,
  `provider_token_refresh_id` int(11) DEFAULT NULL,
  `provider_configuration_id` int(11) NOT NULL,
  PRIMARY KEY (`token`),
  KEY `security_authentication_tokens_token_fk` (`token`),
  KEY `security_authentication_tokens_provider_token_id_fk` (`provider_token_id`),
  KEY `security_authentication_tokens_provider_token_refresh_id_fk` (`provider_token_refresh_id`),
  KEY `security_authentication_tokens_configuration_id_fk` (`provider_configuration_id`),
  CONSTRAINT `security_authentication_tokens_configuration_id_fk` FOREIGN KEY (`provider_configuration_id`)
  REFERENCES `provider_configuration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_provider_token_id_fk` FOREIGN KEY (`provider_token_id`)
  REFERENCES `security_token` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_provider_token_refresh_id_fk` FOREIGN KEY (`provider_token_refresh_id`)
  REFERENCES `security_token` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
