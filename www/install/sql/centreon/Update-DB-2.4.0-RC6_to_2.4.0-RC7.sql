-- Update 'RRD length' field definition
UPDATE cb_field SET `external` = 'D=centreon_storage:T=config:C=len_storage_rrd:RPN=86400 *:CK=id:K=1' WHERE cb_field_id=17;

CREATE TABLE `auth_ressource_host` (
    `ldap_host_id` INT(11) NOT NULL AUTO_INCREMENT,
    `auth_ressource_id` INT(11) NOT NULL,
    `host_address` VARCHAR(255) NOT NULL,
    `host_port` INT(11) NOT NULL,
    `use_ssl` TINYINT NULL DEFAULT 0,
    `use_tls` TINYINT NULL DEFAULT 0,
    `host_order` TINYINT NOT NULL DEFAULT 1,
    PRIMARY KEY (`ldap_host_id`),
    CONSTRAINT `fk_auth_ressource_id`
    FOREIGN KEY (`auth_ressource_id`)
    REFERENCES `auth_ressource` (`ar_id`)
    ON DELETE CASCADE
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `auth_ressource` DROP COLUMN `ar_order`;
ALTER TABLE `auth_ressource` ADD COLUMN `ar_name` VARCHAR (255) NOT NULL DEFAULT 'Default' AFTER `ar_id`;
ALTER TABLE `auth_ressource` ADD COLUMN `ar_description` VARCHAR (255) NOT NULL DEFAULT 'Default description' AFTER `ar_name`;

ALTER TABLE `contact` ADD COLUMN `ar_id` INT (11) DEFAULT NULL AFTER `contact_ldap_dn`;
ALTER TABLE `contact` ADD CONSTRAINT `fk_ar_id` FOREIGN KEY (`ar_id`) REFERENCES `auth_ressource` (`ar_id`) ON DELETE SET NULL;

-- Remove all logAnalyser entries to ensure only one will exist
DELETE FROM `cron_operation` WHERE `name` = 'logAnalyser';

-- Meta service compliant with new perfdata syntax
ALTER TABLE `meta_service` ADD COLUMN `data_source_type` TINYINT (3) NOT NULL DEFAULT '0' AFTER `calcul_type`;


ALTER TABLE `contact` CHANGE `contact_enable_notifications` `contact_enable_notifications` ENUM(  '0',  '1',  '2' ) DEFAULT '2';


-- broker conf

INSERT INTO `cb_tag` (`cb_tag_id`, `tagname`) VALUES (6, 'temporary');

INSERT INTO `cb_module` (`cb_module_id`, `name`, `libname`, `loading_pos`, `is_bundle`, `is_activated`) VALUES (15, 'Temporary', NULL, NULL, 0, 1);

INSERT INTO `cb_type` (`cb_type_id`, `type_name`, `type_shortname`, `cb_module_id`) VALUES (25, 'File', 'file', 15);

INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`, `cb_type_uniq`) VALUES (6, 25, 1);

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(25, 11, 1, 1),
(25, 12, 1, 2),
(25, 25, 1, 3),
(25, 26, 0, 4),
(25, 27, 0, 5);

INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) 
VALUES (41, 'max_size', 'Maximum size of file', 'Maximum size in bytes.', 'int', NULL);

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(11, 41, 0, 3),
(25, 41, 0, 6);

ALTER TABLE `connector` MODIFY `command_line` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `cfg_centreonbroker` ADD COLUMN `event_queue_max_size` INT (11) DEFAULT 50000 AFTER `ns_nagios_server`;

UPDATE `cb_field` SET `displayname` = 'Replication enabled', `description` = 'When enabled, the broker engine will check whether or not the replication is up to date before attempting to update data.' WHERE `fieldname` = 'check_replication';

UPDATE `cb_field` SET `description` = 'Ranges from 0 (no compression) to 9 (best compression). Default is -1 (zlib compression)' WHERE `fieldname` = 'compression_level';


-- update case on cb_type

UPDATE `cb_type` SET `type_name` = 'Local server socket' WHERE `type_name` = 'Local Server Socket';

UPDATE `cb_type` SET `type_name` = 'RRD file generator' WHERE `type_name` = 'RRD File Generator';

UPDATE `cb_type` SET `type_name` = 'Perfdata generator (Centreon Storage)' WHERE `type_name` = 'Perfdata Generator (Centreon Storage)';

UPDATE `cb_type` SET `type_name` = 'Local client socket' WHERE `type_name` = 'Local Client Socket';

UPDATE `cb_type` SET `type_name` = 'Broker SQL database' WHERE `type_name` = 'Broker SQL Database';


-- update case on cb_field.displayname

UPDATE `cb_field` SET `displayname` = 'Serialization protocol' WHERE `displayname` = 'Serialization Protocol';

UPDATE `cb_field` SET `displayname` = 'RRD file directory for metrics' WHERE `displayname` = 'Metrics RRD Directory';

UPDATE `cb_field` SET `displayname` = 'RRD file directory for statuses' WHERE `displayname` = 'Status RRD Directory';

UPDATE `cb_field` SET `displayname` = 'Interval length' WHERE `displayname` = 'Interval Length';

UPDATE `cb_field` SET `displayname` = 'RRD length' WHERE `displayname` = 'RRD Length';

UPDATE `cb_field` SET `displayname` = 'DB port' WHERE `displayname` = 'DB Port';

UPDATE `cb_field` SET `displayname` = 'Failover name' WHERE `displayname` = 'Failover Name';

UPDATE `cb_field` SET `displayname` = 'Correlation file' WHERE `displayname` = 'Correlation File';

UPDATE `cb_field` SET `displayname` = 'Retention file' WHERE `displayname` = 'Retention File';

UPDATE `cb_field` SET `displayname` = 'Retry interval' WHERE `displayname` = 'Retry Interval';

UPDATE `cb_field` SET `displayname` = 'Buffering timeout' WHERE `displayname` = 'Buffering Timeout';

-- update case on cb_field.description

UPDATE `cb_field` SET `description` = 'Database user.' WHERE `description` = 'User for connect to database';

UPDATE `cb_field` SET `description` = 'Password of database user.' WHERE `description` = 'Used password for connect to the database.';

UPDATE `cb_field` SET `description` = 'RRD file directory, for example /var/lib/centreon/metrics' WHERE `description` = 'Metrics RRD Directory, for example /var/lib/centreon/metrics';

UPDATE `cb_field` SET `description` = 'RRD file directory, for example /var/lib/centreon/status' WHERE `description` = 'Status RRD Directory, for example /var/lib/centreon/status';

UPDATE `cb_field` SET `description` = 'Interval length in seconds.' WHERE `description` = 'Interval Length in seconds.';

ALTER TABLE `connector` MODIFY `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `centreon`.`traps` CHANGE COLUMN `traps_submit_result_enable` `traps_submit_result_enable` ENUM('0','1') NULL DEFAULT '0';

UPDATE `informations` SET `value` = '2.4.0-RC7' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC6' LIMIT 1;
