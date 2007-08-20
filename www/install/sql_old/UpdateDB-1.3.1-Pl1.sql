-- 09 08 2006
-- Thoses lines are useful for users who installed Oreon 1.3 from scratch and NOT from an update to Oreon 1.3

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

ALTER TABLE `meta_service_relation` ADD INDEX `host_index` ( `host_id` ) ;
ALTER TABLE `meta_service_relation` ADD FOREIGN KEY ( `host_id` ) REFERENCES `host` ( `host_id` ) ON DELETE CASCADE ;
ALTER TABLE `meta_service_relation` ADD FOREIGN KEY ( `meta_id` ) REFERENCES `meta_service` ( `meta_id` ) ON DELETE CASCADE ;