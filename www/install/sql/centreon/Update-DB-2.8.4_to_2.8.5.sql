-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.5' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.4' LIMIT 1;

ALTER TABLE nagios_server ADD COLUMN centreonbroker_logs_path VARCHAR(255);
ALTER TABLE cfg_centreonbroker ADD COLUMN daemon TINYINT(1);

-- Use service
UPDATE `options` SET `value` = 'cbd' WHERE `key` = 'broker_correlator_script' AND `value` = '/etc/init.d/cbd';
UPDATE `nagios_server` SET `init_script` = 'centengine' WHERE `init_script` = '/etc/init.d/centengine';
UPDATE `nagios_server` SET `init_script_centreontrapd` = 'centreontrapd' WHERE `init_script_centreontrapd` = '/etc/init.d/centreontrapd';

-- Missing 'integer' type, mostly used for auto-refresh preference.
INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES ('integer', 0);

-- Remove useless proxy option
DELETE FROM options WHERE options.key = 'proxy_protocol';
