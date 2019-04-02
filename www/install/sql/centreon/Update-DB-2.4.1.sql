-- Ldap group by auth ressource
ALTER TABLE contactgroup ADD COLUMN ar_id INT;
DELETE FROM `topology` WHERE `topology_name` = 'All Logs' AND `topology_url` = './include/monitoring/mysql_log/viewLog.php';

-- Add Centreon Connector path
ALTER TABLE nagios_server ADD COLUMN centreonconnector_path VARCHAR(255) DEFAULT NULL AFTER centreonbroker_module_path;

-- Change aggressive_host_checking options
UPDATE cfg_nagios SET `use_aggressive_host_checking` = '0';
