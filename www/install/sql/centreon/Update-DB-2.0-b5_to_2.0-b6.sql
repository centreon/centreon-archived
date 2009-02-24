
--
-- Update Script for BETA 5 to BETA 6
--

UPDATE `topology` SET `topology_show` = '1' WHERE `topology_page` = '50105' LIMIT 1 ;
UPDATE `topology` SET `topology_show` = '0' WHERE `topology_page` = 60805 LIMIT 1 ;


-- 
-- Update Centreon version
-- 

UPDATE `informations` SET `value` = '2.0-b6' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.0-b5' LIMIT 1;
