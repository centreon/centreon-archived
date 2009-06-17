
DELETE FROM topology WHERE topology_parent = 203 AND topology_name = 'Advanced Logs';
DELETE FROM topology WHERE topology_parent = 203 AND topology_page = '20311';
DELETE FROM topology WHERE topology_parent = 203 AND topology_page = '20312';
DELETE FROM topology WHERE topology_parent = 203 AND topology_page = '20313';
DELETE FROM topology WHERE topology_parent = 203 AND topology_page = '20314';


UPDATE `informations` SET `value` = '2.1-RC5' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1-RC4' LIMIT 1;
