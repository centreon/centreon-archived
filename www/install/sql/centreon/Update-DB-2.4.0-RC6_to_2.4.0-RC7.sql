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

UPDATE `informations` SET `value` = '2.4.0-RC7' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC6' LIMIT 1;