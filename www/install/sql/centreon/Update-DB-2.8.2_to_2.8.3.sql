-- Change version of Centreon

DELETE FROM topology WHERE topology_page = 60216;
UPDATE topology SET topology_page = 21003 WHERE topology_page = 60106;
UPDATE topology SET topology_parent = 210 WHERE topology_page = 21003;
UPDATE topology SET topology_url ='./include/monitoring/recurrentDowntime/downtime.php' WHERE topology_page = 21003;
