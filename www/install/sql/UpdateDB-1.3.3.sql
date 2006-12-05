-- 04/12/2006 --- 

UPDATE `oreon_informations` SET `value` = '1.3.3' WHERE CONVERT( `key` USING utf8 ) = 'version' AND CONVERT( `value` USING utf8 ) = '1.3.2' LIMIT 1 ;

--
-- Contenu de la table `topology_JS`
--

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 50201, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 50201, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 50201, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 3, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 307, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 30701, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 30701, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 30701, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 307, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 3, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', '');


-- 

UPDATE `topology` SET `topology_order` = '1'  WHERE `topology_page` = '40203' LIMIT 1 ;

-- 

ALTER TABLE `host` ADD `command_command_id_arg2` TEXT NULL AFTER `command_command_id2` ;