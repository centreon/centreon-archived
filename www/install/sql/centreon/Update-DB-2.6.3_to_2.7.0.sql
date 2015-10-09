-- Change version of Centreon
ALTER TABLE options ENGINE=InnoDB;
ALTER TABLE css_color_menu ENGINE=InnoDB;

alter table custom_views add `public` tinyint(6) null default 0;

ALTER TABLE timeperiod_exclude_relations
ADD FOREIGN KEY (timeperiod_id)
REFERENCES timeperiod(tp_id) ON DELETE CASCADE;

ALTER TABLE timeperiod_exclude_relations
ADD FOREIGN KEY (timeperiod_exclude_id)
REFERENCES timeperiod(tp_id) ON DELETE CASCADE;


ALTER TABLE timeperiod_include_relations
ADD FOREIGN KEY (timeperiod_id)
REFERENCES timeperiod(tp_id) ON DELETE CASCADE;

ALTER TABLE timeperiod_include_relations
ADD FOREIGN KEY (timeperiod_include_id)
REFERENCES timeperiod(tp_id) ON DELETE CASCADE;

ALTER TABLE on_demand_macro_host MODIFY COLUMN host_macro_value VARCHAR(4096);
ALTER TABLE on_demand_macro_service MODIFY COLUMN svc_macro_value VARCHAR(4096);

ALTER TABLE `on_demand_macro_host` ADD COLUMN `description` text DEFAULT NULL AFTER `is_password`;
ALTER TABLE `on_demand_macro_service` ADD COLUMN `description` text DEFAULT NULL AFTER `is_password`;

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

ALTER TABLE `command` ADD `command_locked` BOOLEAN DEFAULT 0;
ALTER TABLE `host` ADD `host_locked` BOOLEAN DEFAULT 0 AFTER `host_comment`;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.3' LIMIT 1;

ALTER TABLE `on_demand_macro_host` ADD COLUMN `macro_order` int(11) NULL DEFAULT 0;
ALTER TABLE `on_demand_macro_service` ADD COLUMN `macro_order` int(11) NULL DEFAULT 0;


CREATE TABLE `on_demand_macro_command` (
  `command_macro_id` int(11) NOT NULL AUTO_INCREMENT,
  `command_macro_name` varchar(255) NOT NULL,
  `command_macro_desciption` text DEFAULT NULL,
  `command_command_id` int(11) NOT NULL,
  `command_macro_type` enum('1','2') DEFAULT NULL,
  PRIMARY KEY (`command_macro_id`),
  KEY `command_command_id` (`command_command_id`),
  CONSTRAINT `on_demand_macro_command_ibfk_1` FOREIGN KEY (`command_command_id`) REFERENCES `command` (`command_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Move downtime page
DELETE FROM topology WHERE topology_page IN ('20218', '20106', '60305');

-- #3787
DELETE FROM topology WHERE topology_page IN ('60902', '60903', '60707', '60804');
DELETE FROM topology WHERE topology_page IS NULL AND topology_name LIKE 'Plugins' AND topology_url IS NULL;
DELETE FROM topology WHERE topology_page IS NULL AND topology_name LIKE 'NDOutils' AND topology_url IS NULL;
