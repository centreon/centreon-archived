-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.0-beta1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.9' LIMIT 1;

-- Add graphite output for centreon-broker
INSERT IGNORE INTO cb_module (name, libname, loading_pos, is_activated)
VALUES ('Graphite', 'graphite.so', 21, 1);

INSERT IGNORE INTO cb_type (type_name, type_shortname, cb_module_id)
VALUES ('Storage - Graphite', 'graphite',
    (SELECT MAX(cb_module_id) FROM cb_module)
);

INSERT IGNORE INTO cb_tag_type_relation (cb_tag_id, cb_type_id)
VALUES (
    (SELECT cb_tag_id FROM cb_tag WHERE tagname = 'output' LIMIT 1),
    (SELECT cb_type_id FROM cb_type WHERE type_shortname = 'graphite' LIMIT 1)
);

INSERT IGNORE INTO cb_field (fieldname, displayname, description, fieldtype)
VALUES
    ('metric_naming', 'Metric naming', 'How to name entries for metrics. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text'),
    ('status_naming', 'Status naming', 'How to name entries for statuses. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text');

INSERT IGNORE INTO cb_type_field_relation (cb_type_id, is_required, cb_field_id, order_display)
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
    ('is_tag', 'Tag', 'Whether or not this column is a tag', 'radio', (SELECT cb_fieldgroup_id FROM cb_fieldgroup WHERE groupname = 'status_column')),
    ('cache', 'Cache', 'Enable caching', 'radio', NULL),
    ('storage_db_host', 'Storage DB host', 'IP address or hostname of the database server.', 'text', NULL),
    ('storage_db_user', 'Storage DB user', 'Database user.', 'text', NULL),
    ('storage_db_password', 'Storage DB password', 'Password of database user.', 'password', NULL),
    ('storage_db_name', 'Storage DB name', 'Database name.', 'text', NULL),
    ('storage_db_port', 'Storage DB port', 'Port on which the DB server listens', 'int', NULL),
    ('storage_db_type', 'Storage DB type', 'Target DBMS.', 'select', NULL);

INSERT INTO cb_list (cb_list_id, cb_field_id, default_value)
VALUES
    ((SELECT MAX(cbl1.cb_list_id) + 1 FROM cb_list cbl1), (SELECT cbf.cb_field_id FROM cb_field cbf, cb_fieldgroup cbfg WHERE cbf.fieldname = 'type' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'metrics_column' LIMIT 1), 'string'),
    ((SELECT MAX(cbl1.cb_list_id) + 2 FROM cb_list cbl1), (SELECT cbf.cb_field_id FROM cb_field cbf, cb_fieldgroup cbfg WHERE cbf.fieldname = 'is_tag' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'metrics_column' LIMIT 1), 'false'),
    ((SELECT MAX(cbl1.cb_list_id) + 3 FROM cb_list cbl1), (SELECT cbf.cb_field_id FROM cb_field cbf, cb_fieldgroup cbfg WHERE cbf.fieldname = 'type' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'status_column' LIMIT 1), 'string'),
    ((SELECT MAX(cbl1.cb_list_id) + 4 FROM cb_list cbl1), (SELECT cbf.cb_field_id FROM cb_field cbf, cb_fieldgroup cbfg WHERE cbf.fieldname = 'is_tag' AND cbf.cb_fieldgroup_id = cbfg.cb_fieldgroup_id AND cbfg.groupname = 'status_column' LIMIT 1), 'false'),
    (1, (SELECT cbf.cb_field_id FROM cb_field cbf WHERE cbf.fieldname = 'cache' LIMIT 1), 'yes'),
    (3, (SELECT cbf.cb_field_id FROM cb_field cbf WHERE cbf.fieldname = 'storage_db_type' LIMIT 1), NULL);

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

ALTER TABLE `service` ADD COLUMN `service_use_only_contacts_from_host` enum('0','1') DEFAULT '0' AFTER `service_inherit_contacts_from_host`;

