UPDATE giv_graphs_template SET width = '500' WHERE width = '600' ;
UPDATE giv_graphs_template SET height = '150' WHERE height = '200';

UPDATE `informations` SET `value` = '2.1.9' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1.8' LIMIT 1;