-- Ldap group by auth ressource
ALTER TABLE contactgroup ADD COLUMN ar_id INT;
DELETE FROM `topology` WHERE `topology_page` = 203 AND `topology_url` = './include/monitoring/mysql_log/viewLog.php';

UPDATE `informations` SET `value` = '2.4.1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0' LIMIT 1;
