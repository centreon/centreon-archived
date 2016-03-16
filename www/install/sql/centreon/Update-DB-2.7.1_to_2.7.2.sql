-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.1' LIMIT 1;

-- Clear BDD with old htmlentities values for <,>,<= and >=
UPDATE service SET command_command_id_arg = REPLACE(command_command_id_arg, '&lt;', '<');
UPDATE service SET command_command_id_arg = REPLACE(command_command_id_arg, '&gt;', '>');
UPDATE service SET command_command_id_arg = REPLACE(command_command_id_arg, '&le;', '<=');
UPDATE service SET command_command_id_arg = REPLACE(command_command_id_arg, '&ge;', '>=');
UPDATE command SET command_line = REPLACE(command_line, '&lt;', '<');
UPDATE command SET command_line = REPLACE(command_line, '&gt;', '>');
UPDATE command SET command_line = REPLACE(command_line, '&le;', '<=');
UPDATE command SET command_line = REPLACE(command_line, '&ge;', '>=');
UPDATE on_demand_macro_host SET host_macro_value = REPLACE(host_macro_value, '&lt;', '<');
UPDATE on_demand_macro_host SET host_macro_value = REPLACE(host_macro_value, '&gt;', '>');
UPDATE on_demand_macro_host SET host_macro_value = REPLACE(host_macro_value, '&le;', '<=');
UPDATE on_demand_macro_host SET host_macro_value = REPLACE(host_macro_value, '&ge;', '>=');
UPDATE on_demand_macro_service SET svc_macro_value = REPLACE(svc_macro_value, '&lt;', '<');
UPDATE on_demand_macro_service SET svc_macro_value = REPLACE(svc_macro_value, '&gt;', '>');
UPDATE on_demand_macro_service SET svc_macro_value = REPLACE(svc_macro_value, '&le;', '<=');
UPDATE on_demand_macro_service SET svc_macro_value = REPLACE(svc_macro_value, '&ge;', '>=');

-- Clear BDD with old htmlentities values for &
UPDATE service SET command_command_id_arg = REPLACE(command_command_id_arg, '&amp;', '&');
UPDATE command SET command_line = REPLACE(command_line, '&amp;', '&');
UPDATE on_demand_macro_host SET host_macro_value = REPLACE(host_macro_value, '&amp;', '&');
UPDATE on_demand_macro_service SET svc_macro_value = REPLACE(svc_macro_value, '&amp;', '&');
UPDATE extended_host_information SET ehi_notes_url = REPLACE(ehi_notes_url, '&amp;', '&');
UPDATE extended_host_information SET ehi_action_url = REPLACE(ehi_action_url, '&amp;', '&');
UPDATE extended_service_information SET esi_notes_url = REPLACE(esi_notes_url, '&amp;', '&');
UPDATE extended_service_information SET esi_action_url = REPLACE(esi_action_url, '&amp;', '&');

ALTER TABLE escalation ADD COLUMN host_inheritance_to_services tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE escalation ADD COLUMN hostgroup_inheritance_to_services tinyint(1) DEFAULT 0 NOT NULL;

UPDATE options SET options.value = '1' WHERE options.key = 'index_data';
UPDATE cfg_centreonbroker_info SET config_value = '1' WHERE config_key = 'insert_in_index_data';

UPDATE nagios_server SET monitoring_engine = 'CENGINE' WHERE monitoring_engine = 'Centreon Engine';

UPDATE `topology` SET `topology_name` = 'Edit View' WHERE `topology_id` = 196;
UPDATE `topology` SET `topology_name` = 'Share View' WHERE `topology_id` = 197;
UPDATE `topology` SET `topology_name` = 'Widget Parameters' WHERE `topology_id` = 198;

INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES ('Delete View',NULL,103,10306,NULL,NULL,'./include/home/customViews/actionDelete.php',NULL,NULL,NULL,'0',NULL,NULL,NULL,'1');
INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES ('Add View',NULL,103,10307,NULL,NULL,'./include/home/customViews/actionAddView.php',NULL,NULL,NULL,'0',NULL,NULL,NULL,'1');
INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES ('Set Default',NULL,103,10308,NULL,NULL,'./include/home/customViews/actionSetDefault.php',NULL,NULL,NULL,'0',NULL,NULL,NULL,'1');

-- Apply patch
ALTER TABLE `traps`
CHANGE COLUMN `traps_advanced_treatment_default` `traps_advanced_treatment_default` ENUM('0','1','2') NULL DEFAULT '0';
