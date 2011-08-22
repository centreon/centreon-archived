ALTER TABLE `host` CHANGE `host_register` `host_register` ENUM('0','1','2','3') NOT NULL DEFAULT '0';
ALTER TABLE `service` CHANGE `service_register` `service_register` ENUM('0','1','2','3') NOT NULL DEFAULT '0';
ALTER TABLE nagios_server ADD COLUMN centreonbroker_module_path VARCHAR(255) DEFAULT NULL AFTER centreonbroker_cfg_path;

--
-- Structure de la table `cb_field`
--

CREATE TABLE IF NOT EXISTS `cb_field` (
  `cb_field_id` int(11) NOT NULL auto_increment,
  `fieldname` varchar(100) NOT NULL,
  `displayname` varchar(100) NOT NULL,
  `description` varchar(255) default NULL,
  `fieldtype` varchar(255) NOT NULL default 'text',
  `external` varchar(255) default NULL,
  PRIMARY KEY  (`cb_field_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_list`
--

CREATE TABLE IF NOT EXISTS `cb_list` (
  `cb_list_id` int(11) NOT NULL,
  `cb_field_id` int(11) NOT NULL,
  `default_value` varchar(255) default NULL,
  PRIMARY KEY  (`cb_list_id`,`cb_field_id`),
  UNIQUE KEY `cb_field_idx_01` (`cb_field_id`),
  KEY `fk_cb_list_1` (`cb_field_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_list_values`
--

CREATE TABLE IF NOT EXISTS `cb_list_values` (
  `cb_list_id` int(11) NOT NULL,
  `value_name` varchar(255) NOT NULL,
  `value_value` varchar(255) NOT NULL,
  PRIMARY KEY  (`cb_list_id`,`value_name`),
  KEY `fk_cb_list_values_1` (`cb_list_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_module`
--

CREATE TABLE IF NOT EXISTS `cb_module` (
  `cb_module_id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `libname` varchar(50) default NULL,
  `loading_pos` int(11) default NULL,
  `is_bundle` int(1) NOT NULL default '0',
  `is_activated` int(1) NOT NULL default '0',
  PRIMARY KEY  (`cb_module_id`),
  UNIQUE KEY `cb_module_idx01` (`name`),
  UNIQUE KEY `cb_module_idx02` (`libname`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_module_relation`
--

CREATE TABLE IF NOT EXISTS `cb_module_relation` (
  `cb_module_id` int(11) NOT NULL,
  `module_depend_id` int(11) NOT NULL,
  `inherit_config` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cb_module_id`,`module_depend_id`),
  KEY `fk_cb_module_relation_1` (`cb_module_id`),
  KEY `fk_cb_module_relation_2` (`module_depend_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_tag`
--

CREATE TABLE IF NOT EXISTS `cb_tag` (
  `cb_tag_id` int(11) NOT NULL auto_increment,
  `tagname` varchar(50) NOT NULL,
  PRIMARY KEY  (`cb_tag_id`),
  UNIQUE KEY `cb_tag_ix01` (`tagname`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_tag_type_relation`
--

CREATE TABLE IF NOT EXISTS `cb_tag_type_relation` (
  `cb_tag_id` int(11) NOT NULL,
  `cb_type_id` int(11) NOT NULL,
  PRIMARY KEY  (`cb_tag_id`,`cb_type_id`),
  KEY `fk_cb_tag_type_relation_1` (`cb_tag_id`),
  KEY `fk_cb_tag_type_relation_2` (`cb_type_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_type`
--

CREATE TABLE IF NOT EXISTS `cb_type` (
  `cb_type_id` int(11) NOT NULL auto_increment,
  `type_name` varchar(50) NOT NULL,
  `type_shortname` varchar(50) NOT NULL,
  `cb_module_id` int(11) NOT NULL,
  PRIMARY KEY  (`cb_type_id`),
  KEY `fk_cb_type_1` (`cb_module_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Structure de la table `cb_type_field_relation`
--

CREATE TABLE IF NOT EXISTS `cb_type_field_relation` (
  `cb_type_id` int(11) NOT NULL,
  `cb_field_id` int(11) NOT NULL,
  `is_required` int(11) NOT NULL default '0',
  `order_display` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cb_type_id`,`cb_field_id`),
  KEY `fk_cb_type_field_relation_1` (`cb_type_id`),
  KEY `fk_cb_type_field_relation_2` (`cb_field_id`)
) ENGINE=InnoDB;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `cb_list`
--
ALTER TABLE `cb_list`
  ADD CONSTRAINT `fk_cb_list_1` FOREIGN KEY (`cb_field_id`) REFERENCES `cb_field` (`cb_field_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_list_values`
--
ALTER TABLE `cb_list_values`
  ADD CONSTRAINT `fk_cb_list_values_1` FOREIGN KEY (`cb_list_id`) REFERENCES `cb_list` (`cb_list_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_module_relation`
--
ALTER TABLE `cb_module_relation`
  ADD CONSTRAINT `fk_cb_module_relation_1` FOREIGN KEY (`cb_module_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cb_module_relation_2` FOREIGN KEY (`module_depend_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_tag_type_relation`
--
ALTER TABLE `cb_tag_type_relation`
  ADD CONSTRAINT `fk_cb_tag_type_relation_1` FOREIGN KEY (`cb_tag_id`) REFERENCES `cb_tag` (`cb_tag_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cb_tag_type_relation_2` FOREIGN KEY (`cb_type_id`) REFERENCES `cb_type` (`cb_type_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_type`
--
ALTER TABLE `cb_type`
  ADD CONSTRAINT `fk_cb_type_1` FOREIGN KEY (`cb_module_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cb_type_field_relation`
--
ALTER TABLE `cb_type_field_relation`
  ADD CONSTRAINT `fk_cb_type_field_relation_1` FOREIGN KEY (`cb_type_id`) REFERENCES `cb_type` (`cb_type_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cb_type_field_relation_2` FOREIGN KEY (`cb_field_id`) REFERENCES `cb_field` (`cb_field_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- Insert for Centreon Broker configurations

--
-- Contenu de la table `cb_tag`
--

INSERT INTO `cb_tag` (`cb_tag_id`, `tagname`) VALUES
(2, 'input'),
(3, 'logger'),
(1, 'output');

--
-- Contenu de la table `cb_module`
--

INSERT INTO `cb_module` (`cb_module_id`, `name`, `libname`, `loading_pos`, `is_bundle`, `is_activated`) VALUES
(1, 'SQL', 'sql.so', 80, 0, 1),
(2, 'TCP', 'tcp.so', 50, 0, 1),
(3, 'file', 'file.so', 50, 0, 1),
(4, 'local', 'local.so', 50, 0, 1),
(5, 'NDO', 'ndo.so', 80, 0, 1),
(6, 'NEB', 'neb.so', 10, 0, 1),
(7, 'RRD', 'rrd.so', 30, 0, 1),
(8, 'Storage', 'storage.so', 20, 0, 1),
(9, 'Core', NULL, NULL, 1, 1),
(10, 'Centreon Storage', NULL, NULL, 1, 1),
(11, 'Compression', 'compression.so', 60, 0, 1);

--
-- Contenu de la table `cb_type`
--

INSERT INTO `cb_type` (`cb_type_id`, `type_name`, `type_shortname`, `cb_module_id`) VALUES
(3, 'IPv4', 'ipv4', 2),
(10, 'IPv6', 'ipv6', 2),
(11, 'File', 'file', 3),
(12, 'Local Server Socket', 'local_server', 4),
(13, 'RRD File Generator', 'rrd', 7),
(14, 'Perfdata Generator (Centreon Storage)', 'storage', 8),
(15, 'Local Client Socket', 'local_client', 4),
(16, 'Broker SQL Database', 'sql', 1),
(17, 'File', 'file', 9),
(18, 'Standard', 'standard', 9),
(19, 'Syslog', 'syslog', 9),
(20, 'Compressor', 'compressor', 11);

--
-- Contenu de la table `cb_field`
--

INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES
(1, 'port', 'Connection port', 'Port for listen or connect in TCP', 'int', NULL),
(2, 'host', 'Host to connect to', NULL, 'text', NULL),
(3, 'ca_certificate', 'Trusted CA''s certificate', NULL, 'text', NULL),
(4, 'private_key', 'Private key file.', NULL, 'text', NULL),
(5, 'public_cert', 'Public certificate', NULL, 'text', NULL),
(6, 'tls', 'Enable TLS encryption', NULL, 'radio', NULL),
(7, 'db_host', 'DB host', NULL, 'text', NULL),
(8, 'db_user', 'DB user', NULL, 'text', NULL),
(9, 'db_password', 'DB password', NULL, 'text', NULL),
(10, 'db_name', 'DB name', NULL, 'text', NULL),
(11, 'path', 'File path', NULL, 'text', NULL),
(12, 'protocol', 'Serialization Protocol', NULL, 'select', NULL),
(13, 'metrics_path', 'Metrics RRD Directory', NULL, 'text', NULL),
(14, 'status_path', 'Status RRD Directory', NULL, 'text', NULL),
(15, 'db_type', 'DB type', NULL, 'select', NULL),
(16, 'interval', 'Interval Length', 'Interval Length in seconds', 'int', NULL),
(17, 'length', 'RRD Length', 'RRD storage duration.', 'int', NULL),
(18, 'db_port', 'DB Port', 'Port on which the DB server listens', 'int', NULL),
(19, 'name', 'Name of the logger', 'For a file logger this is the path to the file. For a standard logger, one of ''stdout'' or ''stderr''.', 'text', NULL),
(20, 'config', 'Configuration messages', 'Enable or not configuration messages logging.', 'radio', NULL),
(21, 'debug', 'Debug messages', 'Enable or not debug messages logging.', 'radio', NULL),
(22, 'error', 'Error messages', 'Enable or not error messages logging.', 'radio', NULL),
(23, 'info', 'Informational messages', 'Enable or not informational messages logging.', 'radio', NULL),
(24, 'level', 'Logging level', 'How much messages must be logged.', 'select', NULL),
(25, 'compression', 'Compression (zlib)', 'Enable or not data stream compression.', 'radio', NULL),
(26, 'compression_level', 'Compression level', 'Ranges from 1 (no compression) to 9 (best compression). -1 is the default', 'int', NULL),
(27, 'compression_buffer', 'Compression buffer size', 'The higher the buffer size is, the best compression. This however increase data streaming latency. Use with caution.', 'int', NULL);

--
-- Contenu de la table `cb_list`
--

INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`) VALUES
(1, 6, 'no'),
(1, 20, 'yes'),
(1, 21, 'no'),
(1, 22, 'yes'),
(1, 23, 'no'),
(1, 25, 'no'),
(2, 12, NULL),
(3, 15, NULL),
(4, 24, NULL);

--
-- Contenu de la table `cb_list_values`
--

INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`) VALUES
(1, 'No', 'no'),
(1, 'Yes', 'yes'),
(2, 'NDO Protocol', 'ndo'),
(3, 'DB2', 'db2'),
(3, 'InterBase', 'ibase'),
(3, 'MySQL', 'mysql'),
(3, 'ODBC', 'odbc'),
(3, 'Oracle', 'oci'),
(3, 'PostgreSQL', 'postgresql'),
(3, 'SQLite', 'sqlite'),
(3, 'Sysbase', 'tds'),
(4, 'Base', 'high'),
(4, 'Detailed', 'medium'),
(4, 'Very detailed', 'low');

--
-- Contenu de la table `cb_module_relation`
--

INSERT INTO `cb_module_relation` (`cb_module_id`, `module_depend_id`, `inherit_config`) VALUES
(1, 6, 0),
(1, 8, 0),
(2, 11, 1),
(3, 11, 1),
(4, 11, 1),
(5, 6, 0),
(7, 8, 0),
(8, 6, 0);

--
-- Contenu de la table `cb_tag_type_relation`
--

INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`) VALUES
(1, 3),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(2, 3),
(2, 10),
(2, 11),
(2, 12),
(2, 15),
(3, 17),
(3, 18),
(3, 19);

--
-- Contenu de la table `cb_type_field_relation`
--

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(3, 1, 1, 1),
(3, 2, 0, 2),
(3, 3, 0, 7),
(3, 4, 0, 5),
(3, 5, 0, 6),
(3, 6, 1, 4),
(3, 12, 1, 3),
(10, 1, 1, 1),
(10, 2, 0, 2),
(10, 3, 0, 7),
(10, 4, 0, 5),
(10, 5, 0, 6),
(10, 6, 1, 4),
(10, 12, 1, 3),
(11, 11, 1, 1),
(11, 12, 1, 2),
(12, 11, 1, 1),
(12, 12, 1, 2),
(13, 1, 0, 4),
(13, 11, 0, 3),
(13, 13, 1, 1),
(13, 14, 1, 2),
(14, 7, 1, 4),
(14, 8, 1, 5),
(14, 9, 1, 6),
(14, 10, 1, 7),
(14, 15, 1, 3),
(14, 16, 1, 1),
(14, 17, 1, 2),
(15, 11, 1, 1),
(15, 12, 1, 2),
(16, 7, 1, 2),
(16, 8, 1, 3),
(16, 9, 1, 4),
(16, 10, 1, 5),
(16, 15, 1, 1),
(17, 19, 1, 1),
(17, 20, 1, 2),
(17, 21, 1, 3),
(17, 22, 1, 4),
(17, 23, 1, 5),
(17, 24, 1, 6),
(18, 19, 1, 1),
(18, 20, 1, 2),
(18, 21, 1, 3),
(18, 22, 1, 4),
(18, 23, 1, 5),
(18, 24, 1, 6),
(19, 20, 1, 1),
(19, 21, 1, 2),
(19, 22, 1, 3),
(19, 23, 1, 4),
(19, 24, 1, 5),
(20, 25, 0, 101),
(20, 26, 0, 102),
(20, 27, 0, 103);


INSERT INTO `nagios_macro` (`macro_id`, `macro_name`) VALUES ( NULL, '$LONGSERVICEOUTPUT$');

ALTER TABLE `session` CHANGE `session_id` `session_id` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

UPDATE `informations` SET `value` = '2.3.0-RC2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-RC1' LIMIT 1;