INSERT INTO `topology` (`topology_name`, `topology_url`, `readonly`, `is_react`, `topology_parent`, `topology_page`, `topology_group`, `topology_order`) VALUES ('Authentication', '/administration/authentication', '1', '1', 5, 509, 1, 10);

CREATE TABLE IF NOT EXISTS `cfg_nagios_logger` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cfg_nagios_id` int(11) NOT NULL,
    `log_v2_logger` enum('file', 'syslog') DEFAULT 'file',
    `log_level_functions` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'err',
    `log_level_config` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'info',
    `log_level_events` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'info',
    `log_level_checks` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'info',
    `log_level_notifications` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'err',
    `log_level_eventbroker` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'err',
    `log_level_external_command` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'info',
    `log_level_commands` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'err',
    `log_level_downtimes` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'err',
    `log_level_comments` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'err',
    `log_level_macros` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'err',
    `log_level_process` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'info',
    `log_level_runtime` enum('trace', 'debug', 'info', 'warning', 'err', 'critical', 'off') DEFAULT 'err',
    PRIMARY KEY (`id`),
    CONSTRAINT `cfg_nagios_logger_cfg_nagios_id_fk`
        FOREIGN KEY (`cfg_nagios_id`)
        REFERENCES `cfg_nagios` (`nagios_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;