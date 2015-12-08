update topology set topology_url_opt ='&o=h_unhandled' where topology_page = 20202;
update topology set topology_url_opt ='&o=svc_unhandled' where topology_page = 20201;

-- Clear BDD with old htmlentities values for " and ' 
UPDATE service SET command_command_id_arg = REPLACE(command_command_id_arg, '&quot;', '"');
UPDATE service SET command_command_id_arg = REPLACE(command_command_id_arg, '&apos;', '\'');
UPDATE command SET command_line = REPLACE(command_line, '&quot;', '"');
UPDATE command SET command_line = REPLACE(command_line, '&apos;', '\'');
UPDATE on_demand_macro_host SET host_macro_value = REPLACE(host_macro_value, '&quot;', '"');
UPDATE on_demand_macro_host SET host_macro_value = REPLACE(host_macro_value, '&apos;', '\'');
UPDATE on_demand_macro_service SET svc_macro_value = REPLACE(svc_macro_value, '&quot;', '"');
UPDATE on_demand_macro_service SET svc_macro_value = REPLACE(svc_macro_value, '&apos;', '\'');


-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0-RC2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.0-RC1' LIMIT 1;
