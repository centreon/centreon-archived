-- Change version of Centreon
UPDATE `informations` SET `value` = '2.9.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.10' LIMIT 1;

ALTER TABLE `extended_host_information` DROP FOREIGN KEY `extended_host_information_ibfk_3`;
ALTER TABLE `extended_host_information` DROP COLUMN `ehi_vrml_image`;

DELETE FROM topology_JS WHERE PathName_js LIKE '%aculous%';

UPDATE `cb_field`
SET `fieldname` = 'negotiation', `displayname` = 'Enable negotiation',
`description` = 'Enable negotiation option (use only for version of Centren Broker >= 2.5)'
WHERE `fieldname` = 'negociation';

UPDATE `cfg_centreonbroker_info`
SET `config_key` = 'negotiation'
WHERE `config_key` = 'negociation';