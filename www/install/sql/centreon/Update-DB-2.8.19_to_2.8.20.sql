-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.20' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.19' LIMIT 1;

UPDATE `cb_fieldgroup` SET `groupname` = 'lua_parameter',`displayname` = 'lua parameter' WHERE `groupname` = 'lua_parameters';
