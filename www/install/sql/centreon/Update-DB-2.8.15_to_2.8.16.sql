-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.16' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.15' LIMIT 1;

-- Remove non existing Centreon Broker protocol
DELETE FROM cb_module WHERE name = 'NDO' AND libname = 'ndo.so';
DELETE FROM cb_list_values WHERE value_name = 'NDO Protocol' AND value_value = 'ndo';
DELETE FROM cb_module_relation WHERE cb_module_id = 5;
