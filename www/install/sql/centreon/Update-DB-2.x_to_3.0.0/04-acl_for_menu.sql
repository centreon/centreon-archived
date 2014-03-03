-- Create acl for new routing
CREATE TABLE `acl_routes` (
    `acl_route_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `route` VARCHAR(255) NOT NULL, 
    `permission` INT NOT NULL,
    `acl_group_id` INT,
    PRIMARY KEY (`acl_route_id`),
    KEY `acl_group_id` (`acl_group_id`),
    CONSTRAINT `fk_acl_route_group_id` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups`(`acl_group_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `acl_group_menu_relations` (
  `acl_group_id` INT UNSIGNED NOT NULL,
  `acl_menu_id` INT UNSIGNED NOT NULL,
  KEY `acl_group_id` (`acl_group_id`),
  KEY `acl_menu_id` (`acl_menu_id`),
  CONSTRAINT `acl_group_menu_relations_ibfk_1` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups` (`acl_group_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_group_menu_relations_ibfk_2` FOREIGN KEY (`acl_menu_id`) REFERENCES `acl_menus` (`acl_menu_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `acl_menus` (
  `acl_menu_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAr(255) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `enabled` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`acl_menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `acl_menu_menu_relations` (
  `acl_menu_id` INT UNSIGNED DEFAULT NULL,
  `menu_id` INT DEFAULT NULL,
  `acl_level` TINYINT(3) DEFAULT NULL,
  KEY `acl_menu_id` (`acl_menu_id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `acl_menu_menu_relations_ibfk_1` FOREIGN KEY (`acl_menu_id`) REFERENCES `acl_menus` (`acl_menu_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_menu_menu_relations_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`menu_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
