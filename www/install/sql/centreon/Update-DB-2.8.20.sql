UPDATE `cb_fieldgroup` SET `groupname` = 'lua_parameter',`displayname` = 'lua parameter' WHERE `groupname` = 'lua_parameters';
UPDATE `cb_type_field_relation` SET `jshook_arguments` = '{"target": "lua_parameter__value_%d"}' WHERE `jshook_arguments` = '{"target": "lua_parameters__value_%d"}'
