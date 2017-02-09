-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.4' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.3' LIMIT 1;

-- Use service
UPDATE `options` SET `value` = 'cbd' WHERE `key` = 'broker_correlator_script' AND `value` = '/etc/init.d/cbd';
UPDATE `nagios_server` SET `init_script` = 'centengine' WHERE `init_script` = '/etc/init.d/centengine';
UPDATE `nagios_server` SET `init_script_centreontrapd` = 'centreontrapd' WHERE `init_script_centreontrapd` = '/etc/init.d/centreontrapd';

-- Missing 'integer' type, mostly used for auto-refresh preference.
INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES ('integer', 0);

-- custom views share options
ALTER TABLE custom_view_user_relation ADD is_public tinyint(1) NOT NULL DEFAULT 0 AFTER is_consumed;
ALTER TABLE custom_view_user_relation ADD is_share tinyint(1) NOT NULL DEFAULT 0 AFTER is_public;
