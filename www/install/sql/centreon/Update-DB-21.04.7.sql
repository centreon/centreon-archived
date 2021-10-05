-- Add TLS hostname in config brocker input/outputs IPV4
INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES
(76, 'tls_hostname', 'TLS Host name', 'Expected TLS certificate common name (CN) - leave blank if unsure.', 'text', NULL);

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(3, 76, 0, 5);