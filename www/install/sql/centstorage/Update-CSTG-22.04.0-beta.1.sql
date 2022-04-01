CREATE TABLE `severities` (
  `severity_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` bigint(20) unsigned NOT NULL,
  `type` tinyint(4) unsigned NOT NULL COMMENT '0=service, 1=host',
  `name` varchar(255) NOT NULL,
  `level` int(11) unsigned NOT NULL,
  `icon_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`severity_id`),
  UNIQUE KEY `severities_id_type_uindex` (`id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `resources` (
  `resource_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '0=service, 1=host',
  `status` tinyint(3) unsigned DEFAULT NULL COMMENT 'service: 0=OK, 1=WARNING, 2=CRITICAL, 3=UNKNOWN, 4=PENDING\nhost: 0=UP, 1=DOWN, 2=UNREACHABLE, 4=PENDING',
  `status_ordered` tinyint(3) unsigned DEFAULT NULL COMMENT '0=OK=UP\n1=PENDING\n2=UNKNOWN=UNREACHABLE\n3=WARNING\n4=CRITICAL=DOWN',
  `in_downtime` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `acknowledged` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `status_confirmed` tinyint(1) DEFAULT NULL COMMENT '0=FALSE=SOFT\n1=TRUE=HARD',
  `check_attempts` tinyint(3) unsigned DEFAULT NULL,
  `max_check_attempts` tinyint(3) unsigned DEFAULT NULL,
  `poller_id` bigint(20) unsigned NOT NULL,
  `severity_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `notes_url` varchar(255) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `has_graph` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `passive_checks_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `active_checks_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=false, 1=true',
  `last_check_type` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0=active check, 1=passive check',
  `last_check` bigint(20) unsigned DEFAULT NULL COMMENT 'the last check timestamp',
  `output` text DEFAULT NULL,
  PRIMARY KEY (`resource_id`),
  UNIQUE KEY `resources_id_parent_id_type_uindex` (`id`,`parent_id`,`type`),
  KEY `resources_severities_severity_id_fk` (`severity_id`),
  CONSTRAINT `resources_severities_severity_id_fk` FOREIGN KEY (`severity_id`) REFERENCES `severities` (`severity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tags` (
  `tag_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` bigint(20) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '0=servicegroup, 1=hostgroup, 2=servicecategory, 3=hostcategory',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tags_id_type_uindex` (`id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `resources_tags` (
  `tag_id` bigint(20) unsigned NOT NULL,
  `resource_id` bigint(20) unsigned NOT NULL,
  KEY `resources_tags_resources_resource_id_fk` (`resource_id`),
  KEY `resources_tags_tag_id_fk` (`tag_id`),
  CONSTRAINT `resources_tags_resources_resource_id_fk` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resources_tags_tag_id_fk` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;