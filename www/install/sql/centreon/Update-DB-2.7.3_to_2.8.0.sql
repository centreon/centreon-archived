-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.3' LIMIT 1;

-- Add graphite output for centreon-broker
INSERT INTO cb_module (name, libname, loading_pos, is_activated) 
VALUES ('Graphite', 'graphite.so', 21, 1);

INSERT INTO cb_type (type_name, type_shortname, cb_module_id)
VALUES ('Storage - Graphite', 'graphite',
    (SELECT MAX(cb_module_id) FROM cb_module)
);

INSERT INTO cb_tag_type_relation (cb_tag_id, cb_type_id) 
VALUES (
    (SELECT cb_tag_id FROM cb_tag WHERE tagname = 'output' LIMIT 1),
    (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'graphite' LIMIT 1)
);

INSERT INTO cb_field (fieldname, displayname, description, fieldtype)
VALUES 
    ('metric_naming', 'Metric naming', 'How to name entries for metrics. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text'),
    ('status_naming', 'Status naming', 'How to name entries for statuses. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text');

INSERT INTO cb_type_field_relation (cb_type_id, is_required, cb_field_id, order_display)
    (SELECT (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'graphite' LIMIT 1), 0, cb_field_id, @rownum := @rownum + 1
    FROM cb_field CROSS JOIN (SELECT @rownum := 0) r
    WHERE fieldname IN ('db_host', 'db_port', 'db_user', 'db_password',
        'queries_per_transaction', 'failover', 'metric_naming', 'status_naming')
    ORDER BY FIELD(fieldname, 'db_host', 'db_port', 'db_user', 'db_password',
        'queries_per_transaction', 'failover', 'metric_naming', 'status_naming')
    );

UPDATE cb_type_field_relation SET is_required = 1
WHERE
    cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'graphite' LIMIT 1)
    AND cb_field_id IN (SELECT cb_field_id FROM cb_field where fieldname IN ('db_host', 'metric_naming', 'status_naming'));


-- adding new params field types
insert into widget_parameters_field_type (ft_typename,is_connector) VALUES ('hostCategories',1);
insert into widget_parameters_field_type (ft_typename,is_connector) VALUES ('serviceCategories',1);
insert into widget_parameters_field_type (ft_typename,is_connector) VALUES ('metric',1);
alter table cfg_centreonbroker_info add column fieldIndex int(11) null default null;

INSERT INTO cb_module (name, libname, loading_pos, is_activated)
VALUES ('InfluxDB', 'influxdb.so', 22, 1);

INSERT INTO cb_type (type_name, type_shortname, cb_module_id)
VALUES ('Storage - InfluxDB', 'influxdb',
    (SELECT MAX(cb_module_id) FROM cb_module)
);

INSERT INTO cb_tag_type_relation (cb_tag_id, cb_type_id)
VALUES (
    (SELECT cb_tag_id FROM cb_tag WHERE tagname = 'output' LIMIT 1),
    (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
);

ALTER TABLE cb_fieldgroup ADD displayname varchar(255) NOT NULL DEFAULT '' AFTER groupname;
ALTER TABLE cb_fieldgroup ADD multiple tinyint(1) NOT NULL DEFAULT 0 AFTER displayname;

INSERT INTO cb_fieldgroup (groupname, displayname, multiple, group_parent_id)
VALUES
    ('metrics_column', 'Metrics column', 1, NULL),
    ('status_column', 'Status column', 1, NULL);

INSERT INTO cb_field (fieldname, displayname, description, fieldtype, cb_fieldgroup_id)
VALUES
    ('metrics_timeseries', 'Metrics timeseries', 'How to name entries for metrics timeseries. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text', NULL),
    ('status_timeseries', 'Status timeseries', 'How to name entries for statuses timeseries. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text', NULL),
    ('name', 'Name', 'Name of the column (macros accepted)', 'text', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'metrics_column')),
    ('value', 'Value', 'Value of the column (macros accepted)', 'text', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'metrics_column')),
    ('type', 'Type', 'Type of the column', 'select', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'metrics_column')),
    ('is_tag', 'Tag', 'Whether or not this column is a tag', 'radio', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'metrics_column')),
    ('name', 'Name', 'Name of the column (macros accepted)', 'text', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'status_column')),
    ('value', 'Value', 'Value of the column (macros accepted)', 'text', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'status_column')),
    ('type', 'Type', 'Type of the column', 'select', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'status_column')),
    ('is_tag', 'Tag', 'Whether or not this column is a tag', 'radio', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'status_column'));

