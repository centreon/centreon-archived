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
(11, 41, 1, 3),
(25, 41, 1, 6);


ALTER TABLE `cfg_centreonbroker` ADD COLUMN `event_queue_max_size` INT (11) DEFAULT 50000 AFTER `ns_nagios_server`;

UPDATE `informations` SET `value` = '2.4.0-RC7' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC6' LIMIT 1;