-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.6' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.5' LIMIT 1;

-- Use service
UPDATE `options` SET `value` = 'cbd' WHERE `key` = 'broker_correlator_script' AND `value` = '/etc/init.d/cbd';
UPDATE `nagios_server` SET `init_script` = 'centengine' WHERE `init_script` = '/etc/init.d/centengine';
UPDATE `nagios_server` SET `init_script_centreontrapd` = 'centreontrapd' WHERE `init_script_centreontrapd` = '/etc/init.d/centreontrapd';

-- Change state colors
UPDATE `options` SET `value` = '#88b917' WHERE `key` in ('color_up', 'color_ok');
UPDATE `options` SET `value` = '#e00b3d' WHERE `key` in ('color_down', 'color_critical', 'color_host_down');
UPDATE `options` SET `value` = '#818285' WHERE `key` in ('color_unreachable', 'color_host_unreachable');
UPDATE `options` SET `value` = '#ff9a13' WHERE `key` = 'color_warning';
UPDATE `options` SET `value` = '#2ad1d4' WHERE `key` = 'color_pending';
UPDATE `options` SET `value` = '#ae9500' WHERE `key` = 'color_ack';
UPDATE `options` SET `value` = '#bcbdc0' WHERE `key` = 'color_unknown';
UPDATE `options` SET `value` = '#cc99ff' WHERE `key` = 'color_downtime';