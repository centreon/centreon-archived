
DELETE FROM topology WHERE topology_name = 'm_service' AND topology_page = '2031202';
DELETE FROM topology WHERE topology_name = 'm_host' AND topology_page = '2031301';
DELETE FROM topology WHERE topology_name = 'm_service' AND topology_page = '2031302';
DELETE FROM topology WHERE topology_name = 'modOSM_m_osm' AND topology_page = '41099';
DELETE FROM topology WHERE topology_name = 'hidden redirect' AND topology_page = '40207';

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60703, 'a', './include/common/javascript/changetab.js', 'initChangeTab');

UPDATE `informations` SET `value` = '2.1.4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1.3' LIMIT 1;