ALTER TABLE `host` ADD COLUMN `host_acknowledgement_timeout` int(11) DEFAULT NULL AFTER `host_first_notification_delay`;
ALTER TABLE `service` ADD COLUMN `service_acknowledgement_timeout` int(11) DEFAULT NULL AFTER `service_first_notification_delay`;

-- Ticket #2425
UPDATE topology SET topology_url = './include/Administration/myAccount/formMyAccount.php' WHERE topology_page = 50104;
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (50104,NULL,'./include/common/javascript/changetab.js','initChangeTab');
ALTER TABLE contact ADD COLUMN `default_page` int(11) DEFAULT NULL AFTER `contact_autologin_key`;

-- Ticket #4401
ALTER TABLE nagios_server ADD COLUMN `init_system` varchar(255) DEFAULT 'sytemv' AFTER `init_script`;
UPDATE `nagios_server` SET `init_system` = 'systemv';

ALTER TABLE topology DROP COLUMN topology_icone;

-- ALTER TABLE host_service_relation DROP INDEX `host_host_id`;

-- Change option Path
UPDATE topology SET topology_url = './include/Administration/parameters/parameters.php', topology_url_opt = '&o=general' WHERE topology_page = 50110;
UPDATE topology SET topology_url = './include/Administration/parameters/parameters.php', topology_url_opt = '&o=engine' WHERE topology_page = 50111;
UPDATE topology SET topology_url = './include/Administration/parameters/parameters.php', topology_url_opt = '&o=centcore' WHERE topology_page = 50117;
UPDATE topology SET topology_url = './include/Administration/parameters/parameters.php', topology_url_opt = '&o=ldap' WHERE topology_page = 50113;
UPDATE topology SET topology_url = './include/Administration/parameters/parameters.php', topology_url_opt = '&o=rrdtool' WHERE topology_page = 50114;
UPDATE topology SET topology_url = './include/Administration/parameters/parameters.php', topology_url_opt = '&o=debug' WHERE topology_page = 50115;
UPDATE topology SET topology_url = './include/Administration/parameters/parameters.php', topology_url_opt = '&o=css' WHERE topology_page = 50116;
UPDATE topology SET topology_url = './include/Administration/parameters/parameters.php', topology_url_opt = '&o=storage' WHERE topology_page = 50118;
UPDATE topology SET topology_url = './include/Administration/performance/manageData.php' WHERE topology_page = 50119;
UPDATE topology SET topology_url = './include/views/componentTemplates/componentTemplates.php' WHERE topology_page = 20405;
UPDATE topology SET topology_url = './include/views/graphTemplates/graphTemplates.php' WHERE topology_page = 20404;
UPDATE topology SET topology_url = './include/views/virtualMetrics/virtualMetrics.php' WHERE topology_page = 20408;


-- Remove meta service page in the monitoring
DELETE FROM topology WHERE  topology_name = 'Meta Services' AND topology_parent = 202 AND (topology_page IS NULL OR topology_page = 20206);

ALTER TABLE cfg_nagios DROP COLUMN `free_child_process_memory`;
ALTER TABLE cfg_nagios DROP COLUMN `child_processes_fork_twice`;

UPDATE topology set readonly = '0' WHERE topology_page = 60901;

-- Add an API Access configuration page
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (122,'API',501,50120,100,1,'./include/Administration/parameters/parameters.php','&o=api','0','0','1',NULL,NULL,NULL,'1');

-- Add an KB Access configuration page

