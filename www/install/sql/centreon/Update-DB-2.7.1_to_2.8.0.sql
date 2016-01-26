-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.1' LIMIT 1;

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