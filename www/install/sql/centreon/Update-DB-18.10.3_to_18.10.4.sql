-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.4' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.3' LIMIT 1;

-- Add new Extensions Page entry
INSERT INTO `topology` (`topology_name`, `topology_url`, `readonly`, `is_react`, `topology_parent`, `topology_page`, `topology_group`) VALUES ('Manager', ' ./include/react-compiled/Manager/index.html', '1', '1', '507', '50709', 1);

-- Remove old Extensions Page menus
DELETE FROM `topology` WHERE (`topology_page` = '50701');
DELETE FROM `topology` WHERE (`topology_page` = '50703');