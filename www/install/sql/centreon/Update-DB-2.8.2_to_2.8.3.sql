-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.3' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.2' LIMIT 1;

-- Move recurrent downtimes configuration to monitoring menu
DELETE FROM topology WHERE topology_page = 60216;
UPDATE topology SET topology_page = 21003 WHERE topology_page = 60106;
UPDATE topology SET topology_parent = 210 WHERE topology_page = 21003;
UPDATE topology SET topology_url = './include/monitoring/recurrentDowntime/downtime.php',
topology_name = 'Recurrent downtimes',
topology_order = 20
WHERE topology_page = 21003;

-- broker option
DELETE FROM `options` WHERE `key` = 'broker';
INSERT INTO `options` (`key`, `value`) VALUES ('broker', 'broker');

-- Remove relations between contact templates and contactgroups
DELETE FROM contactgroup_contact_relation
WHERE contact_contact_id IN (
    SELECT contact_id FROM contact WHERE contact_register = '0'
);
