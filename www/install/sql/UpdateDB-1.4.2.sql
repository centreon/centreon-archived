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

-- 20/08/2007
ALTER TABLE `traps` DROP INDEX `traps_name`;
ALTER TABLE `traps` ADD UNIQUE (`traps_oid`);

-- 24/08/2007
ALTER TABLE `general_opt` ADD `snmptt_unknowntrap_log_file` VARCHAR( 255 ) NULL AFTER `snmpttconvertmib_path_bin` ;


-- 09/10/2007
-- NDO view
INSERT INTO `centreon`.`topology_JS` (`id_t_js` ,`id_page` ,`o` ,`PathName_js` ,`Init`) VALUES (NULL , '20212', NULL , NULL , 'initM');
INSERT INTO `centreon`.`topology_JS` (`id_t_js` ,`id_page` ,`o` ,`PathName_js` ,`Init`) VALUES (NULL , '2021201', NULL , NULL , 'initM');
INSERT INTO `centreon`.`topology_JS` (`id_t_js` ,`id_page` ,`o` ,`PathName_js` ,`Init`) VALUES (NULL , '2021202', NULL , NULL , 'initM');
INSERT INTO `centreon`.`topology_JS` (`id_t_js` ,`id_page` ,`o` ,`PathName_js` ,`Init`) VALUES (NULL , '2021203', NULL , NULL , 'initM');
INSERT INTO `centreon`.`topology_JS` (`id_t_js` ,`id_page` ,`o` ,`PathName_js` ,`Init`) VALUES (NULL , '20213', NULL , NULL , 'initM');
INSERT INTO `centreon`.`topology_JS` (`id_t_js` ,`id_page` ,`o` ,`PathName_js` ,`Init`) VALUES (NULL , '2021301', NULL , NULL , 'initM');
INSERT INTO `centreon`.`topology_JS` (`id_t_js` ,`id_page` ,`o` ,`PathName_js` ,`Init`) VALUES (NULL , '2021302', NULL , NULL , 'initM');
INSERT INTO `centreon`.`topology_JS` (`id_t_js` ,`id_page` ,`o` ,`PathName_js` ,`Init`) VALUES (NULL , '2021303', NULL , NULL , 'initM');

-- 15/10/2007
--
-- Structure de la table `escalation_servicegroup_relation`
--

CREATE TABLE `escalation_servicegroup_relation` (
  `esgr_id` int(11) NOT NULL auto_increment,
  `escalation_esc_id` int(11) default NULL,
  `servicegroup_sg_id` int(11) default NULL,
  PRIMARY KEY  (`esgr_id`),
  KEY `escalation_index` (`escalation_esc_id`),
  KEY `sg_index` (`servicegroup_sg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contraintes pour la table `escalation_servicegroup_relation`
--
ALTER TABLE `escalation_servicegroup_relation`
  ADD CONSTRAINT `escalation_servicegroup_relation_ibfk_1` FOREIGN KEY (`escalation_esc_id`) REFERENCES `escalation` (`esc_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `escalation_servicegroup_relation_ibfk_2` FOREIGN KEY (`servicegroup_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE;

INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES ('', 'm_servicegroupesc', './img/icones/16x16/bookmarks.gif', 604, 60406, 60, 1, './include/configuration/configObject/escalation/escalation.php', '&list=sg', '0', '0', '1');

INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60406, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60406, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js` , `id_page` , `o` , `PathName_js` , `Init`) VALUES ('', 60406, 'a', './include/common/javascript/changetab.js', 'initChangeTab');