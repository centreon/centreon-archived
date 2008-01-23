
-- Graphs

 DROP TABLE `giv_graphT_componentT_relation` ;
 
-- Menu

UPDATE `topology` SET `topology_name` = 'm_notification' WHERE `topology_page` = 604 LIMIT 1 ;

DELETE FROM topology where topology_parent = '604';
DELETE FROM topology where topology_parent = '605';
DELETE FROM topology where topology_page = '604';
DELETE FROM topology where topology_page = '605';

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_notification', NULL, 6, 604, 40, 1, NULL, NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_escalation', NULL, 604, NULL, NULL, 1, NULL, NULL, '0', '0', '1');

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_escalation', './img/icones/16x16/bookmark.gif', 604, 60401, 10, 1, './include/configuration/configObject/escalation/escalation.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_hostesc', './img/icones/16x16/bookmarks.gif', 604, 60402, 20, 1, './include/configuration/configObject/escalation/escalation.php', '&list=h', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_serviceesc', './img/icones/16x16/bookmarks.gif', 604, 60403, 30, 1, './include/configuration/configObject/escalation/escalation.php', '&list=sv', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_hostgroupesc', './img/icones/16x16/bookmarks.gif', 604, 60404, 40, 1, './include/configuration/configObject/escalation/escalation.php', '&list=hg', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_servicegroupesc', './img/icones/16x16/bookmarks.gif', 604, 60406, 60, 1, './include/configuration/configObject/escalation/escalation.php', '&list=sg', '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_metaserviceesc', './img/icones/16x16/bookmarks.gif', 604, 60405, 50, 1, './include/configuration/configObject/escalation/escalation.php', '&list=ms', '0', '0', '1');

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_dependencies', NULL, 604, NULL, NULL, 2, NULL, NULL, '0', '0', '1');

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'h', './img/icones/16x16/server_network.gif', 604, 60407, 10, 2, './include/configuration/configObject/host_dependency/hostDependency.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'hg', './img/icones/16x16/clients.gif', 604, 60408, 20, 2, './include/configuration/configObject/hostgroup_dependency/hostGroupDependency.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'sv', './img/icones/16x16/element_new_after.gif', 604, 60409, 30, 2, './include/configuration/configObject/service_dependency/serviceDependency.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'sg', './img/icones/16x16/branch_element.gif', 604, 60410, 40, 2, './include/configuration/configObject/servicegroup_dependency/serviceGroupDependency.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'ms', './img/icones/16x16/workstation2.gif', 604, 60411, 50, 2, './include/configuration/configObject/metaservice_dependency/MetaServiceDependency.php', NULL, '0', '0', '1');

INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 604, 'a', './include/common/javascript/changetab.js ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 604, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 604, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 604, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');

INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60401, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60401, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60401, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60402, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60402, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60402, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60403, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60403, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60403, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60404, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60404, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60404, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60405, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60405, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60405, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60406, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60406, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60406, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60409, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60409, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60409, 'c', './include/common/javascript/changetab.js', 'initChangeTab');

-- Nagios 3.X compatibility

ALTER TABLE `cfg_nagios` ADD `precached_object_file` VARCHAR( 255 ) NULL AFTER `object_cache_file` ;
ALTER TABLE `cfg_nagios` ADD `temp_path` VARCHAR( 255 ) NULL AFTER `temp_file` ;
ALTER TABLE `cfg_nagios` ADD `check_result_path` VARCHAR( 255 ) NULL AFTER `status_file`, ADD `max_check_result_file_age` VARCHAR( 255 ) NULL AFTER `check_result_path` ;
ALTER TABLE `cfg_nagios` ADD `translate_passive_host_checks` ENUM( '0', '1' ) NULL AFTER `event_broker_options`, ADD `passive_host_checks_are_soft` VARCHAR( 255 ) ENUM( '0', '1' ) NULL AFTER `translate_passive_host_checks`;
ALTER TABLE `cfg_nagios` ADD `enable_predictive_host_dependency_checks` ENUM( '0', '1' ) NULL , ADD `enable_predictive_service_dependency_checks` ENUM( '0', '1' ) NULL , ADD `cached_host_check_horizon` INT NULL , ADD `cached_service_check_horizon` INT NULL , ADD `use_large_installation_tweaks` ENUM( '0', '1' ) NULL , ADD `free_child_process_memory` ENUM( '0', '1' ) NULL , ADD `child_processes_fork_twice` ENUM( '0', '1' ) NULL , ADD `enable_environment_macros` ENUM( '0', '1' ) NULL ;

ALTER TABLE `cfg_nagios` ADD `additional_freshness_latency` INT NULL ,
ADD `enable_embedded_perl` ENUM( '0', '1' ) NULL ,
ADD `use_embedded_perl_implicitly` ENUM( '0', '1' ) NULL ,
ADD `debug_file` VARCHAR( 255 ) NULL ,
ADD `debug_level` INT NULL ,
ADD `debug_verbosity` INT NULL ,
ADD `max_debug_file_size` INT NULL ;

-- Template de graphs au niveau des commandes 

ALTER TABLE `command` ADD `graph_id` INT NULL ;


-- Command categories

CREATE TABLE `command_categories` (
`cms_category_id` INT NULL AUTO_INCREMENT PRIMARY KEY ,
`category_name` VARCHAR( 255 ) NOT NULL ,
`category_alias` VARCHAR( 255 ) NOT NULL ,
`category_order` INT NOT NULL
) ENGINE = innodb;

CREATE TABLE `command_categories_relation` (
`cmd_cat_id` INT NULL AUTO_INCREMENT PRIMARY KEY ,
`category_id` INT NULL ,
`command_command_id` INT NULL
) ENGINE = innodb;



















