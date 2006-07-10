ALTER TABLE `giv_components_template` ADD `ds_invert` ENUM( '0', '1' ) NULL AFTER `ds_transparency` ;



ALTER TABLE `extended_service_information` DROP FOREIGN KEY `extended_service_information_ibfk_1`;
ALTER TABLE `extended_service_information` DROP INDEX `host_index`;
ALTER TABLE `extended_service_information` DROP `host_host_id`;
ALTER TABLE `extended_service_information` ADD `graph_id` INT NULL ;
ALTER TABLE `extended_service_information`  ADD KEY graph_index( graph_id );
ALTER TABLE `extended_service_information`  ADD CONSTRAINT `extended_service_information_ibfk_1` FOREIGN KEY ( `graph_id` ) REFERENCES `giv_graphs_template` ( `graph_id` ) ON DELETE SET NULL ;
ALTER TABLE `giv_graphs_template` ADD `period` INT NULL AFTER `vertical_label` ;
ALTER TABLE `giv_graphs_template` ADD `step` INT NULL AFTER `period` ;
ALTER TABLE `giv_graphs_template` ADD `lower_limit` INT NULL AFTER `height` , ADD `upper_limit` INT NULL AFTER `lower_limit` ;

-- 27/06/2006 ----------

-- Add Authentification type for LDAP compatibility --

ALTER TABLE `contact` ADD `contact_auth_type` VARCHAR( 255 ) NULL AFTER `contact_activate`;
ALTER TABLE `contact` ADD `contact_ldap_dn` varchar(255) NULL AFTER `contact_auth_type`;

ALTER TABLE  `general_opt` ADD `ldap_host` varchar(254) default NULL AFTER `template` ;
ALTER TABLE  `general_opt` ADD `ldap_port` varchar(5) default '389' AFTER `ldap_host`;
ALTER TABLE  `general_opt` ADD `ldap_base_dn` varchar(254) default NULL AFTER `ldap_port`;
ALTER TABLE  `general_opt` ADD `ldap_login_attrib` varchar(254) default 'dn' AFTER `ldap_base_dn`;
ALTER TABLE  `general_opt` ADD `ldap_ssl` enum('0','1') default NULL AFTER `ldap_login_attrib`;
ALTER TABLE  `general_opt` ADD `ldap_auth_enable` enum('0','1') default NULL AFTER `ldap_ssl`;

-- Template added --

ALTER TABLE `general_opt` ADD `template` VARCHAR( 255 ) NULL AFTER `maxViewConfiguration`;
UPDATE `general_opt` SET `template` = 'Basic_light' WHERE `gopt_id` =1 LIMIT 1 ;

-- Trap Module --

ALTER TABLE `general_opt` ADD `snmp_trapd_path_daemon` VARCHAR( 255 ) NULL AFTER `snmp_version` ,
ADD `snmp_trapd_path_conf` VARCHAR( 255 ) NULL AFTER `snmp_trapd_path_daemon` ;


