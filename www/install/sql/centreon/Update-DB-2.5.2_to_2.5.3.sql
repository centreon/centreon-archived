UPDATE cb_field SET external = 'T=options:C=value:CK=key:K=index_data', fieldtype = 'text' WHERE cb_field_id = 43;
INSERT cb_field (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES
(49, 'cleanup_check_interval', "Cleanup check interval", "Interval in seconds before delete data from deleted pollers.", 'int', NULL),
(50, 'instance_timeout', "Instance timeout", "Interval in seconds before change status of resources from a disconnected poller", "int", NULL);

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(16, 49, 0, 18),
(16, 50, 0, 19);

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.5.3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.5.2' LIMIT 1;
