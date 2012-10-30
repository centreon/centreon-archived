-- Add informations for delete index_data or metrics
ALTER TABLE `index_data` ADD `to_delete` INT(1) DEFAULT 0;
ALTER TABLE `metrics` ADD `to_delete` INT(1) DEFAULT 0;