-- Clean source code and remove potential problems with ACL
UPDATE topology SET topology_url = NULL WHERE topology_page = 502;
