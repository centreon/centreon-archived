-- Tables for generate forms
CREATE TABLE `form` (
  `form_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `route` VARCHAR(255) NOT NULL,
  `redirect` CHAR(1) NOT NULL DEFAULT '0',
  `redirect_route` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`form_id`),
  UNIQUE KEY `route_UNIQUE` (`route`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `form_block` (
  `block_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  `section_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`block_id`,`section_id`),
  KEY `fk_block_section1_idx` (`section_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `form_block_field_relation` (
  `block_id` INT NOT UNSIGNED NULL AUTO_INCREMENT,
  `field_id` INT NOT UNSIGNED NULL,
  `rank` INT NOT UNSIGNED NULL,
  `mandatory` char(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`block_id`,`field_id`),
  KEY `fk_block_has_field_field1_idx` (`field_id`),
  KEY `fk_block_has_field_block1_idx` (`block_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `form_field` (
  `field_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `label` VARCHAR(45) NOT NULL,
  `default_value` VARCHAR(45) NOT NULL,
  `attributes` VARCHAR(255) DEFAULT NULL,
  `advanced` char(1) NOT NULL DEFAULT '0',
  `type` VARCHAR(45) NOT NULL,
  `help` VARCHAR(45) DEFAULT NULL,
  `validator_id` INT UNSIGNED NOT NULL,
  `module_id` INT UNSIGNED NOT NULL,
  `parent_field` VARCHAR(45) DEFAULT NULL,
  `child_actions` VARCHAR(45) DEFAULT NULL,
  PRIMARY KEY (`field_id`,`validator_id`,`module_id`),
  KEY `fk_field_validator1_idx` (`validator_id`),
  KEY `fk_field_module1_idx` (`module_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `form_massive_change` (
  `massive_change_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `route` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`massive_change_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_massive_change_field_relation` (
  `massive_change_id` INT UNSIGNED NOT NULL,
  `field_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`massive_change_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_section` (
  `section_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  `form_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`section_id`,`form_id`),
  KEY `fk_section_form1_idx` (`form_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `form_step` (
  `step_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `wizard_id` INT NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`step_id`,`wizard_id`),
  KEY `fk_step_wizard_idx` (`wizard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `form_step_field_relation` (
  `step_id` INT UNSIGNED NOT NULL,
  `field_id` INT UNSIGNED NOT NULL,
  `rank` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`step_id`,`field_id`),
  KEY `fk_step_has_field_field1_idx` (`field_id`),
  KEY `fk_step_has_field_step1_idx` (`step_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CRE;ATE TABLE `form_wizard` (
  `wizard_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `route` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`wizard_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
