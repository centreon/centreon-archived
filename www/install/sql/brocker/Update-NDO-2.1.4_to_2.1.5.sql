 ALTER TABLE `centreon_acl` CHANGE `host_name` `host_name` VARCHAR( 65 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
 ALTER TABLE `centreon_acl` CHANGE `service_description` `service_description` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
 ALTER TABLE `centreon_acl` ADD INDEX `name`( `host_name` , `service_description` , `group_id` );
 ALTER TABLE `centreon_acl` ADD INDEX `id` ( `host_id` , `service_id` , `group_id` );
 
