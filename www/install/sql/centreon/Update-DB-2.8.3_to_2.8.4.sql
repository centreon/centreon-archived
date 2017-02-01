-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.4' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.3' LIMIT 1;

-- Use service
UPDATE `options` SET `value` = 'cbd' WHERE `key` = 'broker_correlator_script' AND `value` = '/etc/init.d/cbd';
UPDATE `nagios_server` SET `init_script` = 'centengine' WHERE `init_script` = '/etc/init.d/centengine';
UPDATE `nagios_server` SET `init_script_centreontrapd` = 'centreontrapd' WHERE `init_script_centreontrapd` = '/etc/init.d/centreontrapd';

