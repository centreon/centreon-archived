CREATE TABLE IF NOT EXISTS `centreon_acl` (
  `id` int(11) NOT NULL auto_increment,
  `host_id` int(11) default NULL,
  `host_name` varchar(255) default NULL,
  `service_id` int(11) default NULL,
  `service_description` varchar(255) default NULL,
  `group_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `group_id_by_name` (`host_name`(70),`service_description`(120),`group_id`),
  KEY `group_id_by_id` (`host_id`,`service_id`,`group_id`),
  KEY `group_id_for_host` (`host_name`,`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `metrics` ADD `data_source_type` ENUM( '0', '1', '2', '3' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' AFTER `metric_name` ;
