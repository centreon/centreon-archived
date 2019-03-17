-- Add new Extensions Page entry
INSERT INTO `topology` (`topology_name`, `topology_url`, `readonly`, `is_react`, `topology_parent`, `topology_page`, `topology_group`) VALUES ('Manager', '/administration/extensions/manager', '0', '1', 507, 50709, 1);

-- Remove old Extensions Page menus
DELETE FROM `topology` WHERE (`topology_page` = '50701');
DELETE FROM `topology` WHERE (`topology_page` = '50703');
