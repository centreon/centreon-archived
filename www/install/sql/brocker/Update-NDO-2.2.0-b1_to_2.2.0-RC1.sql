
ALTER TABLE `centreon_acl` ENGINE = InnoDB;
ALTER TABLE `centreon_acl` DROP INDEX `group_id`;
ALTER TABLE `centreon_acl` ADD INDEX `group_id_by_name` ( `host_name` ( 70 ) , `service_description` ( 120 ) , `group_id` );
ALTER TABLE `centreon_acl` ADD INDEX `group_id_by_id` ( `host_id` , `service_id` , `group_id` );
ALTER TABLE `centreon_acl` ADD INDEX `group_id_for_host` ( `host_name` , `group_id` );
