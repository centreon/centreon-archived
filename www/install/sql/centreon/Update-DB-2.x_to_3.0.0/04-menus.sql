-- Menu with for routing
-- @TODO See if icon is a relation to a image table
CREATE TABLE `menus` (
    `menu_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `parent_id` INT UNSIGNED,
    `url` VARCHAR(255) DEFAULT NULL,
    `icon_class` VARCHAR(100),
    `icon` VARCHAR(255),
    `bgcolor` VARCHAR(55),
    `is_module` BOOLEAN DEFAULT 0,
    `menu_order` TINYINT(5) DEFAULT 0,
    PRIMARY KEY(`menu_id`),
    KEY `parent_id` (`parent_id`),
    CONSTRAINT `fk_menu_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`menu_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
