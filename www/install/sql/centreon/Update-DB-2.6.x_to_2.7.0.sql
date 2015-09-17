CREATE TABLE `traps_group` (
  `traps_group_id` int(11) DEFAULT NULL,
  `traps_id` int(11) DEFAULT NULL,
  KEY `traps_group_id` (`traps_group_id`),
  KEY `traps_id` (`traps_id`),
  CONSTRAINT `traps_group_ibfk_1` FOREIGN KEY (`traps_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Create table for relation between metaservice and contact
CREATE TABLE `meta_contact` (
  `meta_id` INT NOT NULL,
  `contact_id` INT NOT NULL,
  PRIMARY KEY (`meta_id`, `contact_id`),
  FOREIGN KEY (`meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `on_demand_macro_host` ADD COLUMN `description` text DEFAULT NULL AFTER `is_password`;
ALTER TABLE `on_demand_macro_service` ADD COLUMN `description` text DEFAULT NULL AFTER `is_password`;
