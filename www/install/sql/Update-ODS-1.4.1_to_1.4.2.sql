
ALTER TABLE `index_data` CHANGE `must_be_rebuild` `must_be_rebuild` ENUM( '0', '1', '2' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '0' 
ALTER TABLE `index_data` ADD `locked` ENUM('0' , '1') NULL DEFAULT '0' AFTER `trashed` ;

ALTER TABLE `config` ADD `DailyReporting` ENUM( '0', '1' ) NULL DEFAULT '1',
ADD `HourlyReporting` ENUM( '0', '1' ) NULL DEFAULT '1',
ADD `MonthlyReporting` ENUM( '0', '1' ) NULL DEFAULT '1',
ADD `Reporting_enable` ENUM( '0', '1' ) NULL DEFAULT '1',
ADD `Force_Reporting_Rebuild` ENUM( '0', '1' ) NULL DEFAULT '0';


ALTER TABLE `metrics` ADD `hidden` ENUM( '0', '1' ) NULL DEFAULT '0';
ALTER TABLE `metrics` ADD `min` INT(11) NULL DEFAULT '0' AFTER `crit`;
ALTER TABLE `metrics` ADD `max` INT(11) NULL DEFAULT '0' AFTER `min`;
ALTER TABLE `metrics` ADD `locked` ENUM( '0', '1' ) NULL DEFAULT '0' AFTER `hidden` ;

CREATE TABLE `log_snmptt` (
  `traps_id` int(11) NOT NULL auto_increment,
  `traps_oid` varchar(255) default NULL,
  `traps_ip` varchar(255) default NULL,
  `traps_community` varchar(255) default NULL,
  `traps_infos` varchar(255) default NULL,
  PRIMARY KEY  (`traps_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
