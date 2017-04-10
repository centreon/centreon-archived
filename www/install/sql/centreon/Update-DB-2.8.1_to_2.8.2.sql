-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.2' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.1' LIMIT 1;

-- Pull request #4797
ALTER TABLE `custom_view_user_relation` 
  ADD COLUMN `deleted` TINYINT(6) NULL DEFAULT '0' AFTER `locked`;
UPDATE `custom_view_user_relation` SET `deleted`='1' WHERE `is_owner`='1';
