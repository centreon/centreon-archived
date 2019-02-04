-- updating the side menus
-- removing or renaming unfriendly titles from performance menu
DELETE FROM topology WHERE topology_name = "Graphs" AND topology_parent = 204 AND topology_page IS NULL;
DELETE FROM topology WHERE topology_name = "Templates" AND topology_parent = 204 AND topology_page IS NULL;
UPDATE topology SET topology_name = "Parameters" WHERE topology_name = "Virtuals" AND topology_parent = 204;
UPDATE topology SET topology_name = "Virtual Metrics" WHERE topology_page = 20408;

-- grouping the menus under Parameters
UPDATE topology SET topology_group = 46 WHERE topology_page IN (20404, 20405, 20408);

-- removing unfriendly titles from Configuration menu
DELETE FROM topology WHERE topology_name = "Services" AND topology_parent = 602 AND topology_page IS NULL;
DELETE FROM topology WHERE topology_name = "Meta Services" AND topology_parent = 602 AND topology_page IS NULL;

-- removing unfriendly titles from Configuration menu
DELETE FROM topology WHERE topology_name = "Commands" AND topology_parent = 608 AND topology_page IS NULL;
DELETE FROM topology WHERE topology_name = "Connectors" AND topology_parent = 608 AND topology_page IS NULL;

-- removing the CSS page from Administration menu
DELETE FROM topology WHERE topology_name = "CSS" AND topology_parent = 501 AND topology_page = 50116;

-- removing unfriendly title from Logs menu
DELETE FROM topology WHERE topology_name = "Visualisation" AND topology_parent = 508 AND topology_page = 50801;

-- Remove old Extensions Page menus
DELETE FROM `topology` WHERE (`topology_page` = '50701');
DELETE FROM `topology` WHERE (`topology_page` = '50703');

-- Add new configuration for Centeron Broker : Use multiple thread to connect to database
INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES
(75, 'connections_count', 'Connections count', 'Number of opened connection to the database.', 'int', NULL);

-- Add new Extensions Page entry
INSERT INTO `topology` (`topology_name`, `topology_url`, `readonly`, `is_react`, `topology_parent`, `topology_page`, `topology_group`) VALUES ('Manager', '/administration/extensions/manager', '1', '1', 507, 50709, 1);

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(14, 75, 1, 18),
(16, 75, 1, 20);

-- New configuration options for Centreon Engine
ALTER TABLE `cfg_nagios` ADD COLUMN `enable_macros_filter` ENUM('0', '1') DEFAULT '0';
ALTER TABLE `cfg_nagios` ADD COLUMN `event_broker_to_log` INT DEFAULT -1;
ALTER TABLE `cfg_nagios` ADD COLUMN `macros_filter` TEXT DEFAULT '';
