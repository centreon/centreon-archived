-- Add default socket path for Centreon Broker
INSERT INTO `options` (`key`, `value`) VALUES ('broker_socket_path', '@CENTREONBROKER_VARLIB@/command');

-- Add new configuration information for synchronize database with Centreon Broker
INSERT INTO `cb_module` (`cb_module_id`, `name`, `libname`, `loading_pos`, `is_bundle`, `is_activated`) VALUES
(17, 'Dumper', 'dumper.so', 20, 0, 1);

INSERT INTO `cb_type` (`cb_type_id`, `type_name`, `type_shortname`, `cb_module_id`) VALUES
(28, 'Database configuration reader', 'db_cfg_reader', 17),
(29, 'Database configuration writer', 'db_cfg_writer', 17);

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(28, 15, 1, 1),
(28, 7, 1, 2),
(28, 18, 0, 3),
(28, 8, 0, 4),
(28, 9, 0, 5),
(28, 10, 1, 6),
(29, 15, 1, 1),
(29, 7, 1, 2),
(29, 18, 0, 3),
(29, 8, 0, 4),
(29, 9, 0, 5),
(29, 10, 1, 6);

INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`, `cb_type_uniq`) VALUES
(1, 28, 1),
(1, 29, 1);

ALTER TABLE cfg_centreonbroker ADD COLUMN command_file VARCHAR(255);

INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`) VALUES
(6, 'Dumper', 'dumper');

INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES ('Discovery','./img/icones/16x16/gear_view.gif',608,60807,21,1,'./include/configuration/configObject/command/command.php','&type=4','0','0','1',NULL,NULL,NULL,'0');

ALTER TABLE `cron_operation` ALTER `last_execution_time` SET DEFAULT '0';

ALTER TABLE `giv_graphs_template` ALTER `size_to_max` SET DEFAULT '0';

-- Change version of Centreon
ALTER TABLE options ENGINE=InnoDB;
ALTER TABLE css_color_menu ENGINE=InnoDB;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.2' LIMIT 1;


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