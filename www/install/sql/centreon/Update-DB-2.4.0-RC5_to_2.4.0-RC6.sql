-- Get globals informations from Centreon in Centreon Broker configuration
UPDATE cb_field SET external = 'T=options:C=value:CK=key:K=interval_length' WHERE cb_field_id = 16;
UPDATE cb_field SET external = 'D=centreon_storage:T=config:C=RRDdatabase_path:CK=id:K=1' WHERE cb_field_id = 13;
UPDATE cb_field SET external = 'D=centreon_storage:T=config:C=RRDdatabase_status_path:CK=id:K=1' WHERE cb_field_id = 14;
UPDATE cb_field SET external = 'D=centreon_storage:T=config:C=len_storage_rrd:CK=id:K=1' WHERE cb_field_id = 17;

-- Add informations for rrdcached in Centreon Broker
INSERT INTO cb_field (cb_field_id, fieldname, displayname, description, fieldtype, external) VALUES
(36, 'path', 'Unix socket', 'The Unix socket to use to communicate with rrdcached', 'text', 'T=options:C=value:CK=key:K=rrdcached_unix_path'),
(37, 'port', 'TCP port', 'The port od TCP socket to use to communicate with rrdcached', 'int', 'T=options:C=value:CK=key:K=rrdcached_port');
DELETE FROM cb_type_field_relation WHERE cb_type_id = 13 AND is_required = 0 AND cb_field_id IN (1, 11);
INSERT INTO cb_type_field_relation (cb_type_id, cb_field_id, is_required, order_display) VALUES
(13, 36, 0, 3),
(13, 37, 0, 4);

-- Add informations for Centreon Broker max_size log
INSERT INTO cb_field (cb_field_id, fieldname, displayname, description, fieldtype, external) VALUES
(38, 'max_size', 'Max file size in bytes', 'The maximum size of log file.', 'int', NULL);
INSERT INTO cb_type_field_relation (cb_type_id, cb_field_id, is_required, order_display) VALUES
(17, 38, 0, 7);

-- Add new logger type for Centreon Broker
INSERT INTO `cb_type` (`cb_type_id`, `type_name`, `type_shortname`, `cb_module_id`) VALUES
(24, 'Monitoring', 'monitoring', 9);
INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`, `cb_type_uniq`) VALUES
(3, 24, 0);
INSERT INTO cb_type_field_relation (cb_type_id, cb_field_id, is_required, order_display) VALUES
(24, 19, 1, 1),
(24, 20, 1, 2),
(24, 21, 1, 3),
(24, 22, 1, 4),
(24, 23, 1, 5),
(24, 24, 1, 6);

-- Add suport for random colors in curves
ALTER TABLE `giv_components_template` ADD `ds_color_line_mode` ENUM('0', '1') DEFAULT '0' AFTER `ds_color_line`;
ALTER TABLE `giv_components_template` ADD `ds_total` ENUM('0', '1') DEFAULT '0' AFTER `ds_last`;
UPDATE `giv_components_template` SET `ds_color_line_mode` = '1' WHERE ds_name LIKE 'Default_DS%';

UPDATE `informations` SET `value` = '2.4.0-RC6' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC5' LIMIT 1;
