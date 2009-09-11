UPDATE `topology` SET topology_url = 'http://trac.centreon.com/' WHERE topology_url LIKE 'http://bugs.centreon.com%';

UPDATE `acl_resources` SET changed = '1';

ALTER TABLE `service` ADD `service_first_notification_delay` INT NULL AFTER `service_notifications_enabled` ;

UPDATE `informations` SET `value` = '2.1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1-RC8' LIMIT 1;

