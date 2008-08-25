
ALTER TABLE `index_data` ADD `hidden` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `special` , ADD `locked` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `hidden` ;

CREATE TABLE IF NOT EXISTS `log_archive_last_status` (
  `id` int(11) NOT NULL,
  `host_name` varchar(255) default NULL,
  `service_description` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `ctime` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `log_archive_service` ADD `UNDETERMINEDTimeScheduled` INT( 11 ) NULL ;
ALTER TABLE `log_archive_host` ADD `UNDETERMINEDTimeScheduled` INT( 11 ) NULL ;