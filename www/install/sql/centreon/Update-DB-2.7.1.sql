-- Update topology in monitoring host and service
UPDATE topology SET topology_url_opt = NULL WHERE topology_page in (20202, 20201);

-- Update topology of service grid
UPDATE topology SET topology_url_opt = '&o=svcOV_pb' WHERE topology_page = 20204;

-- Update topology of service by host group
UPDATE topology SET topology_url_opt = '&o=svcOVHG_pb' WHERE topology_page = 20209;

-- Update topology of service by service group
UPDATE topology SET topology_url_opt = '&o=svcOVSG_pb' WHERE topology_page = 20212;
