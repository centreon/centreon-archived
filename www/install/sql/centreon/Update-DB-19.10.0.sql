-- Update broker form
UPDATE `cb_field` SET `fieldname` = 'rrd_cached_option', `displayname` = 'Cache Option', `description` = 'Use RRD cache.', `fieldtype` = 'radio', `external` = NULL
WHERE `fieldname` = 'path' AND `displayname` = 'Unix socket';

UPDATE `cb_field` SET `fieldname` = 'rrd_cached', `displayname` = 'Cache Link', `description` = 'The Unix socket or the TCP port used to communicate with rrdcached.', `fieldtype` = 'text', `external` = NULL
WHERE `fieldname` = 'port' AND `displayname` = 'TCP port';

INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`)
VALUES((SELECT coalesce(MAX(l.cb_list_id),0)+1 from cb_list l), (SELECT `cb_field_id` FROM `cb_field` WHERE `description` = 'Use RRD cache.'), 'disable');

INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`)
VALUES
((SELECT `cb_list_id` FROM `cb_list` WHERE `cb_field_id` =
  (SELECT `cb_field_id` FROM `cb_field` WHERE `description` = 'Use RRD cache.')), 'Disable', 'disable'
),
((SELECT `cb_list_id` FROM `cb_list` WHERE `cb_field_id` =
  (SELECT `cb_field_id` FROM `cb_field` WHERE `description` = 'Use RRD cache.')), 'UNIX Socket', 'unix'
),
((SELECT `cb_list_id` FROM `cb_list` WHERE `cb_field_id` =
  (SELECT `cb_field_id` FROM `cb_field` WHERE `description` = 'Use RRD cache.')), 'TCP Port ', 'tcp'
);

UPDATE `cb_type_field_relation` SET `jshook_name` = 'rrdArguments', `jshook_arguments` = '{"target": "cache"}'
WHERE `cb_type_id` = (SELECT `cb_type_id` FROM `cb_type` WHERE `type_shortname` = 'rrd') AND `cb_field_id` = (SELECT `cb_field_id` FROM `cb_field` WHERE `description` = 'Use RRD cache.');
