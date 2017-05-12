-- Change version of Centreon
UPDATE `informations` SET `value` = '2.9.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.7' LIMIT 1;

-- Adding a column, for the RPN function put in the template of the curves
ALTER TABLE `giv_components_template` 
ADD COLUMN `ds_cdef` VARCHAR(255) NULL DEFAULT NULL AFTER `comment`;