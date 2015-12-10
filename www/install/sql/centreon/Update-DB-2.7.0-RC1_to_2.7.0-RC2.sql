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

-- Add changetab for hostDetails page in Monitoring
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES (NULL,20202,NULL,'./include/common/javascript/changetab.js','initChangeTab');

-- Add discovery command menu
INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES ('Discovery','./img/icones/16x16/gear_view.gif',608,60807,21,1,'./include/configuration/configObject/command/command.php','&type=4','0','0','1',NULL,NULL,NULL,'0');

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0-RC2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.0-RC1' LIMIT 1;
