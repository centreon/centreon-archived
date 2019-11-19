-- Remove broker correlation mechanism
ALTER TABLE `cfg_centreonbroker` DROP COLUMN `correlation_activate`;
DELETE FROM `cb_field` WHERE
  `displayname` = 'Correlation file'
  OR `description` LIKE 'File where correlation%'
  OR `displayname` = 'Correlation passive';
DELETE FROM `cb_type` WHERE `type_shortname` = 'correlation';

-- Update topology of service grid / by host group / by service group
UPDATE topology SET topology_url_opt = '&o=svcOV_pb' WHERE topology_page = 20204;
UPDATE topology SET topology_url_opt = '&o=svcOVHG_pb' WHERE topology_page = 20209;
UPDATE topology SET topology_url_opt = '&o=svcOVSG_pb' WHERE topology_page = 20212;
