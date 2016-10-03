-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.0-beta2' LIMIT 1;

INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES
('hostCategoriesMulti', 1),
('hostGroupMulti', 1),
('hostMulti', 1),
('metricMulti', 1),
('serviceCategory', 1),
('hostCategory', 1),
('serviceMulti', 1),
('serviceGroupMulti',1),
('pollerMulti',1);

UPDATE `options` SET `value`='/var/cache/centreon/backup' WHERE `key`='backup_backup_directory';

-- Update influxdb output
INSERT INTO cb_type_field_relation (cb_type_id, cb_field_id, cb_fieldset_id, is_required, order_display) VALUES(
(SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1),
(SELECT cb_field_id FROM cb_field WHERE fieldname = 'cache' LIMIT 1),
NULL,
0,
1
);

UPDATE cb_type_field_relation
SET order_display = 2
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (SELECT cb_field_id FROM cb_field WHERE fieldname = 'db_host' LIMIT 1);

UPDATE cb_type_field_relation
SET order_display = 3
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (SELECT cb_field_id FROM cb_field WHERE fieldname = 'db_port' LIMIT 1);

UPDATE cb_type_field_relation
SET order_display = 4,
is_required = 1
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (SELECT cb_field_id FROM cb_field WHERE fieldname = 'db_user' LIMIT 1);

UPDATE cb_type_field_relation
SET order_display = 5
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (SELECT cb_field_id FROM cb_field WHERE fieldname = 'db_password' LIMIT 1);

INSERT INTO cb_type_field_relation (cb_type_id, cb_field_id, cb_fieldset_id, is_required, order_display)
VALUES (
    (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1),
    (SELECT cb_field_id FROM cb_field WHERE fieldname = 'db_name' LIMIT 1),
    NULL,
    1,
    6
);

UPDATE cb_type_field_relation
SET order_display = 7
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (SELECT cb_field_id FROM cb_field WHERE fieldname = 'metrics_timeseries' LIMIT 1);

UPDATE cb_type_field_relation
SET order_display = 8
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (
    SELECT cbf.cb_field_id
    FROM cb_field cbf, cb_fieldgroup cbfg
    WHERE cbf.fieldname = 'name'
    AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id
    AND cbfg.groupname = 'metrics_column' LIMIT 1
    );

UPDATE cb_type_field_relation
SET order_display = 9
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (
    SELECT cbf.cb_field_id
    FROM cb_field cbf, cb_fieldgroup cbfg
    WHERE cbf.fieldname = 'value'
    AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id
    AND cbfg.groupname = 'metrics_column' LIMIT 1
    );

UPDATE cb_type_field_relation
SET order_display = 10
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (
    SELECT cbf.cb_field_id
    FROM cb_field cbf, cb_fieldgroup cbfg
    WHERE cbf.fieldname = 'type'
    AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id
    AND cbfg.groupname = 'metrics_column' LIMIT 1
    );

UPDATE cb_type_field_relation
SET order_display = 11
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (
    SELECT cbf.cb_field_id
    FROM cb_field cbf, cb_fieldgroup cbfg
    WHERE cbf.fieldname = 'is_tag'
    AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id
    AND cbfg.groupname = 'metrics_column' LIMIT 1
    );

UPDATE cb_type_field_relation
SET order_display = 12
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (SELECT cb_field_id FROM cb_field WHERE fieldname = 'status_timeseries' LIMIT 1);

UPDATE cb_type_field_relation
SET order_display = 13
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (
    SELECT cbf.cb_field_id
    FROM cb_field cbf, cb_fieldgroup cbfg
    WHERE cbf.fieldname = 'name'
    AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id
    AND cbfg.groupname = 'status_column' LIMIT 1
    );

UPDATE cb_type_field_relation
SET order_display = 14
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (
    SELECT cbf.cb_field_id
    FROM cb_field cbf, cb_fieldgroup cbfg
    WHERE cbf.fieldname = 'value'
    AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id
    AND cbfg.groupname = 'status_column' LIMIT 1
    );

UPDATE cb_type_field_relation
SET order_display = 15
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (
    SELECT cbf.cb_field_id
    FROM cb_field cbf, cb_fieldgroup cbfg
    WHERE cbf.fieldname = 'type'
    AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id
    AND cbfg.groupname = 'status_column' LIMIT 1
    );

UPDATE cb_type_field_relation
SET order_display = 16
WHERE cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
AND cb_field_id = (
    SELECT cbf.cb_field_id
    FROM cb_field cbf, cb_fieldgroup cbfg
    WHERE cbf.fieldname = 'is_tag'
    AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id
    AND cbfg.groupname = 'status_column' LIMIT 1
    );

-- Ticket #4687
ALTER TABLE timeperiod MODIFY tp_alias varchar(200);

-- Update maximum number of chart in performance
UPDATE `options` SET `value` = '18' WHERE `key` = 'maxGraphPerformances';

-- Can enable/disable chart extended information #4679
INSERT INTO `options` (`key`, `value`) VALUES
('display_downtime_chart','0'),
('display_comment_chart','0');

-- Add index for better performance on ods_view_details #4670
CREATE INDEX `contact_index` ON `ods_view_details` (`contact_id`, `index_id`) USING BTREE;

-- Replace Generate in breadcrumb by Export configuration
UPDATE topology SET topology_name = 'Export configuration' WHERE topology_page = 60902;