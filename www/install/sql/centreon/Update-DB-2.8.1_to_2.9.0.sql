-- Change version of Centreon
UPDATE `informations` SET `value` = '2.9.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.1' LIMIT 1;

-- Rights "delete" for the share views
ALTER TABLE `custom_view_user_relation` 
  ADD COLUMN `deleteable` TINYINT(6) NULL DEFAULT '0' AFTER `locked`;
UPDATE `custom_view_user_relation` SET `deleteable`='1' WHERE `is_owner`='1';