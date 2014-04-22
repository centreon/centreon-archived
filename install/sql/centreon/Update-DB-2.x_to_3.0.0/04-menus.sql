-- Menu with for routing
-- @TODO See if icon is a relation to a image table
CREATE TABLE `menus` (
  `menu_id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(255) NOT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `bgcolor` varchar(55) DEFAULT NULL,
  `module_id` int(10) unsigned NOT NULL,
  `menu_order` tinyint(5) DEFAULT '0',
  PRIMARY KEY (`menu_id`),
  KEY `parent_id` (`parent_id`),
  KEY `fk_menus_1_idx` (`module_id`),
  CONSTRAINT `fk_menus_1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_menu_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`menu_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
