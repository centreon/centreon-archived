INSERT INTO `topology_JS` (id_page, o, PathName_js, Init) VALUES ('2021203', NULL, './include/common/javascript/ajaxMonitoring.js', 'initM');

ALTER TABLE cfg_nagios ADD debug_level_opt VARCHAR(200) DEFAULT '0' AFTER debug_level;

UPDATE `informations` SET `value` = '2.1-RC4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1-RC3' LIMIT 1;