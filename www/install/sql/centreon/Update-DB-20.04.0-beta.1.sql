-- Remove broker correlation mechanism
ALTER TABLE `cfg_centreonbroker` DROP COLUMN `correlation_activate`;
DELETE FROM `cb_field` WHERE
  `displayname` = 'Correlation file'
  OR `description` LIKE 'File where correlation%'
  OR `displayname` = 'Correlation passive';
DELETE FROM `cb_type` WHERE `type_shortname` = 'correlation';
