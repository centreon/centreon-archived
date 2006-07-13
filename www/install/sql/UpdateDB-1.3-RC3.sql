-- 13 07 06

ALTER TABLE `meta_service` ADD `graph_id` INT NULL AFTER `critical` ;
ALTER TABLE `meta_service` ADD INDEX `graph_index` ( `graph_id` );
ALTER TABLE `meta_service`  ADD FOREIGN KEY ( `graph_id` ) REFERENCES `giv_graphs_template` ( `graph_id` ) ON DELETE SET NULL ;