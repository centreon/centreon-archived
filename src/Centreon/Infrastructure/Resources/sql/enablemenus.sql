-- Monitoring menus
-- Monitoring > Downtimes > Recurrent downtimes
UPDATE topology SET topology_show = '1' WHERE topology_page = '21003';

-- Configuration menus
-- Configuration > Hosts
UPDATE topology SET topology_show = '1' WHERE topology_page = '601';
UPDATE topology SET topology_show = '1' WHERE topology_parent = '601';
-- Configuration > Services
UPDATE topology SET topology_show = '1' WHERE topology_page = '602';
UPDATE topology SET topology_show = '1' WHERE topology_parent = '602';
-- Configuration > Users > Time Periods
UPDATE topology SET topology_show = '1' WHERE topology_page = '60304';
-- Configuration > Commands
UPDATE topology SET topology_show = '1' WHERE topology_page = '608';
UPDATE topology SET topology_show = '1' WHERE topology_parent = '608';
-- Configuration > Notifications
UPDATE topology SET topology_show = '1' WHERE topology_page = '604';
UPDATE topology SET topology_show = '1' WHERE topology_parent = '604';
-- Configuration > SNMP Traps
UPDATE topology SET topology_show = '1' WHERE topology_page = '617';
UPDATE topology SET topology_show = '1' WHERE topology_parent = '617';
-- Configuration > Plugin Packs
UPDATE topology SET topology_show = '1' WHERE topology_page = '650';
UPDATE topology SET topology_show = '1' WHERE topology_parent = '650';
-- Configuration > Pollers
UPDATE topology SET topology_show = '1' WHERE topology_page = '609';
UPDATE topology SET topology_show = '1' WHERE topology_parent = '609';
-- Configuration > Knownledge Base
UPDATE topology SET topology_show = '1' WHERE topology_page = '610';
UPDATE topology SET topology_show = '1' WHERE topology_parent = '610';

-- Administration menus
-- Administration > Parameters > Main Menu > Monitoring
UPDATE topology SET topology_show = '1' WHERE topology_page = '50111';
-- Administration > Parameters > Media > Images
UPDATE topology SET topology_show = '1' WHERE topology_page = '50102';
-- Administration > Extensions > Subscription
UPDATE topology SET topology_show = '1' WHERE topology_page = '50707';
-- Administration > Parameters > Remote access // Hide the menu on the central
UPDATE topology SET topology_show = '0' WHERE topology_page = '50120';