-- Tables for generate forms
CREATE TABLE `form` (
  `form_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `route` varchar(255) NOT NULL,
  `redirect` char(1) NOT NULL DEFAULT '0',
  `redirect_route` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`form_id`),
  UNIQUE KEY `route_UNIQUE` (`route`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_section` (
  `section_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  `form_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`section_id`,`form_id`),
  KEY `fk_section_form1_idx` (`form_id`),
  CONSTRAINT `fk_form_section_1` FOREIGN KEY (`form_id`) REFERENCES `form` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_block` (
  `block_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  `section_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`block_id`,`section_id`),
  KEY `fk_form_block_1_idx` (`section_id`),
  CONSTRAINT `fk_form_block_1` FOREIGN KEY (`section_id`) REFERENCES `form_section` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_field` (
  `field_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `label` varchar(45) NOT NULL,
  `default_value` varchar(45) NOT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `advanced` char(1) NOT NULL DEFAULT '0',
  `type` varchar(45) NOT NULL,
  `help` text,
  `help_url` varchar(255) DEFAULT NULL,
  `module_id` INT UNSIGNED NOT NULL,
  `parent_field` varchar(45) DEFAULT NULL,
  `child_actions` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`field_id`),
  KEY `fk_field_module1_idx` (`module_id`),
  CONSTRAINT `fk_form_field_1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_wizard` (
  `wizard_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `route` varchar(45) NOT NULL,
  PRIMARY KEY (`wizard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_step` (
  `step_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `wizard_id` INT UNSIGNED NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`step_id`,`wizard_id`),
  KEY `fk_step_wizard_idx` (`wizard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_validator` (
  `validator_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `action` varchar(255) NOT NULL,
  PRIMARY KEY (`validator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_massive_change` (
  `massive_change_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `route` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`massive_change_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_massive_change_field_relation` (
  `massive_change_id` INT UNSIGNED NOT NULL,
  `field_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`massive_change_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_block_field_relation` (
  `block_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_id` INT UNSIGNED NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  `mandatory` char(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`block_id`,`field_id`),
  KEY `fk_form_block_field_relation_1_idx` (`block_id`),
  KEY `fk_form_block_field_relation_2_idx` (`field_id`),
  CONSTRAINT `fk_form_block_field_relation_1` FOREIGN KEY (`block_id`) REFERENCES `form_block` (`block_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_form_block_field_relation_2` FOREIGN KEY (`field_id`) REFERENCES `form_field` (`field_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_step_field_relation` (
  `step_id` INT UNSIGNED NOT NULL,
  `field_id` INT UNSIGNED NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  `mandatory` char(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`step_id`,`field_id`),
  KEY `fk_step_has_field_field1_idx` (`field_id`),
  KEY `fk_step_has_field_step1_idx` (`step_id`),
  CONSTRAINT `fk_form_step_field_relation_1` FOREIGN KEY (`step_id`) REFERENCES `form_step` (`step_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_form_step_field_relation_2` FOREIGN KEY (`field_id`) REFERENCES `form_field` (`field_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_field_validator_relation` (
  `field_id` int(10) unsigned NOT NULL,
  `validator_id` int(10) unsigned NOT NULL,
  `client_side_event` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`field_id`,`validator_id`),
  KEY `fk_new_table_1_idx` (`field_id`),
  KEY `fk_form_field_validator_relation_2_idx` (`validator_id`),
  CONSTRAINT `fk_form_field_validator_relation_1` FOREIGN KEY (`field_id`) REFERENCES `form_field` (`field_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_form_field_validator_relation_2` FOREIGN KEY (`validator_id`) REFERENCES `form_validator` (`validator_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
