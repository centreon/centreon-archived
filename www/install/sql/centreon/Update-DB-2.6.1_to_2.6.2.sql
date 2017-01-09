-- Add default socket path for Centreon Broker
INSERT INTO `options` (`key`, `value`) VALUES ('broker_socket_path', '@CENTREONBROKER_VARLIB@/command');

-- Add new configuration information for synchronize database with Centreon Broker
INSERT INTO `cb_module` (`name`, `libname`, `loading_pos`, `is_bundle`, `is_activated`) VALUES
('Dumper', 'dumper.so', 20, 0, 1);

INSERT INTO `cb_type` (`type_name`, `type_shortname`, `cb_module_id`) VALUES
('Database configuration reader', 'db_cfg_reader', (SELECT cb_module_id FROM cb_module WHERE name LIKE 'Dumper')),
('Database configuration writer', 'db_cfg_writer', (SELECT cb_module_id FROM cb_module WHERE name LIKE 'Dumper'));

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration reader'), 15, 1, 1),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration reader'), 7, 1, 2),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration reader'), 18, 0, 3),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration reader'), 8, 0, 4),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration reader'), 9, 0, 5),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration reader'), 10, 1, 6),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration writer'), 15, 1, 1),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration writer'), 7, 1, 2),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration writer'), 18, 0, 3),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration writer'), 8, 0, 4),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration writer'), 9, 0, 5),
((SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration writer'), 10, 1, 6);

INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`, `cb_type_uniq`) VALUES
(1, (SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration reader'), 1),
(1, (SELECT cb_type_id FROM cb_type WHERE type_name LIKE 'Database configuration writer'), 1);

ALTER TABLE cfg_centreonbroker ADD COLUMN command_file VARCHAR(255);

INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`) VALUES
(6, 'Dumper', 'dumper');

INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES ('Discovery','./img/icones/16x16/gear_view.gif',608,60807,21,1,'./include/configuration/configObject/command/command.php','&type=4','0','0','1',NULL,NULL,NULL,'0');

ALTER TABLE `cron_operation` ALTER `last_execution_time` SET DEFAULT '0';

ALTER TABLE `giv_graphs_template` ALTER `size_to_max` SET DEFAULT '0';

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.6.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.1' LIMIT 1;

-- Table for WebService token
CREATE TABLE ws_token (
  contact_id INT NOT NULL,
  token VARCHAR(100) NOT NULL,
  generate_date DATETIME NOT NULL,
  PRIMARY KEY(contact_id),
  UNIQUE (token),
  FOREIGN KEY (contact_id) REFERENCES contact (contact_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;