INSERT INTO cb_list (cb_list_id, cb_field_id, default_value)
VALUES
    ((SELECT MAX(cbl1.cb_list_id) + 1 FROM cb_list cbl1), (SELECT cbf.cb_field_id FROM cb_field cbf, cb_fieldgroup cbfg WHERE cbf.fieldname = 'type' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'metrics_column' LIMIT 1), 'string'),
    ((SELECT MAX(cbl1.cb_list_id) + 2 FROM cb_list cbl1), (SELECT cbf.cb_field_id FROM cb_field cbf, cb_fieldgroup cbfg WHERE cbf.fieldname = 'is_tag' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'metrics_column' LIMIT 1), 'false'),
    ((SELECT MAX(cbl1.cb_list_id) + 3 FROM cb_list cbl1), (SELECT cbf.cb_field_id FROM cb_field cbf, cb_fieldgroup cbfg WHERE cbf.fieldname = 'type' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'status_column' LIMIT 1), 'string'),
    ((SELECT MAX(cbl1.cb_list_id) + 4 FROM cb_list cbl1), (SELECT cbf.cb_field_id FROM cb_field cbf, cb_fieldgroup cbfg WHERE cbf.fieldname = 'is_tag' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'status_column' LIMIT 1), 'false');

INSERT INTO cb_list_values (cb_list_id, value_name, value_value)
VALUES
    ((SELECT cbl.cb_list_id FROM cb_list cbl, cb_field cbf, cb_fieldgroup cbfg WHERE cbl.cb_field_id = cbf.cb_field_id AND cbf.fieldname = 'type' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'metrics_column' LIMIT 1), 'String', 'string'),
    ((SELECT cbl.cb_list_id FROM cb_list cbl, cb_field cbf, cb_fieldgroup cbfg WHERE cbl.cb_field_id = cbf.cb_field_id AND cbf.fieldname = 'type' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'metrics_column' LIMIT 1), 'Number', 'number'),
    ((SELECT cbl.cb_list_id FROM cb_list cbl, cb_field cbf, cb_fieldgroup cbfg WHERE cbl.cb_field_id = cbf.cb_field_id AND cbf.fieldname = 'is_tag' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'metrics_column' LIMIT 1), 'True', 'true'),
    ((SELECT cbl.cb_list_id FROM cb_list cbl, cb_field cbf, cb_fieldgroup cbfg WHERE cbl.cb_field_id = cbf.cb_field_id AND cbf.fieldname = 'is_tag' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'metrics_column' LIMIT 1), 'False', 'false'),
    ((SELECT cbl.cb_list_id FROM cb_list cbl, cb_field cbf, cb_fieldgroup cbfg WHERE cbl.cb_field_id = cbf.cb_field_id AND cbf.fieldname = 'type' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'status_column' LIMIT 1), 'String', 'string'),
    ((SELECT cbl.cb_list_id FROM cb_list cbl, cb_field cbf, cb_fieldgroup cbfg WHERE cbl.cb_field_id = cbf.cb_field_id AND cbf.fieldname = 'type' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'status_column' LIMIT 1), 'Number', 'number'),
    ((SELECT cbl.cb_list_id FROM cb_list cbl, cb_field cbf, cb_fieldgroup cbfg WHERE cbl.cb_field_id = cbf.cb_field_id AND cbf.fieldname = 'is_tag' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'status_column' LIMIT 1), 'True', 'true'),
    ((SELECT cbl.cb_list_id FROM cb_list cbl, cb_field cbf, cb_fieldgroup cbfg WHERE cbl.cb_field_id = cbf.cb_field_id AND cbf.fieldname = 'is_tag' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'status_column' LIMIT 1), 'False', 'false');
    
INSERT INTO cb_type_field_relation (cb_type_id, is_required, cb_field_id, order_display)
    (SELECT (SELECT cbt.cb_type_id FROM cb_type cbt WHERE cbt.type_shortname = 'influxdb' LIMIT 1), 0, cbf1.cb_field_id, @rownum := @rownum + 1
    FROM cb_field cbf1 CROSS JOIN (SELECT @rownum := 0) r
    WHERE cbf1.cb_field_id IN (SELECT cbf2.cb_field_id FROM cb_field cbf2 WHERE cbf2.fieldname IN (
        'db_host', 'db_port', 'db_user', 'db_password',
        'metrics_timeseries', 'status_timeseries')
    )
    OR cbf1.cb_fieldgroup_id IN (SELECT cbfg.cb_fieldgroup_id FROM cb_fieldgroup cbfg WHERE cbfg.groupname IN (
        'metrics_column', 'status_column')
    )
    ORDER BY FIELD(cbf1.fieldname, 'db_host', 'db_port', 'db_user', 'db_password',
        'queries_per_transaction', 'failover', 'metrics_timeseries', 'status_timeseries')
    );

UPDATE cb_type_field_relation SET is_required = 1
WHERE
    cb_type_id = (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'influxdb' LIMIT 1)
    AND cb_field_id IN (SELECT cb_field_id FROM cb_field where fieldname IN ('db_host', 'metrics_timeseries', 'status_timeseries'));

CREATE TABLE IF NOT EXISTS `locale` (
  `locale_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`locale_id`),
  `locale_short_name` varchar(3) NOT NULL,
  `locale_long_name` varchar(255) NOT NULL,
  `locale_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `locale` ( `locale_short_name`, `locale_long_name`, `locale_img`) VALUES
('en', 'English', 'en.png'),
('fr', 'French', 'fr.png');

ALTER TABLE `cfg_nagios`
    DROP COLUMN `temp_path`,
    DROP COLUMN `p1_file`,
    DROP COLUMN `enable_embedded_perl`,
    DROP COLUMN `use_embedded_perl_implicitly`;

