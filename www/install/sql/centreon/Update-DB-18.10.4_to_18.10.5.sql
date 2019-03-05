-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.5' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.4' LIMIT 1;

-- Remove non existing entries
DELETE FROM topology_JS WHERE id_page IN ('201', '2020301', '2020302', '5010103', '5010105');
