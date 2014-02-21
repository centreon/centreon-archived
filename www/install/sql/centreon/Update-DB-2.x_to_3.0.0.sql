ALTER TABLE `options` ADD COLUMN `group` VARCHAR(255) NOT NULL DEFAULT 'default' FIRST;

-- Ticket #5106
CREATE TABLE `hooks` (
    `hook_id` int(11) NOT NULL AUTO_INCREMENT,
    `hook_name` varchar(255),
    `hook_description` varchar(255),
    `hook_type` tinyint(1) DEFAULT 0,
    PRIMARY KEY(`hook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `module_hooks` (
    `module_id` int(11) NOT NULL,
    `hook_id` int(11) NOT NULL,
    `module_hook_name` varchar(255) NOT NULL,
    `module_hook_description` varchar(255) NOT NULL,
    KEY `module_id` (`module_id`),
    KEY `hook_id` (`hook_id`),
    CONSTRAINT `fk_module_id` FOREIGN KEY (`module_id`) REFERENCES `modules_informations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_hook_id` FOREIGN KEY (`hook_id`) REFERENCES `hooks` (`hook_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--

-- Ticket #5137
CREATE TABLE `menus` (
    `menu_id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `parent_id` int(11),
    `url` varchar(255) DEFAULT NULL,
    `icon_class`varchar(100),
    `icon` varchar(255),
    `bgcolor` varchar(55),
    `is_module` tinyint(1) DEFAULT 0,
    `menu_order` tinyint(5) DEFAULT 0,
    PRIMARY KEY(`menu_id`),
    KEY `parent_id` (`parent_id`),
    CONSTRAINT `fk_menu_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`menu_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--

-- Ticket #5193
CREATE TABLE `acl_routes` (
    `acl_route_id` int(11) NOT NULL AUTO_INCREMENT,
    `route` varchar(255) NOT NULL, 
    `permission` int(11) NOT NULL,
    `acl_group_id` int(11),
    PRIMARY KEY (`acl_route_id`),
    KEY `acl_group_id` (`acl_group_id`),
    CONSTRAINT `fk_acl_route_group_id` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups`(`acl_group_id`) ON DELETE CASCADE

CREATE TABLE `acl_group_menu_relations` (
  `acl_group_id` int(11) NOT NULL,
  `acl_menu_id` int(11) NOT NULL,
  KEY `acl_group_id` (`acl_group_id`),
  KEY `acl_menu_id` (`acl_menu_id`),
  CONSTRAINT `acl_group_menu_relations_ibfk_1` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups` (`acl_group_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_group_menu_relations_ibfk_2` FOREIGN KEY (`acl_menu_id`) REFERENCES `acl_menus` (`acl_menu_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `acl_menus` (
  `acl_menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`acl_menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `acl_menu_menu_relations` (
  `acl_menu_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `acl_level` tinyint(3) DEFAULT NULL,
  KEY `acl_menu_id` (`acl_menu_id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `acl_menu_menu_relations_ibfk_1` FOREIGN KEY (`acl_menu_id`) REFERENCES `acl_menus` (`acl_menu_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_menu_menu_relations_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`menu_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--


-- New session table
DROP TABLE `session`;
CREATE TABLE `session` (
    `session_id` VARCHAR(255) NOT NULL,
    `user_id` INT NOT NULL,
    `session_start_time` INT NOT NULL,
    `last_reload` INT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `route` VARCHAR(255) NOT NULL,
    `update_acl` ENUM('0', '1') DEFAULT '0',
    PRIMARY KEY(`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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

