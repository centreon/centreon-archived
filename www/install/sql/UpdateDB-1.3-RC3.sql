-- 13 07 06

ALTER TABLE `meta_service` ADD `graph_id` INT NULL AFTER `critical` ;
ALTER TABLE `meta_service` ADD INDEX `graph_index` ( `graph_id` );
ALTER TABLE `meta_service`  ADD FOREIGN KEY ( `graph_id` ) REFERENCES `giv_graphs_template` ( `graph_id` ) ON DELETE SET NULL ;

-- 17 07 06
ALTER TABLE `log_archive_host` ADD INDEX `host_index` ( `host_id` ) ;
ALTER TABLE `log_archive_host` ADD INDEX `date_end_index` ( `date_end` ) ;
ALTER TABLE `log_archive_host` ADD INDEX `date_start_index` ( `date_start` );
ALTER TABLE `log_archive_host`  ADD FOREIGN KEY ( `host_id` ) REFERENCES `host` ( `host_id` ) ON DELETE CASCADE ;

ALTER TABLE `log_archive_service` ADD INDEX `host_index` ( `host_id` ) ;
ALTER TABLE `log_archive_service` ADD INDEX `service_index` ( `service_id` ) ;
ALTER TABLE `log_archive_service` ADD INDEX `date_end_index` ( `date_end` ) ;
ALTER TABLE `log_archive_service` ADD INDEX `date_start_index` ( `date_start` );
ALTER TABLE `log_archive_service`  ADD FOREIGN KEY ( `host_id` ) REFERENCES `host` ( `host_id` ) ON DELETE CASCADE ;
ALTER TABLE `log_archive_service`  ADD FOREIGN KEY ( `service_id` ) REFERENCES `service` ( `service_id` ) ON DELETE CASCADE ;

-- escalation view
INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` )
VALUES (
NULL , 'mod_gantt', './img/icones/16x16/bookmarks.gif', '604', '60406', '60', '1', './include/configuration/configObject/escalation/ViewEscalation.php', NULL , '0', '0', '1'
);
