
UPDATE `topology` SET `topology_order` = '20' WHERE `topology_page` = '20201' AND `topology_url_opt` = '&o=svc' LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '10' WHERE `topology_page` = '20201' AND `topology_url_opt` = '&o=svcpb' LIMIT 1 ;

DELETE FROM `topology` WHERE `topology_page` = 3 OR  `topology_page` = 307 OR  `topology_page` = 30701 OR  `topology_page` = 30702 OR `topology_parent` = 3;

INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES 
( 'm_reporting', NULL, NULL, 3, 30, 1, './include/reporting/dashboard/viewHostLog.php', NULL, '0', '0', '1'),
( 'm_dashboard', NULL, 3, 307, 3, 1, './include/reporting/dashboard/viewHostLog.php', NULL, '0', '0', '1'),
( 'm_dashboardHost', './img/icones/16x16/outbox.gif', 307, 30701, 10, 1, './include/reporting/dashboard/viewHostLog.php', NULL, '0', '0', '1'),
( 'm_dashboardService', NULL, 307, 30702, 20, 1, './include/reporting/dashboard/viewServicesLog.php', NULL, '0', '0', '0');

DELETE FROM `log_archive_file_name`;
DELETE FROM `log_archive_host`;
DELETE FROM `log_archive_service`;

--
-- Structure de la table `oreon_informations`
--

CREATE TABLE `oreon_informations` (
`key` VARCHAR( 25 ) NULL ,
`value` VARCHAR( 25 ) NULL
) ENGINE = innodb;

INSERT INTO `oreon_informations` ( `key` , `value` ) VALUES ('version', '1.3.2-b2');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60202, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'a', './include/common/javascript/changetab.js', 'initChangeTab');

UPDATE `topology` SET `topology_name` = 'm_host',
`topology_icone` = NULL ,
`topology_parent` = '2',
`topology_page` = '201',
`topology_order` = '20',
`topology_group` = '1',
`topology_url` = './include/monitoring/status/monitoringHost.php',
`topology_url_opt` = '&o=h',
`topology_popup` = NULL ,
`topology_modules` = NULL ,
`topology_show` = '1' WHERE `topology_page` =201 LIMIT 1 ;

UPDATE `topology` SET `topology_name` = 'm_service',
`topology_icone` = NULL ,
`topology_parent` = '2',
`topology_page` = '202',
`topology_order` = '10',
`topology_group` = '1',
`topology_url` = './include/monitoring/status/monitoringService.php',
`topology_url_opt` = '&o=svcpb',
`topology_popup` = '0',
`topology_modules` = '0',
`topology_show` = '1' WHERE `topology_page` =202 LIMIT 1 ;

DELETE FROM `topology` WHERE `topology_url` = './include/views/graphs/myViews/myViews.php' LIMIT 1;

ALTER TABLE `general_opt` ADD `problem_sort_type` VARCHAR( 25 ) NULL AFTER `template` ;
ALTER TABLE `general_opt` ADD `problem_sort_order` VARCHAR( 15 ) NULL AFTER `problem_sort_type` ;

-- had host check arg

ALTER TABLE `host` ADD `command_command_id_arg1` TEXT NULL AFTER `command_command_id` ;

-- 

UPDATE `topology` SET `topology_order` = '20' WHERE `topology_page` = '40201' LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '10' WHERE `topology_page` = '40202' LIMIT 1 ;
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_general', './img/icones/16x16/lock_new.gif', 502, 50201, 10, 1, './include/options/LCA/define/lca.php', NULL, '0', '0', '1');

-- beta 3

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', '6', 'a', './include/common/javascript/changetab.js', ' initChangeTab');

