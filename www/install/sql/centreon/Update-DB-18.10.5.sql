-- Remove non existing entries
DELETE FROM topology_JS WHERE id_page IN ('201', '2020301', '2020302', '5010103', '5010105');

-- Update topology of service grid
UPDATE topology SET topology_url_opt = '&o=svcOV_pb' WHERE topology_page = 20204;

-- Update topology of service by host group
UPDATE topology SET topology_url_opt = '&o=svcOVHG_pb' WHERE topology_page = 20209;

-- Update topology of service by service group
UPDATE topology SET topology_url_opt = '&o=svcOVSG_pb' WHERE topology_page = 20212;
