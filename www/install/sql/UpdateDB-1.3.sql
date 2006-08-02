-- 26 07 2006
ALTER TABLE `general_opt` ADD `snmp_trapd_used` ENUM( '0', '1' ) NULL AFTER `snmp_version` ;
UPDATE `topology` SET `topology_icone` = NULL , `topology_url` = './include/monitoring/log/viewAlertLog.php', `topology_url_opt` = NULL WHERE `topology_page` = '203' LIMIT 1 ;

-- 02 08 2006
TRUNCATE TABLE `dependency_serviceParent_relation` ;
TRUNCATE TABLE `dependency_serviceChild_relation` ;
TRUNCATE TABLE `escalation_service_relation` ;

ALTER TABLE `dependency_serviceParent_relation` ADD `host_host_id` INT NULL AFTER `service_service_id` ;
ALTER TABLE `dependency_serviceParent_relation` ADD INDEX `host_index` ( `host_host_id` ) ;
ALTER TABLE `dependency_serviceParent_relation` ADD FOREIGN KEY ( `host_host_id` ) REFERENCES `host` ( `host_id` ) ON DELETE CASCADE ;

ALTER TABLE `dependency_serviceChild_relation` ADD `host_host_id` INT NULL AFTER `service_service_id` ;
ALTER TABLE `dependency_serviceChild_relation` ADD INDEX `host_index` ( `host_host_id` ) ;
ALTER TABLE `dependency_serviceChild_relation` ADD FOREIGN KEY ( `host_host_id` ) REFERENCES `host` ( `host_id` ) ON DELETE CASCADE ;

ALTER TABLE `escalation_service_relation` ADD `host_host_id` INT NULL AFTER `service_service_id` ;
ALTER TABLE `escalation_service_relation` ADD INDEX `host_index` ( `host_host_id` ) ;
ALTER TABLE `escalation_service_relation` ADD FOREIGN KEY ( `host_host_id` ) REFERENCES `host` ( `host_id` ) ON DELETE CASCADE ;
