-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.0-beta1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.6' LIMIT 1;

-- Remove failover field from graphite broker output
DELETE cbfr FROM cb_type_field_relation cbfr
INNER JOIN cb_field cbf ON cbfr.cb_field_id=cbf.cb_field_id
INNER JOIN cb_type cbt ON cbfr.cb_type_id=cbt.cb_type_id
AND cbf.fieldname = 'failover'shows
AND cbt.type_shortname = 'graphite';

-- Insert KB configuration
INSERT INTO `options` (`key`, `value`) VALUES ('kb_db_name', ''), ('kb_db_user', ''), ('kb_db_password', ''), ('kb_db_host', ''), ('kb_db_prefix', ''), ('kb_WikiURL', '');
INSERT INTO `topology` (`topology_id` ,`topology_name` ,`topology_parent` ,`topology_page` ,`topology_order` ,`topology_group` ,`topology_url` ,`topology_url_opt` ,`topology_popup` ,`topology_modules` ,`topology_show` ,`topology_style_class` ,`topology_style_id` ,`topology_OnClick`) VALUES (NULL , 'Knowledge Base', '501', '50133', 90, 1, './include/Administration/parameters/parameters.php', '&o=knowledgeBase' , NULL , '1', '1', NULL , NULL , NULL);