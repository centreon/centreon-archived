-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.20' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.19' LIMIT 1;

UPDATE `cb_fieldgroup` SET `groupname` = 'lua_parameter',`displayname` = 'lua parameter' WHERE `groupname` = 'lua_parameters';
UPDATE `cb_type_field_relation` SET `jshook_arguments` = '{"target": "lua_parameter__value_%d"}' WHERE `jshook_arguments` = '{"target": "lua_parameters__value_%d"}'
