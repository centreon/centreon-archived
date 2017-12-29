-- Add size to max for CPU Graph template

UPDATE `giv_graphs_template` SET `size_to_max` = '1' WHERE `name` = 'CPU';

UPDATE `giv_graphs_template` SET `upper_limit` = '100' where `name` = 'CPU';


-- Delete useless field in montoring engine configuration form

DELETE FROM options WHERE `key` LIKE 'cengine_path_connectors';


-- Set Submit value as default option

ALTER TABLE `traps` CHANGE `traps_submit_result_enable`  `traps_submit_result_enable` ENUM('0', '1') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT  '1';


UPDATE `informations` SET `value` = '2.4.3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.2' LIMIT 1;
