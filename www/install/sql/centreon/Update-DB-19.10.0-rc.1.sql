-- Create rs_poller_relation for the additional relationship between poller and remote servers
CREATE TABLE IF NOT EXISTS `rs_poller_relation` (
  `remote_server_id` int(11) NOT NULL,
  `poller_server_id` int(11) NOT NULL,
  KEY `remote_server_id` (`remote_server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relation Table For centreon pollers and remote servers';

--new inheritance mode
INSERT INTO `options` (`key`, `value`) VALUES ('inheritance_mode', '1');

--new updated field of pollers-
ALTER TABLE `nagios_server` ADD COLUMN `updated` enum('0','1') NOT NULL DEFAULT '0';