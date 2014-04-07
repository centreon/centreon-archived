ALTER TABLE `connector` MODIFY `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `connector` MODIFY `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `connector` MODIFY `command_line` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci;

UPDATE `connector` SET `command_line` = '$USER3$/centreon_connector_perl' WHERE `command_line` = '$USER2$/centreon_connector_perl';
UPDATE `connector` SET `command_line` = '$USER3$/centreon_connector_ssh' WHERE `command_line` = '$USER2$/centreon_connector_ssh';

UPDATE cb_field SET description = 'Port to listen on (empty host) or to connect to (with host filled).' WHERE fieldname = 'port';
UPDATE cb_field SET description = 'IP address or hostname of the host to connect to (leave blank for listening mode).' WHERE fieldname = 'host';
UPDATE cb_field SET description = 'Trusted CA''s certificate.' WHERE fieldname = 'ca_certificate';
UPDATE cb_field SET description = 'Private key file path when TLS encryption is used.' WHERE fieldname = 'private_key';
UPDATE cb_field SET description = 'Public certificate file path when TLS encryption is used.' WHERE fieldname = 'public_cert';
UPDATE cb_field SET description = 'Enable TLS encryption.' WHERE fieldname = 'tls';
UPDATE cb_field SET description = 'IP address or hostname of the database server.' WHERE fieldname = 'db_host';
UPDATE cb_field SET description = 'User for connect to database' WHERE fieldname = 'db_user';
UPDATE cb_field SET description = 'Used password for connect to the database.' WHERE fieldname = 'db_password';
UPDATE cb_field SET description = 'Database name.' WHERE fieldname = 'db_name';
UPDATE cb_field SET description = 'Path to the file.' WHERE fieldname = 'path';
UPDATE cb_field SET description = 'Serialization protocol.' WHERE fieldname = 'protocol';
UPDATE cb_field SET description = 'Metrics RRD Directory, for example /var/lib/centreon/metrics' WHERE fieldname = 'metrics_path';
UPDATE cb_field SET description = 'Status RRD Directory, for example /var/lib/centreon/status' WHERE fieldname = 'status_path';
UPDATE cb_field SET description = 'Target DBMS.' WHERE fieldname = 'db_type';
UPDATE cb_field SET description = 'Interval Length in seconds.' WHERE fieldname = 'interval';
UPDATE cb_field SET description = 'RRD storage duration in seconds.' WHERE fieldname = 'length';

UPDATE `informations` SET `value` = '2.4.0-RC3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC2' LIMIT 1;