-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.1' LIMIT 1;

ALTER TABLE escalation ADD COLUMN host_inheritance_to_services tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE escalation ADD COLUMN hostgroup_inheritance_to_services tinyint(1) DEFAULT 0 NOT NULL;
