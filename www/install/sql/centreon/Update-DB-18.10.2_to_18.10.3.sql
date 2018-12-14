-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.3' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.2' LIMIT 1;

-- Remove old progressbar lib
DELETE FROM topology_JS WHERE PathName_js LIKE '%aculous%';