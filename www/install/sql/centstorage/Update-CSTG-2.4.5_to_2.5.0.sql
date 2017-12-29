-- /!\ WARNING /!\
-- This file must be renamed once we know the exact source and target versions.
-- /!\ WARNING /!\

CREATE TABLE `log_traps_args` (
  `fk_log_traps` int(11) NOT NULL,
  `arg_number` int(11) DEFAULT NULL,
  `arg_oid` varchar(255) DEFAULT NULL,
  `arg_value` varchar(255) DEFAULT NULL,
  `trap_time` int(11) DEFAULT NULL,
  KEY `fk_log_traps` (`fk_log_traps`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `log_traps` (
  `trap_id` int(11) NOT NULL AUTO_INCREMENT,
  `trap_time` int(11) DEFAULT NULL,
  `timeout` enum('0','1') DEFAULT '0' DEFAULT NULL,
  `host_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `agent_host_name` varchar(255) DEFAULT NULL,
  `agent_ip_address` varchar(255) DEFAULT NULL,
  `trap_oid` varchar(512) DEFAULT NULL,
  `trap_name` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `severity_id` int(11) DEFAULT NULL,
  `severity_name` varchar(255) DEFAULT NULL,
  `output_message` varchar(2048) DEFAULT NULL,
  KEY `trap_id` (`trap_id`),
  KEY `trap_time` (`trap_time`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

DROP TABLE `log_snmptt`;

ALTER TABLE instances ADD COLUMN `deleted` boolean NOT NULL default false AFTER `version`;

ALTER TABLE metrics ADD COLUMN `current_value` float DEFAULT NULL AFTER `unit_name`;

ALTER TABLE hostgroups ADD COLUMN `enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `notes_url`;
ALTER TABLE servicegroups ADD COLUMN `enabled` tinyint(1) NOT NULL DEFAULT '1' AFTER `notes_url`;

-- Ticket #4863
ALTER TABLE metrics MODIFY metric_name VARCHAR (255) COLLATE utf8_bin;

ALTER TABLE `instance` ADD `last_ctime` int(11) DEFAULT 0 AFTER `log_md5`;

