-- Ldap group by auth ressource
ALTER TABLE contactgroup ADD COLUMN ar_id INT;
DELETE FROM `topology` WHERE `topology_page` = 203 AND `topology_url` = './include/monitoring/mysql_log/viewLog.php';