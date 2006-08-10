-- 08/08/2006

ALTER TABLE `giv_graphs_template` CHANGE `lower_limit` `lower_limit` FLOAT NULL DEFAULT NULL ,
CHANGE `upper_limit` `upper_limit` FLOAT NULL DEFAULT NULL;

-- 10/08/2006

ALTER TABLE `general_opt` ADD `graph_preferencies` INT '0' AFTER `perfparse_installed` ;

UPDATE `topology` SET `topology_url` = './include/views/graphs/graphSummary/graphSummary.php' WHERE `topology_page` = 402 LIMIT 1 ;
UPDATE `topology` SET `topology_url` = './include/views/graphs/graphSummary/graphSummary.php' WHERE `topology_page` = 4 LIMIT 1 ;
