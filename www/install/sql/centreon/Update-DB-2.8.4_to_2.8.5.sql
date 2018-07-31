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
UPDATE custom_view_user_relation SET is_share = 1 WHERE is_owner = 0;

-- Remove useless proxy option
DELETE FROM options WHERE options.key = 'proxy_protocol';

-- Add column to hide acl resources
ALTER TABLE acl_resources ADD locked tinyint(1) NOT NULL DEFAULT 0 AFTER changed;

-- Update broker cache directory column name
ALTER TABLE cfg_centreonbroker CHANGE COLUMN `retention_path` `cache_directory` VARCHAR(255) DEFAULT NULL;

-- change column type
ALTER TABLE downtime_period MODIFY COLUMN `dtp_month_cycle` varchar(100);

-- Add topology for split
INSERT INTO `topology` (`topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_show`, `readonly`)
VALUES ('Chart split', 20401, 2040101, 1, 1, './include/views/graphs/graph-split.php', 0, 1);
INSERT INTO `topology` (`topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_show`, `readonly`)
VALUES ('Chart periods', 20401, 2040102, 1, 1, './include/views/graphs/graph-periods.php', 0, 1);

-- Fix problem regarding the recurrent downtimes
UPDATE topology set topology_url_opt = NULL WHERE topology_page = 21003;

-- Update event queue max size
ALTER TABLE cfg_centreonbroker CHANGE COLUMN `event_queue_max_size` `event_queue_max_size` int(11) DEFAULT 100000;
UPDATE cfg_centreonbroker SET event_queue_max_size = 100000 WHERE event_queue_max_size < 100000;
