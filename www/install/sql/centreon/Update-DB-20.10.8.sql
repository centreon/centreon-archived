-- Add missing link between _Module_Meta and localhost poller
INSERT INTO ns_host_relation(`nagios_server_id`, `host_host_id`)
VALUES(
    (SELECT id FROM nagios_server WHERE localhost = '1'),
    (SELECT host_id FROM host WHERE host_name = '_Module_Meta')
)
ON DUPLICATE KEY UPDATE nagios_server_id = (SELECT id FROM nagios_server WHERE localhost = '1');

-- Delete obsolete topologies
DELETE FROM `topology` WHERE `topology_page` IN (6090901, 6090902);
