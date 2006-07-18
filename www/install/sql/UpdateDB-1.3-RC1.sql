INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` )
VALUES (
NULL , 'graph', NULL , '402', '40207', '60', './include/views/graphs/graphSummary/graphSummary.php', NULL , '0', '0', '0'
);

INSERT INTO `topology` ( `topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show` )
VALUES (
'', 'm_idUpdate', NULL, 7, 701, 40, './modules/inventory/inventory.php', '&o=u', NULL, NULL, '1'
);


ALTER TABLE `general_opt` ADD `maxViewMonitoring` INT NOT NULL DEFAULT '50',
ADD `maxViewConfiguration` INT NOT NULL DEFAULT '20';


ALTER TABLE `giv_components_template` ADD `ds_invert` INT NULL DEFAULT 'NULL' AFTER `ds_transparency` ;

ALTER TABLE `command` ADD `command_example` VARCHAR( 254 ) NULL AFTER `command_line` ;

ALTER TABLE `cfg_nagios` ADD `p1_file` VARCHAR( 255 ) NULL AFTER `status_file` ;

-- -------------------------------------------------------

-- 
-- Structure de la table `downtime`
-- 

CREATE TABLE `downtime` (
  `downtime_id` int(11) NOT NULL auto_increment,
  `host_id` int(11) NOT NULL default '0',
  `service_id` int(11) default NULL,
  `entry_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `author` varchar(254) NOT NULL default '',
  `comment` varchar(254) NOT NULL default '',
  `start_time` varchar(15) NOT NULL default '',
  `end_time` varchar(15) NOT NULL default '',
  `fixed` enum('0','1') NOT NULL default '0',
  `duration` int(11) NOT NULL default '0',
  `deleted` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`downtime_id`)
) TYPE=InnoDB;

UPDATE `topology` SET `topology_icone` = NULL ,
`topology_parent` = NULL ,
`topology_url_opt` = NULL ,
`topology_show` = '0' WHERE `topology_page` = '3' LIMIT 1 ;

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


