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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
    `update_acl` ENUM('0', '1') DEFAULT 0,
    PRIMARY KEY(`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
