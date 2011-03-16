ALTER TABLE `metrics` ADD `data_source_type` ENUM( '0', '1', '2', '3' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' AFTER `metric_name` ;
