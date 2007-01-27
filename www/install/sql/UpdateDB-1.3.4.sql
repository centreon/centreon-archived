-- 03/01/2007 --- 

ALTER TABLE `topology` ADD `topology_style_class` VARCHAR( 255 ) NULL ;
ALTER TABLE `topology` ADD `topology_style_id` VARCHAR( 255 ) NULL ;
ALTER TABLE `topology` ADD `topology_OnClick` VARCHAR( 255 ) NULL ;

-- PO here

-- 19/01/2007
DELETE FROM topology WHERE topology_page = '60601';
DELETE FROM topology WHERE topology_page = '60602';
DELETE FROM topology WHERE topology_page = '60603';
DELETE FROM topology WHERE topology_page = '606';

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_host_template_model', './img/icones/16x16/server_client_ext.gif', 601, 60103, 30, 1, './include/configuration/configObject/host_template_model/hostTemplateModel.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_service_template_model', './img/icones/16x16/element_template.gif', 602, 60206, 50, 1, './include/configuration/configObject/service_template_model/serviceTemplateModel.php', NULL, '0', '0', '1');
UPDATE topology SET topology_group = 2 WHERE topology_page = 60705;
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'mod_purgePolicy', './img/icones/16x16/data_down.gif', '607', '60708', '60', '2', './include/configuration/configObject/purgePolicy/purgePolicy.php', NULL , '0', '0', '1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('', 'm_perfparse', NULL, 607, NULL, NULL, 2, NULL, NULL, '0', '0', '1');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'a', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'c', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'w', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'a', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'c', './include/common/javascript/changetab.js       ', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'w', './include/common/javascript/changetab.js       ', 'initChangeTab');

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60206, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'a', './include/common/javascript/autoSelectCommandExample.js', NULL);
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'c', './include/common/javascript/autoSelectCommandExample.js', NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60102, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'c', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60101, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60102, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 60103, 'a', './include/common/javascript/autocomplete-3-2.js', 'initAutoComplete(''Form'',''city_name'',''sub'')');

-- 20/01/2007
UPDATE `oreon_informations` SET `value` = '1.3.3' WHERE CONVERT( `key` USING utf8 ) = 'version' AND CONVERT( `value` USING utf8 ) = '1.3.2' LIMIT 1 ;
UPDATE `oreon_informations` SET `value` = '1.3.4' WHERE CONVERT( `key` USING utf8 ) = 'version' AND CONVERT( `value` USING utf8 ) = '1.3.3' LIMIT 1 ;
ALTER TABLE `escalation` ADD `esc_alias` VARCHAR( 255 ) NULL AFTER `esc_name` ;
ALTER TABLE `lca_define` ADD `lca_alias` VARCHAR( 255 ) NULL AFTER `lca_name` ;

-- 22/01/2007

--
-- Quand on rajoute, on rajoute a la Fin Merci !
--

CREATE TABLE `modules_informations` (
  `id` int(11) NOT NULL auto_increment,
  `internal_name` varchar(254) default NULL,
  `name` varchar(254) default NULL,
  `version` int(11) default NULL,
  `is_installed` enum('0','1') default NULL,
  `is_removeable` enum('0','1') default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 27/01/2006

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'views_title1', NULL, 402, NULL, NULL, 1, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'views_title2', NULL, 402, NULL, NULL, 2, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_host_graph', './img/icones/16x16/dot-chart.gif', 402, 40210, 10, 2, './include/views/graphs/graphODS/summaryODS.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'm_views_graphShow', './img/icones/16x16/column-chart.gif', 402, 40211, 20, 2, './include/views/graphs/graphODS/graphODS.php', NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'views_title3', NULL, 402, NULL, NULL, 3, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES ('', 'views_title4', NULL, 402, NULL, NULL, 4, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);

INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 4, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 402, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40201, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40202, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40203, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40208, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40210, NULL, './include/common/javascript/datePicker.js', '');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES ('', 40211, NULL, './include/common/javascript/datePicker.js', '');

UPDATE topology SET topology_group = '4' WHERE topology_page = '40204';
UPDATE topology SET topology_group = '4' WHERE topology_page = '40205';
UPDATE topology SET topology_group = '3' WHERE topology_page = '40203';
UPDATE topology SET topology_group = '3' WHERE topology_page = '40208';