--new updated field of pollers-
ALTER TABLE `nagios_server` ADD COLUMN `updated` enum('0','1') NOT NULL DEFAULT '0';

-- Update topology of service grid / by host group / by service group
UPDATE topology SET topology_url_opt = '&o=svcOV_pb' WHERE topology_page = 20204;
UPDATE topology SET topology_url_opt = '&o=svcOVHG_pb' WHERE topology_page = 20209;
UPDATE topology SET topology_url_opt = '&o=svcOVSG_pb' WHERE topology_page = 20212;