CREATE TABLE `traps_service_relation` (
  `tsr_id` int(11) NOT NULL auto_increment,
  `traps_id` int(11) default NULL,
  `service_id` int(11) default NULL,
  PRIMARY KEY  (`tsr_id`),
  KEY `service_index` (`service_id`),
  KEY `traps_index` (`traps_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;


CREATE TABLE `traps` (
  `traps_id` int(11) NOT NULL auto_increment,
  `traps_name` varchar(255) default NULL,
  `traps_oid` varchar(255) default NULL,
  `traps_handler` varchar(255) default NULL,
  `traps_args` varchar(255) default NULL,
  `traps_comments` varchar(255) default NULL,
  UNIQUE KEY `traps_name` (`traps_name`),
  KEY `traps_id` (`traps_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `traps_service_relation`
  ADD CONSTRAINT `traps_service_relation_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `traps_service_relation_ibfk_2` FOREIGN KEY (`traps_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE;

------------------------
-- 28/06/2006

ALTER TABLE `topology` ADD `topology_group` INT NULL AFTER `topology_order` ;
UPDATE topology SET topology_group = '1';

INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES
('m_traps_command', './img/icones/16x16/funnel_new.gif', 602, 60205, 50, 2, './include/configuration/configObject/traps/traps.php', NULL, NULL, NULL, '1');


-- 29/06/2006

CREATE TABLE `purge_policy` (
  `purge_policy_id` int(11) NOT NULL auto_increment,
  `purge_policy_name` varchar(255) default NULL,
  `purge_policy_alias` varchar(255) default NULL,
  `purge_policy_retention` int(11) default NULL,
  `purge_policy_raw` enum('0','1') default '0',
  `purge_policy_bin` enum('0','1') default '0',
  `purge_policy_metric` enum('0','1') default '0',
  `purge_policy_service` enum('0','1') default '0',
  `purge_policy_host` enum('0','1') default '0',
  `purge_policy_comment` text,
  PRIMARY KEY  (`purge_policy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` )
VALUES (
NULL , 'mod_purgePolicy', './img/icones/16x16/data_down.gif', '606', '60603', '30', '1', './include/configuration/configObject/purgePolicy/purgePolicy.php', NULL , '0', '0', '1'
);

ALTER TABLE `service` ADD `purge_policy_id` INT NULL AFTER `timeperiod_tp_id2` ;
ALTER TABLE `service` ADD INDEX `purge_index` ( `purge_policy_id` ) ;
ALTER TABLE `service`   ADD  FOREIGN KEY (`purge_policy_id`) REFERENCES `purge_policy` (`purge_policy_id`) ON DELETE SET NULL;
ALTER TABLE `host` ADD `purge_policy_id` INT NULL AFTER `timeperiod_tp_id2` ;
ALTER TABLE `host` ADD INDEX `purge_index` ( `purge_policy_id` ) ;
ALTER TABLE `host`
  ADD CONSTRAINT `host_ibfk_1` FOREIGN KEY (`command_command_id`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `host_ibfk_2` FOREIGN KEY (`command_command_id2`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `host_ibfk_3` FOREIGN KEY (`timeperiod_tp_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `host_ibfk_4` FOREIGN KEY (`timeperiod_tp_id2`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `host_ibfk_5` FOREIGN KEY (`purge_policy_id`) REFERENCES `purge_policy` (`purge_policy_id`) ON DELETE SET NULL;


--
-- Structure de la table `log_archive_file_name`
--

CREATE TABLE `log_archive_file_name` (
  `id_log_file` int(11) NOT NULL auto_increment,
  `file_name` varchar(200) default NULL,
  `date` int(11) default NULL,
  PRIMARY KEY  (`id_log_file`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=65 ;

-- --------------------------------------------------------

--
-- Structure de la table `log_archive_host`
--

CREATE TABLE `log_archive_host` (
  `log_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) default NULL,
  `UPTimeScheduled` int(11) default NULL,
  `UPTimeUnScheduled` int(11) default NULL,
  `DOWNTimeScheduled` int(11) default NULL,
  `DOWNTimeUnScheduled` int(11) default NULL,
  `UNREACHABLETimeScheduled` int(11) default NULL,
  `UNREACHABLETimeUnScheduled` int(11) default NULL,
  `UNDETERMINATETimeScheduled` int(11) default NULL,
  `UNDETERMINATETimeUnScheduled` int(11) default NULL,
  `date_end` int(11) default NULL,
  `date_start` int(11) default NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=42398 ;

-- --------------------------------------------------------

--
-- Structure de la table `log_archive_service`
--

CREATE TABLE `log_archive_service` (
  `log_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) NOT NULL default '0',
  `service_id` int(11) NOT NULL default '0',
  `OKTimeScheduled` int(11) NOT NULL default '0',
  `OKTimeUnScheduled` int(11) NOT NULL default '0',
  `WARNINGTimeScheduled` int(11) NOT NULL default '0',
  `WARNINGTimeUnScheduled` int(11) NOT NULL default '0',
  `UNKNOWNTimeScheduled` int(11) NOT NULL default '0',
  `UNKNOWNTimeUnScheduled` int(11) NOT NULL default '0',
  `CRITICALTimeScheduled` int(11) NOT NULL default '0',
  `CRITICALTimeUnScheduled` int(11) NOT NULL default '0',
  `UNDETERMINATETimeScheduled` int(11) NOT NULL default '0',
  `UNDETERMINATETimeUnScheduled` int(11) NOT NULL default '0',
  `date_start` int(11) default NULL,
  `date_end` int(11) default NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=19692 ;

-- --------------------------------------------------------
--05 07 06

UPDATE topology SET topology_name = 'view_redirect_graph' WHERE topology_page = '40207';



-- 07 07 06

ALTER TABLE `cfg_nagios` CHANGE `sleep_time` `sleep_time` VARCHAR(10) NULL DEFAULT NULL;
INSERT INTO `topology` (`topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES ('m_host_graph','./img/icones/16x16/column-chart.gif',402,40208,32,1, './include/views/graphs/hostGraphs/hostGraphs.php',NULL,'0','0','1');


-- --------------------------------------------------------