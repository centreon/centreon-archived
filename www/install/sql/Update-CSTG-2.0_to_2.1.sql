ALTER TABLE `session` ADD update_acl ENUM('0', '1');

ALTER TABLE `log` CHANGE `msg_type` `msg_type` INT NULL;

CREATE TABLE IF NOT EXISTS `log_action` (
  `action_log_id` int(11) NOT NULL auto_increment,
  `action_log_date` int(11) NOT NULL,
  `object_type` varchar(255) NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_name` varchar(255) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `log_contact_id` int(11) NOT NULL,
  PRIMARY KEY  (`action_log_id`),
  KEY `log_contact_id` (`log_contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `log_action_modification` (
  `modification_id` int(11) NOT NULL auto_increment,
  `field_name` varchar(255) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  `action_log_id` int(11) NOT NULL,
  PRIMARY KEY  (`modification_id`),
  KEY `action_log_id` (`action_log_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

