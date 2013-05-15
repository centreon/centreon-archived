

-- Add size to max for CPU Graph template
UPDATE `giv_graphs_template` SET `size_to_max` = '1' WHERE `name` = 'CPU';
UPDATE `giv_graphs_template` SET `upper_limit` = '100' where `name` = 'CPU';

UPDATE `informations` SET `value` = '2.4.3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.2' LIMIT 1;
