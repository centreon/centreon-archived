-- Ticket #5197
CREATE TABLE `form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `route` varchar(255) NOT NULL,
  `redirect` char(1) NOT NULL DEFAULT '0',
  `redirect_route` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `route_UNIQUE` (`route`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `rank` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  PRIMARY KEY (`id`,`form_id`),
  KEY `fk_section_form1_idx` (`form_id`),
  CONSTRAINT `fk_section_form1` FOREIGN KEY (`form_id`) REFERENCES `form` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `rank` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  PRIMARY KEY (`id`,`section_id`),
  KEY `fk_block_section1_idx` (`section_id`),
  CONSTRAINT `fk_block_section1` FOREIGN KEY (`section_id`) REFERENCES `section` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `validator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `label` varchar(45) NOT NULL,
  `default_value` varchar(45) NOT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `advanced` char(1) NOT NULL DEFAULT '0',
  `type` varchar(45) NOT NULL,
  `help` varchar(45) DEFAULT NULL,
  `validator_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  PRIMARY KEY (`id`,`validator_id`,`module_id`),
  KEY `fk_field_validator1_idx` (`validator_id`),
  KEY `fk_field_module1_idx` (`module_id`),
  CONSTRAINT `fk_field_module1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_field_validator1` FOREIGN KEY (`validator_id`) REFERENCES `validator` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `block_has_field` (
  `block_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `mandatory` char(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`block_id`,`field_id`),
  KEY `fk_block_has_field_field1_idx` (`field_id`),
  KEY `fk_block_has_field_block1_idx` (`block_id`),
  CONSTRAINT `fk_block_has_field_block1` FOREIGN KEY (`block_id`) REFERENCES `block` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_block_has_field_field1` FOREIGN KEY (`field_id`) REFERENCES `field` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
---

