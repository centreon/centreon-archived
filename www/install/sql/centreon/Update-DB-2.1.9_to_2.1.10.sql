ALTER TABLE acl_groups ADD acl_group_changed INT AFTER acl_group_alias;

UPDATE `informations` SET `value` = '2.1.10' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1.9' LIMIT 1;