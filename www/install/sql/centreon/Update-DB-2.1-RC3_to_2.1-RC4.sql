INSERT INTO `topology_JS` (id_page, o, PathName_js, Init) VALUES ('2021203', NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');

ALTER TABLE cfg_nagios ADD debug_level_opt VARCHAR(200) DEFAULT '0' AFTER debug_level;

UPDATE `giv_graphs_template` SET `scaled` = '1';

UPDATE `informations` SET `value` = '2.1-RC4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1-RC3' LIMIT 1;

UPDATE `topology` SET `topology_name` = 'Service Problems' WHERE `topology`.`topology_name` = 'Services Problems';
UPDATE `topology` SET `topology_name` = 'Services by host' WHERE `topology`.`topology_name` = 'Services by hosts';
UPDATE `topology` SET `topology_name` = 'Services by host group' WHERE `topology`.`topology_name` = 'Services by hosts group';
UPDATE `topology` SET `topology_name` = 'Service Groups' WHERE `topology`.`topology_name` = 'Services Groups';
UPDATE `topology` SET `topology_name` = 'Contact Groups' WHERE `topology`.`topology_name` = 'Contacts Groups';
UPDATE `topology` SET `topology_name` = 'Host Groups' WHERE `topology`.`topology_name` = 'Hosts Groups';
UPDATE `topology` SET `topology_name` = 'Bug Tracker' WHERE `topology`.`topology_name` = 'Bugs Tracker';
