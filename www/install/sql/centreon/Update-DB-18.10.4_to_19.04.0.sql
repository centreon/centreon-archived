TRUNCATE TABLE ods_view_details;

-- Change version of Centreon
UPDATE `informations` SET `value` = '19.04.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.4' LIMIT 1;
