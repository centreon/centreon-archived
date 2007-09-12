-- --------------------------------------------------------

--
-- Structure de la table `service_categories`
--

CREATE TABLE `service_categories` (
`sc_id` INT NULL AUTO_INCREMENT PRIMARY KEY ,
`sc_name` VARCHAR( 255 ) NULL ,
`sc_description` VARCHAR( 255 ) NULL ,
`sc_activate` ENUM( '0', '1') NULL
) ENGINE = innodb COMMENT = 'Services Cat�gories For best Reporting';


INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_categories', './img/icones/16x16/certificate.gif', 602, 60209, 90, 1, './include/configuration/configObject/service_categories/serviceCategories.php', NULL, '0', '0', '1');

INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', '60703', 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', '60703', 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', '60703', 'c', './include/common/javascript/changetab.js', 'initChangeTab');

--
-- Reporting
--

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick`) VALUES ('', 'm_dashboardHostGroup', './img/icones/16x16/text_rich_colored.gif', 307, 30703, 20, 50, './include/reporting/dashboard/viewHostGroupLog.php', NULL, '0', '0', '1', NULL , NULL , NULL);
INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` , `topology_style_class` , `topology_style_id` , `topology_OnClick`) VALUES ('', 'm_dashboardServiceGroup', './img/icones/16x16/text_rich_colored.gif', 307, 30704, 20, 50, './include/reporting/dashboard/viewServicesGroupLog.php', NULL, '0', '0', '1', NULL , NULL , NULL);

INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 30703, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', 'initTimeline');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 30704, NULL, './include/common/javascript/Timeline/src/main/webapp/api/timeline-api.js', 'initTimeline');
