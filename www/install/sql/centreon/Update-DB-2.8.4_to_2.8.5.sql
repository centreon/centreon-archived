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

-- custom views share options
ALTER TABLE custom_view_user_relation ADD is_share tinyint(1) NOT NULL DEFAULT 0 AFTER is_consumed;

-- Remove useless proxy option
DELETE FROM options WHERE options.key = 'proxy_protocol';

-- Add column to hide acl resources
ALTER TABLE acl_resources ADD locked tinyint(1) NOT NULL DEFAULT 0 AFTER changed;

-- change column type
ALTER TABLE downtime_period MODIFY COLUMN `dtp_month_cycle` varchar(100);

-- add column for widget, select multiple
ALTER TABLE widget_parameters ADD COLUMN `multiple` TINYINT(1) NOT NULL DEFAULT 0 AFTER `field_type_id`;