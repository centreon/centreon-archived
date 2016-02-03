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


ALTER TABLE escalation ADD COLUMN host_inheritance_to_services tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE escalation ADD COLUMN hostgroup_inheritance_to_services tinyint(1) DEFAULT 0 NOT NULL;

UPDATE options SET options.value = '1' WHERE options.key = 'index_data';
UPDATE cfg_centreonbroker_info SET config_value = '1' WHERE config_key = 'insert_in_index_data';

UPDATE nagios_server SET monitoring_engine = 'CENGINE' WHERE monitoring_engine = 'Centreon Engine';
