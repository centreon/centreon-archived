ALTER TABLE `contact` ADD `contact_location` INT NULL ;
ALTER TABLE `host` ADD `host_location` INT NULL AFTER `host_snmp_version` ;
UPDATE `contact` SET `contact_location` = '0';
UPDATE `host` SET `host_location` = '0';