-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.0-beta2' LIMIT 1;

INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES
('hostCategoriesMulti', 1),
('hostGroupMulti', 1),
('hostMulti', 1),
('metricMulti', 1),
('serviceCategory', 1),
('hostCategory', 1),
('serviceMulti', 1);

UPDATE `options` SET `value`='/var/cache/centreon/backup' WHERE `key`='backup_backup_directory';