DELETE FROM `topology` WHERE `topology_parent` = 610;
INSERT INTO `topology` (`topology_id` ,`topology_name` ,`topology_parent` ,`topology_page` ,`topology_order` ,`topology_group` ,`topology_url` ,`topology_url_opt` ,`topology_popup` ,`topology_modules` ,`topology_show` ,`topology_style_class` ,`topology_style_id` ,`topology_OnClick`) VALUES (NULL , 'Knowledge Base', '610', NULL , NULL , '36', NULL , NULL , NULL , NULL , '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id` ,`topology_name` ,`topology_parent` ,`topology_page` ,`topology_order` ,`topology_group` ,`topology_url` ,`topology_url_opt` ,`topology_popup` ,`topology_modules` ,`topology_show` ,`topology_style_class` ,`topology_style_id` ,`topology_OnClick`) VALUES (NULL , 'Knowledge Base', '6', '610', '65', '36', NULL, NULL , NULL , '1', '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id` ,`topology_name` ,`topology_parent` ,`topology_page` ,`topology_order` ,`topology_group` ,`topology_url` ,`topology_url_opt` ,`topology_popup` ,`topology_modules` ,`topology_show` ,`topology_style_class` ,`topology_style_id` ,`topology_OnClick`) VALUES (NULL , 'Hosts', '610', '61001', '5', '36', './include/configuration/configKnowledge/display-hosts.php', NULL , NULL , '1', '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id` ,`topology_name` ,`topology_parent` ,`topology_page` ,`topology_order` ,`topology_group` ,`topology_url` ,`topology_url_opt` ,`topology_popup` ,`topology_modules` ,`topology_show` ,`topology_style_class` ,`topology_style_id` ,`topology_OnClick`) VALUES (NULL , 'Host Templates', '610', '61003', '15', '36', './include/configuration/configKnowledge/display-hostTemplates.php', NULL , NULL , '1', '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id` ,`topology_name` ,`topology_parent` ,`topology_page` ,`topology_order` ,`topology_group` ,`topology_url` ,`topology_url_opt` ,`topology_popup` ,`topology_modules` ,`topology_show` ,`topology_style_class` ,`topology_style_id` ,`topology_OnClick`) VALUES (NULL , 'Services', '610', '61002', '10', '36', './include/configuration/configKnowledge/display-services.php', NULL , NULL , '1', '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id` ,`topology_name` ,`topology_parent` ,`topology_page` ,`topology_order` ,`topology_group` ,`topology_url` ,`topology_url_opt` ,`topology_popup` ,`topology_modules` ,`topology_show` ,`topology_style_class` ,`topology_style_id` ,`topology_OnClick`) VALUES (NULL , 'Service Templates', '610', '61004', '20', '36', './include/configuration/configKnowledge/display-serviceTemplates.php', NULL , NULL , '1', '1', NULL , NULL , NULL);

-- Add possibility to limit access to API
ALTER TABLE contact ADD COLUMN `reach_api` int(11) DEFAULT '0' AFTER `contact_oreon`;

-- INITCHANGETAB js is defined by default
DELETE FROM topology_JS WHERE Init = 'initChangeTab';

DELETE FROM topology WHERE topology_page = 50120;

-- Insert geo_coords
ALTER TABLE host ADD COLUMN `geo_coords` varchar(32) DEFAULT NULL AFTER `host_comment`;
ALTER TABLE hostgroup ADD COLUMN `geo_coords` varchar(32) DEFAULT NULL AFTER `hg_rrd_retention`;
ALTER TABLE meta_service ADD COLUMN `geo_coords` varchar(32) DEFAULT NULL AFTER `meta_comment`;
ALTER TABLE service ADD COLUMN `geo_coords` varchar(32) DEFAULT NULL AFTER `service_comment`;
ALTER TABLE servicegroup ADD COLUMN `geo_coords` varchar(32) DEFAULT NULL AFTER `sg_comment`;

-- Move Template and curve templace in another location
UPDATE topology SET topology_url = './include/views/graphTemplates/graphTemplates.php' WHERE topology_page = 20404;
UPDATE topology SET topology_url = './include/views/componentTemplates/componentTemplates.php' WHERE topology_page = 20405;
UPDATE topology SET topology_url = './include/views/virtualMetrics/virtualMetrics.php' WHERE topology_page = 20408;

-- Add recovery_notification_delay columns
ALTER TABLE `host` ADD COLUMN `host_recovery_notification_delay` int(11) DEFAULT NULL AFTER `host_first_notification_delay`;
ALTER TABLE `service` ADD COLUMN `service_recovery_notification_delay` int(11) DEFAULT NULL AFTER `service_first_notification_delay`;

-- Add possibility to disable command
ALTER TABLE `command` ADD COLUMN `command_activate` enum('0','1') DEFAULT '1' AFTER `command_comment`;
