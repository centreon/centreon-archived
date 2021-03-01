-- Create authentication tables and insert local configuration

CREATE TABLE `provider_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_name` varchar(255) NOT NULL,
  `provider_configuration_name` varchar(255) NOT NULL,
  `configuration` text NOT NULL,
  `isActive` BOOLEAN NOT NULL DEFAULT 1,
  `isForced` BOOLEAN NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `provider_configuration` (provider_name, provider_configuration_name, configuration, isActive, isForced)
VALUES ('local', 'local', '{}', true, false);

CREATE TABLE `security_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `creation_date` datetime NOT NULL,
  `expiration_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `security_authentication_tokens` (
  `session_token_id` int(11) NOT NULL,
  `token_id` int(11) NOT NULL,
  `token_refresh_id` int(11) DEFAULT NULL,
  `provider_configuration_id` int(11) NOT NULL,
  PRIMARY KEY (`session_token_id`),
  KEY `security_authentication_session_tokens_id_fk` (`session_token_id`),
  KEY `security_authentication_tokens_id_fk` (`token_id`),
  KEY `security_authentication_tokens_refresh_id_fk` (`token_refresh_id`),
  KEY `security_authentication_tokens_configuration_id_fk` (`provider_configuration_id`),
  CONSTRAINT `security_authentication_session_tokens_id_fk` FOREIGN KEY (`session_token_id`)
  REFERENCES `session` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_configuration_id_fk` FOREIGN KEY (`provider_configuration_id`)
  REFERENCES `provider_configuration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_id_fk` FOREIGN KEY (`token_id`)
  REFERENCES `security_token` (`id`) ON DELETE CASCADE,
  CONSTRAINT `security_authentication_tokens_refresh_id__fk` FOREIGN KEY (`token_refresh_id`)
  REFERENCES `security_token` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
