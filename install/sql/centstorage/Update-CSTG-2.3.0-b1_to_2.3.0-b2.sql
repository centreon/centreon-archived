ALTER TABLE  `log_archive_host` ADD  `MaintenanceTime` INT NULL DEFAULT  '0' AFTER  `UNDETERMINEDTimeScheduled` ;
ALTER TABLE  `log_archive_service` ADD  `MaintenanceTime` INT NULL DEFAULT  '0' AFTER  `UNDETERMINEDTimeScheduled` ;

ALTER TABLE `metrics` ADD `data_source_type` ENUM( '0', '1', '2', '3' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' AFTER `metric_name` ;

--
-- Table structure for table `hoststateevents`
--

CREATE TABLE IF NOT EXISTS `hoststateevents` (
  `hoststateevents_id` int(11) NOT NULL auto_increment,
  `end_time` int(11) default NULL,
  `host_id` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `state` tinyint(11) NOT NULL,
  `last_update` tinyint(4) NOT NULL default '0',
  `in_downtime` tinyint(4) NOT NULL,
  PRIMARY KEY  (`hoststateevents_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `servicestateevents`
--

CREATE TABLE IF NOT EXISTS `servicestateevents` (
  `servicestateevents_id` int(11) NOT NULL auto_increment,
  `end_time` int(11) default NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11) default NULL,
  `start_time` int(11) NOT NULL,
  `state` tinyint(11) NOT NULL,
  `last_update` tinyint(4) NOT NULL default '0',
  `in_downtime` tinyint(4) NOT NULL,
  PRIMARY KEY  (`servicestateevents_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
