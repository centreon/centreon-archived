<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1433431541.
 * Generated on 2015-06-04 17:25:41 by root
 */
class PropelMigration_1433431541
{

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'centreon' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `cfg_acl_resources_bas_relations`;

DROP TABLE IF EXISTS `cfg_acl_resources_business_activities_params`;

DROP TABLE IF EXISTS `cfg_acl_resources_tags_bas_relations`;

DROP TABLE IF EXISTS `cfg_bam`;

DROP TABLE IF EXISTS `cfg_bam_ba_groups`;

DROP TABLE IF EXISTS `cfg_bam_ba_type`;

DROP TABLE IF EXISTS `cfg_bam_bagroup_ba_relation`;

DROP TABLE IF EXISTS `cfg_bam_boolean`;

DROP TABLE IF EXISTS `cfg_bam_impacts`;

DROP TABLE IF EXISTS `cfg_bam_kpi`;

DROP TABLE IF EXISTS `cfg_bam_relations_ba_timeperiods`;

DROP TABLE IF EXISTS `cfg_centreonbroker`;

DROP TABLE IF EXISTS `cfg_centreonbroker_info`;

DROP TABLE IF EXISTS `cfg_centreonbroker_paths`;

DROP TABLE IF EXISTS `cfg_centreonbroker_pollervalues`;

DROP TABLE IF EXISTS `cfg_engine`;

DROP TABLE IF EXISTS `cfg_engine_broker_module`;

DROP TABLE IF EXISTS `cfg_meta_services`;

DROP TABLE IF EXISTS `cfg_meta_services_relations`;

DROP TABLE IF EXISTS `cfg_tags_bas`;

DROP TABLE IF EXISTS `mod_bam_reporting_ba`;

DROP TABLE IF EXISTS `mod_bam_reporting_ba_availabilities`;

DROP TABLE IF EXISTS `mod_bam_reporting_ba_events`;

DROP TABLE IF EXISTS `mod_bam_reporting_ba_events_durations`;

DROP TABLE IF EXISTS `mod_bam_reporting_bv`;

DROP TABLE IF EXISTS `mod_bam_reporting_kpi`;

DROP TABLE IF EXISTS `mod_bam_reporting_kpi_events`;

DROP TABLE IF EXISTS `mod_bam_reporting_relations_ba_bv`;

DROP TABLE IF EXISTS `mod_bam_reporting_relations_ba_kpi_events`;

DROP TABLE IF EXISTS `mod_bam_reporting_relations_ba_timeperiods`;

DROP TABLE IF EXISTS `mod_bam_reporting_timeperiods`;

DROP TABLE IF EXISTS `mod_bam_reporting_timeperiods_exceptions`;

DROP TABLE IF EXISTS `mod_bam_reporting_timeperiods_exclusions`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'centreon' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `cfg_acl_resources_bas_relations`
(
    `arbar_id` INTEGER NOT NULL AUTO_INCREMENT,
    `acl_resource_id` INTEGER,
    `ba_id` INTEGER,
    `type` TINYINT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`arbar_id`),
    INDEX `acl_resource_id` (`acl_resource_id`),
    INDEX `ba_id` (`ba_id`),
    CONSTRAINT `acl_resources_bas_relations_ibfk_1`
        FOREIGN KEY (`acl_resource_id`)
        REFERENCES `cfg_acl_resources` (`acl_resource_id`)
        ON DELETE CASCADE,
    CONSTRAINT `acl_resources_bas_relations_ibfk_2`
        FOREIGN KEY (`ba_id`)
        REFERENCES `cfg_bam` (`ba_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_acl_resources_business_activities_params`
(
    `acl_resource_id` INTEGER NOT NULL,
    `all_business_activities` TINYINT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`acl_resource_id`),
    INDEX `acl_resource_id` (`acl_resource_id`),
    CONSTRAINT `acl_resources_business_activities_params_ibfk_1`
        FOREIGN KEY (`acl_resource_id`)
        REFERENCES `cfg_acl_resources` (`acl_resource_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_acl_resources_tags_bas_relations`
(
    `artbar_id` INTEGER NOT NULL AUTO_INCREMENT,
    `acl_resource_id` INTEGER,
    `tag_id` INTEGER,
    `type` TINYINT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`artbar_id`),
    INDEX `acl_resource_id` (`acl_resource_id`),
    INDEX `tag_id` (`tag_id`),
    CONSTRAINT `acl_resources_tags_bas_relations_ibfk_1`
        FOREIGN KEY (`acl_resource_id`)
        REFERENCES `cfg_acl_resources` (`acl_resource_id`)
        ON DELETE CASCADE,
    CONSTRAINT `acl_resources_tags_bas_relations_ibfk_2`
        FOREIGN KEY (`tag_id`)
        REFERENCES `cfg_tags_bas` (`tag_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_bam`
(
    `ba_id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(254),
    `slug` VARCHAR(254) NOT NULL,
    `description` VARCHAR(254),
    `level_w` FLOAT,
    `level_c` FLOAT,
    `sla_month_percent_warn` FLOAT,
    `sla_month_percent_crit` FLOAT,
    `sla_month_duration_warn` INTEGER,
    `sla_month_duration_crit` INTEGER,
    `id_reporting_period` INTEGER,
    `max_check_attempts` INTEGER,
    `normal_check_interval` INTEGER,
    `retry_check_interval` INTEGER,
    `current_level` FLOAT,
    `calculate` enum(\'0\',\'1\') DEFAULT \'0\' NOT NULL,
    `downtime` FLOAT DEFAULT 0 NOT NULL,
    `acknowledged` FLOAT DEFAULT 0 NOT NULL,
    `must_be_rebuild` enum(\'0\',\'1\',\'2\') DEFAULT \'0\',
    `last_state_change` INTEGER,
    `current_status` TINYINT,
    `in_downtime` TINYINT(1),
    `dependency_dep_id` INTEGER,
    `graph_id` INTEGER,
    `icon_id` INTEGER,
    `graph_style` VARCHAR(254),
    `activate` TINYINT DEFAULT 1,
    `comment` TEXT,
    `organization_id` INTEGER DEFAULT 1 NOT NULL,
    `ba_type_id` INTEGER,
    PRIMARY KEY (`ba_id`),
    UNIQUE INDEX `unique_name` (`name`(254), `organization_id`),
    INDEX `name_index` (`name`(254)),
    INDEX `description_index` (`description`(254)),
    INDEX `calculate_index` (`calculate`),
    INDEX `currentlevel_index` (`current_level`),
    INDEX `levelw_index` (`level_w`),
    INDEX `levelc_index` (`level_c`),
    INDEX `id_reporting_period` (`id_reporting_period`),
    INDEX `dependency_index` (`dependency_dep_id`),
    INDEX `icon_index` (`icon_id`),
    INDEX `graph_index` (`graph_id`),
    INDEX `FI__ibfk_6` (`organization_id`),
    INDEX `FI__ibfk_7` (`ba_type_id`),
    CONSTRAINT `bam_ibfk_6`
        FOREIGN KEY (`organization_id`)
        REFERENCES `cfg_organizations` (`organization_id`)
        ON DELETE CASCADE,
    CONSTRAINT `bam_ibfk_7`
        FOREIGN KEY (`ba_type_id`)
        REFERENCES `cfg_bam_ba_type` (`ba_type_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_ibfk_4`
        FOREIGN KEY (`icon_id`)
        REFERENCES `cfg_view_images` (`img_id`)
        ON DELETE SET NULL,
    CONSTRAINT `mod_bam_ibfk_5`
        FOREIGN KEY (`id_reporting_period`)
        REFERENCES `cfg_timeperiods` (`tp_id`)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `cfg_bam_ba_groups`
(
    `id_ba_group` INTEGER NOT NULL AUTO_INCREMENT,
    `ba_group_name` VARCHAR(255),
    `ba_group_description` VARCHAR(255),
    `visible` CHAR DEFAULT \'1\',
    `organization_id` INTEGER NOT NULL,
    PRIMARY KEY (`id_ba_group`),
    UNIQUE INDEX `unique_name` (`ba_group_name`(255), `organization_id`),
    INDEX `FI__ba_groups_ibfk_1` (`organization_id`),
    CONSTRAINT `bam_ba_groups_ibfk_1`
        FOREIGN KEY (`organization_id`)
        REFERENCES `cfg_organizations` (`organization_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_bam_ba_type`
(
    `ba_type_id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255),
    `description` VARCHAR(255),
    PRIMARY KEY (`ba_type_id`),
    UNIQUE INDEX `unique_name` (`name`(255))
) ENGINE=InnoDB;

CREATE TABLE `cfg_bam_bagroup_ba_relation`
(
    `id_bgr` INTEGER NOT NULL AUTO_INCREMENT,
    `id_ba` INTEGER NOT NULL,
    `id_ba_group` INTEGER NOT NULL,
    PRIMARY KEY (`id_bgr`),
    INDEX `bam_ba_groups_ba_relation_ibfk_1` (`id_ba`),
    INDEX `bam_ba_groups_ba_relation_ibfk_2` (`id_ba_group`),
    CONSTRAINT `bam_ba_groups_ba_relation_ibfk_1`
        FOREIGN KEY (`id_ba`)
        REFERENCES `cfg_bam` (`ba_id`)
        ON DELETE CASCADE,
    CONSTRAINT `bam_bagroup_ba_relation_ibfk_2`
        FOREIGN KEY (`id_ba_group`)
        REFERENCES `cfg_bam_ba_groups` (`id_ba_group`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_bam_boolean`
(
    `boolean_id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(254) NOT NULL,
    `expression` TEXT NOT NULL,
    `bool_state` TINYINT(1) DEFAULT 1 NOT NULL,
    `comments` TEXT,
    `activate` TINYINT NOT NULL,
    PRIMARY KEY (`boolean_id`)
) ENGINE=InnoDB;

CREATE TABLE `cfg_bam_impacts`
(
    `id_impact` INTEGER NOT NULL AUTO_INCREMENT,
    `code` TINYINT NOT NULL,
    `impact` FLOAT NOT NULL,
    `color` VARCHAR(7),
    `organization_id` INTEGER NOT NULL,
    PRIMARY KEY (`id_impact`),
    INDEX `FI__impacts_ibfk_1` (`organization_id`),
    CONSTRAINT `bam_impacts_ibfk_1`
        FOREIGN KEY (`organization_id`)
        REFERENCES `cfg_organizations` (`organization_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_bam_kpi`
(
    `kpi_id` INTEGER NOT NULL AUTO_INCREMENT,
    `state_type` TINYINT DEFAULT 1 NOT NULL,
    `kpi_type` TINYINT DEFAULT 0 NOT NULL,
    `host_id` INTEGER,
    `service_id` INTEGER,
    `id_indicator_ba` INTEGER,
    `id_ba` INTEGER,
    `meta_id` INTEGER,
    `boolean_id` INTEGER,
    `current_status` SMALLINT,
    `last_level` FLOAT,
    `last_impact` FLOAT,
    `downtime` FLOAT,
    `acknowledged` FLOAT,
    `comment` TEXT,
    `config_type` TINYINT,
    `drop_warning` FLOAT,
    `drop_warning_impact_id` INTEGER,
    `drop_critical` FLOAT,
    `drop_critical_impact_id` INTEGER,
    `drop_unknown` FLOAT,
    `drop_unknown_impact_id` INTEGER,
    `activate` TINYINT DEFAULT 1,
    `ignore_downtime` TINYINT DEFAULT 0,
    `ignore_acknowledged` TINYINT DEFAULT 0,
    `last_state_change` INTEGER,
    `in_downtime` TINYINT(1),
    `organization_id` INTEGER NOT NULL,
    PRIMARY KEY (`kpi_id`),
    INDEX `ba_index` (`id_ba`),
    INDEX `ba_indicator_index` (`id_indicator_ba`),
    INDEX `host_id` (`host_id`),
    INDEX `svc_id` (`service_id`),
    INDEX `ms_index` (`meta_id`),
    INDEX `boolean_id` (`boolean_id`),
    CONSTRAINT `mod_bam_kpi_ibfk_3`
        FOREIGN KEY (`host_id`)
        REFERENCES `cfg_hosts` (`host_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_kpi_ibfk_4`
        FOREIGN KEY (`service_id`)
        REFERENCES `cfg_services` (`service_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_kpi_ibfk_5`
        FOREIGN KEY (`id_indicator_ba`)
        REFERENCES `cfg_bam` (`ba_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_kpi_ibfk_6`
        FOREIGN KEY (`id_ba`)
        REFERENCES `cfg_bam` (`ba_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_kpi_ibfk_7`
        FOREIGN KEY (`meta_id`)
        REFERENCES `cfg_meta_services` (`meta_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_kpi_ibfk_8`
        FOREIGN KEY (`boolean_id`)
        REFERENCES `cfg_bam_boolean` (`boolean_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_bam_relations_ba_timeperiods`
(
    `ba_id` INTEGER NOT NULL,
    `tp_id` INTEGER NOT NULL,
    PRIMARY KEY (`ba_id`,`tp_id`),
    INDEX `ba_id` (`ba_id`),
    INDEX `tp_id` (`tp_id`),
    CONSTRAINT `cfg_bam_relations_ba_timeperiods_ibfk_1`
        FOREIGN KEY (`ba_id`)
        REFERENCES `cfg_bam` (`ba_id`)
        ON DELETE CASCADE,
    CONSTRAINT `cfg_bam_relations_ba_timeperiods_ibfk_2`
        FOREIGN KEY (`tp_id`)
        REFERENCES `cfg_timeperiods` (`tp_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_centreonbroker`
(
    `config_id` INTEGER NOT NULL AUTO_INCREMENT,
    `poller_id` INTEGER NOT NULL,
    `config_name` VARCHAR(100) NOT NULL,
    `flush_logs` INTEGER,
    `write_timestamp` INTEGER,
    `write_thread_id` INTEGER,
    `event_queue_max_size` INTEGER,
    PRIMARY KEY (`config_id`),
    INDEX `cfg_centreonbroker_FI_1` (`poller_id`),
    CONSTRAINT `cfg_centreonbroker_FK_1`
        FOREIGN KEY (`poller_id`)
        REFERENCES `cfg_pollers` (`poller_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_centreonbroker_info`
(
    `config_id` INTEGER NOT NULL,
    `config_key` VARCHAR(255) NOT NULL,
    `config_value` VARCHAR(255) NOT NULL,
    `config_group` VARCHAR(50) NOT NULL,
    `config_group_id` INTEGER,
    `grp_level` INTEGER DEFAULT 0 NOT NULL,
    `subgrp_id` INTEGER,
    `parent_grp_id` INTEGER,
    PRIMARY KEY (`config_id`,`config_key`),
    INDEX `cfg_centreonbroker_info_idx01` (`config_id`),
    INDEX `cfg_centreonbroker_info_idx02` (`config_id`, `config_group`(50)),
    CONSTRAINT `cfg_centreonbroker_info_ibfk_01`
        FOREIGN KEY (`config_id`)
        REFERENCES `cfg_centreonbroker` (`config_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_centreonbroker_paths`
(
    `poller_id` INTEGER NOT NULL,
    `directory_config` VARCHAR(255) NOT NULL,
    `directory_modules` VARCHAR(255) NOT NULL,
    `directory_data` VARCHAR(255) NOT NULL,
    `directory_logs` VARCHAR(255) NOT NULL,
    `directory_cbmod` VARCHAR(255) NOT NULL,
    `init_script` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`poller_id`),
    CONSTRAINT `cfg_centreonbroker_paths_FK_1`
        FOREIGN KEY (`poller_id`)
        REFERENCES `cfg_pollers` (`poller_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_centreonbroker_pollervalues`
(
    `poller_id` INTEGER NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `value` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`poller_id`,`name`),
    CONSTRAINT `cfg_centreonbroker_pollervalues_FK_1`
        FOREIGN KEY (`poller_id`)
        REFERENCES `cfg_pollers` (`poller_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_engine`
(
    `poller_id` INTEGER NOT NULL,
    `bin_path` VARCHAR(255),
    `conf_dir` VARCHAR(255),
    `log_dir` VARCHAR(255),
    `var_lib_dir` VARCHAR(255),
    `module_dir` VARCHAR(255),
    `init_script` VARCHAR(255),
    `enable_event_handlers` TINYINT(1) DEFAULT 1,
    `external_command_buffer_slots` INTEGER,
    `command_check_interval` VARCHAR(255),
    `command_file` VARCHAR(255),
    `use_syslog` TINYINT(1) DEFAULT 1,
    `log_service_retries` TINYINT(1) DEFAULT 1,
    `log_host_retries` TINYINT(1) DEFAULT 0,
    `log_event_handlers` TINYINT(1) DEFAULT 1,
    `log_initial_states` TINYINT(1) DEFAULT 0,
    `log_external_commands` TINYINT(1) DEFAULT 1,
    `log_passive_checks` TINYINT(1) DEFAULT 1,
    `global_host_event_handler` INTEGER,
    `global_service_event_handler` INTEGER,
    `max_concurrent_checks` INTEGER,
    `check_result_reaper_frequency` INTEGER,
    `enable_flap_detection` TINYINT(1) DEFAULT 0,
    `low_service_flap_threshold` VARCHAR(255) DEFAULT \'20\',
    `high_service_flap_threshold` VARCHAR(255) DEFAULT \'30\',
    `low_host_flap_threshold` VARCHAR(255),
    `high_host_flap_threshold` VARCHAR(255) DEFAULT \'30\',
    `service_check_timeout` INTEGER DEFAULT 30,
    `host_check_timeout` INTEGER DEFAULT 30,
    `event_handler_timeout` INTEGER DEFAULT 30,
    `ocsp_timeout` INTEGER DEFAULT 15,
    `ochp_timeout` INTEGER DEFAULT 15,
    `ocsp_command` INTEGER,
    `ochp_command` INTEGER,
    `check_service_freshness` TINYINT(1) DEFAULT 0,
    `freshness_check_interval` INTEGER DEFAULT 30,
    `check_host_freshness` TINYINT(1) DEFAULT 0,
    `enable_predictive_host_dependency_checks` TINYINT(1) DEFAULT 1,
    `enable_predictive_service_dependency_checks` TINYINT(1) DEFAULT 0,
    `debug_file_path` VARCHAR(255),
    `debug_level` INTEGER DEFAULT 0,
    `debug_verbosity` TINYINT(1),
    `max_debug_file_size` INTEGER DEFAULT 1000000,
    PRIMARY KEY (`poller_id`),
    INDEX `cmd1_index` (`global_host_event_handler`),
    INDEX `cmd2_index` (`global_service_event_handler`),
    INDEX `cmd3_index` (`ocsp_command`),
    INDEX `cmd4_index` (`ochp_command`),
    INDEX `poller_id` (`poller_id`),
    CONSTRAINT `cfg_engine_ibfk_gheh`
        FOREIGN KEY (`global_host_event_handler`)
        REFERENCES `cfg_commands` (`command_id`)
        ON DELETE SET NULL,
    CONSTRAINT `cfg_engine_ibfk_gseh`
        FOREIGN KEY (`global_service_event_handler`)
        REFERENCES `cfg_commands` (`command_id`)
        ON DELETE SET NULL,
    CONSTRAINT `cfg_engine_ibfk_ochpc`
        FOREIGN KEY (`ochp_command`)
        REFERENCES `cfg_commands` (`command_id`)
        ON DELETE SET NULL,
    CONSTRAINT `cfg_engine_ibfk_ocspc`
        FOREIGN KEY (`ocsp_command`)
        REFERENCES `cfg_commands` (`command_id`)
        ON DELETE SET NULL,
    CONSTRAINT `cfg_engine_ibfk_poller_id`
        FOREIGN KEY (`poller_id`)
        REFERENCES `cfg_pollers` (`poller_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_engine_broker_module`
(
    `bk_mod_id` INTEGER NOT NULL AUTO_INCREMENT,
    `poller_id` INTEGER,
    `broker_module` VARCHAR(255),
    PRIMARY KEY (`bk_mod_id`),
    INDEX `fk_engine_cfg` (`poller_id`),
    CONSTRAINT `fk_engine_cfg`
        FOREIGN KEY (`poller_id`)
        REFERENCES `cfg_engine` (`poller_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_meta_services`
(
    `meta_id` INTEGER NOT NULL AUTO_INCREMENT,
    `meta_name` VARCHAR(254),
    `meta_display` VARCHAR(254),
    `check_period` INTEGER,
    `max_check_attempts` INTEGER,
    `normal_check_interval` INTEGER,
    `retry_check_interval` INTEGER,
    `calcul_type` enum(\'SOM\',\'AVE\',\'MIN\',\'MAX\'),
    `data_source_type` SMALLINT DEFAULT 0 NOT NULL,
    `meta_select_mode` enum(\'1\',\'2\') DEFAULT \'1\',
    `regexp_str` VARCHAR(254),
    `metric` VARCHAR(255),
    `warning` VARCHAR(254),
    `critical` VARCHAR(254),
    `graph_id` INTEGER,
    `meta_comment` TEXT,
    `meta_activate` enum(\'0\',\'1\'),
    `value` FLOAT,
    PRIMARY KEY (`meta_id`)
) ENGINE=InnoDB;

CREATE TABLE `cfg_meta_services_relations`
(
    `msr_id` INTEGER NOT NULL AUTO_INCREMENT,
    `meta_id` INTEGER,
    `host_id` INTEGER,
    `metric_id` INTEGER,
    `msr_comment` TEXT,
    `activate` enum(\'0\',\'1\'),
    PRIMARY KEY (`msr_id`),
    INDEX `meta_id` (`meta_id`),
    CONSTRAINT `cfg_meta_services_relations_ibfk_1`
        FOREIGN KEY (`meta_id`)
        REFERENCES `cfg_meta_services` (`meta_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cfg_tags_bas`
(
    `tag_id` INTEGER NOT NULL,
    `resource_id` INTEGER NOT NULL,
    `template_id` INTEGER,
    PRIMARY KEY (`tag_id`,`resource_id`),
    CONSTRAINT `cfg_tags_bas_fk_01`
        FOREIGN KEY (`tag_id`)
        REFERENCES `cfg_tags` (`tag_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_ba`
(
    `ba_id` INTEGER NOT NULL,
    `ba_name` VARCHAR(45),
    `ba_description` TEXT,
    `sla_month_percent_crit` FLOAT,
    `sla_month_percent_warn` FLOAT,
    `sla_month_duration_crit` INTEGER,
    `sla_month_duration_warn` INTEGER,
    PRIMARY KEY (`ba_id`),
    UNIQUE INDEX `ba_name` (`ba_name`(45))
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_ba_availabilities`
(
    `ba_id` INTEGER NOT NULL,
    `time_id` INTEGER NOT NULL,
    `timeperiod_id` INTEGER NOT NULL,
    `available` INTEGER,
    `unavailable` INTEGER,
    `degraded` INTEGER,
    `unknown` INTEGER,
    `downtime` INTEGER,
    `alert_unavailable_opened` INTEGER,
    `alert_degraded_opened` INTEGER,
    `alert_unknown_opened` INTEGER,
    `nb_downtime` INTEGER,
    `timeperiod_is_default` TINYINT(1),
    PRIMARY KEY (`ba_id`),
    UNIQUE INDEX `ba_id` (`ba_id`, `time_id`, `timeperiod_id`)
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_ba_events`
(
    `ba_event_id` INTEGER NOT NULL AUTO_INCREMENT,
    `ba_id` INTEGER NOT NULL,
    `start_time` INTEGER NOT NULL,
    `first_level` DOUBLE,
    `end_time` INTEGER,
    `status` TINYINT,
    `in_downtime` TINYINT(1),
    PRIMARY KEY (`ba_event_id`),
    INDEX `ba_id` (`ba_id`, `start_time`),
    INDEX `ba_id_2` (`ba_id`, `end_time`)
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_ba_events_durations`
(
    `ba_event_id` INTEGER NOT NULL,
    `timeperiod_id` INTEGER NOT NULL,
    `start_time` INTEGER,
    `end_time` INTEGER,
    `duration` INTEGER,
    `sla_duration` INTEGER,
    `timeperiod_is_default` TINYINT(1),
    PRIMARY KEY (`ba_event_id`),
    UNIQUE INDEX `ba_event_id` (`ba_event_id`, `timeperiod_id`),
    INDEX `end_time` (`end_time`, `start_time`),
    CONSTRAINT `mod_bam_reporting_ba_events_durations_ibfk_1`
        FOREIGN KEY (`ba_event_id`)
        REFERENCES `mod_bam_reporting_ba_events` (`ba_event_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_bv`
(
    `bv_id` INTEGER NOT NULL AUTO_INCREMENT,
    `bv_name` VARCHAR(45),
    `bv_description` TEXT,
    PRIMARY KEY (`bv_id`),
    UNIQUE INDEX `bv_name` (`bv_name`(45))
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_kpi`
(
    `kpi_id` INTEGER NOT NULL,
    `kpi_name` VARCHAR(45),
    `ba_id` INTEGER,
    `ba_name` VARCHAR(45),
    `host_id` INTEGER,
    `host_name` VARCHAR(45),
    `service_id` INTEGER,
    `service_description` VARCHAR(45),
    `kpi_ba_id` INTEGER,
    `kpi_ba_name` VARCHAR(45),
    `meta_service_id` INTEGER,
    `meta_service_name` VARCHAR(45),
    `boolean_id` INTEGER,
    `boolean_name` VARCHAR(45),
    `impact_warning` FLOAT,
    `impact_critical` FLOAT,
    `impact_unknown` FLOAT,
    PRIMARY KEY (`kpi_id`),
    INDEX `ba_id` (`ba_id`),
    INDEX `kpi_ba_id` (`kpi_ba_id`),
    CONSTRAINT `mod_bam_reporting_kpi_ibfk_1`
        FOREIGN KEY (`ba_id`)
        REFERENCES `mod_bam_reporting_ba` (`ba_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_reporting_kpi_ibfk_2`
        FOREIGN KEY (`kpi_ba_id`)
        REFERENCES `mod_bam_reporting_ba` (`ba_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_kpi_events`
(
    `kpi_event_id` INTEGER NOT NULL AUTO_INCREMENT,
    `kpi_id` INTEGER NOT NULL,
    `start_time` INTEGER NOT NULL,
    `end_time` INTEGER,
    `status` TINYINT,
    `in_downtime` TINYINT(1),
    `impact_level` TINYINT,
    `first_output` TEXT,
    `first_perfdata` VARCHAR(45),
    PRIMARY KEY (`kpi_event_id`),
    INDEX `kpi_id` (`kpi_id`, `start_time`)
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_relations_ba_bv`
(
    `ba_bv_id` INTEGER NOT NULL AUTO_INCREMENT,
    `bv_id` INTEGER NOT NULL,
    `ba_id` INTEGER NOT NULL,
    PRIMARY KEY (`ba_bv_id`),
    INDEX `bv_id` (`bv_id`),
    INDEX `ba_id` (`ba_id`),
    CONSTRAINT `mod_bam_reporting_relations_ba_bv_ibfk_1`
        FOREIGN KEY (`bv_id`)
        REFERENCES `mod_bam_reporting_bv` (`bv_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_reporting_relations_ba_bv_ibfk_2`
        FOREIGN KEY (`ba_id`)
        REFERENCES `mod_bam_reporting_ba` (`ba_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_relations_ba_kpi_events`
(
    `ba_event_id` INTEGER NOT NULL,
    `kpi_event_id` INTEGER NOT NULL,
    PRIMARY KEY (`ba_event_id`,`kpi_event_id`),
    INDEX `ba_event_id` (`ba_event_id`),
    INDEX `kpi_event_id` (`kpi_event_id`),
    CONSTRAINT `mod_bam_reporting_relations_ba_kpi_events_ibfk_1`
        FOREIGN KEY (`ba_event_id`)
        REFERENCES `mod_bam_reporting_ba_events` (`ba_event_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_reporting_relations_ba_kpi_events_ibfk_2`
        FOREIGN KEY (`kpi_event_id`)
        REFERENCES `mod_bam_reporting_kpi_events` (`kpi_event_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_relations_ba_timeperiods`
(
    `ba_id` INTEGER NOT NULL,
    `timeperiod_id` INTEGER NOT NULL,
    `is_default` TINYINT(1),
    PRIMARY KEY (`ba_id`,`timeperiod_id`),
    INDEX `ba_id` (`ba_id`),
    INDEX `timeperiod_id` (`timeperiod_id`),
    CONSTRAINT `mod_bam_reporting_relations_ba_timeperiods_ibfk_1`
        FOREIGN KEY (`ba_id`)
        REFERENCES `mod_bam_reporting_ba` (`ba_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_reporting_relations_ba_timeperiods_ibfk_2`
        FOREIGN KEY (`timeperiod_id`)
        REFERENCES `mod_bam_reporting_timeperiods` (`timeperiod_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_timeperiods`
(
    `timeperiod_id` INTEGER NOT NULL,
    `name` VARCHAR(200),
    `sunday` VARCHAR(200),
    `monday` VARCHAR(200),
    `tuesday` VARCHAR(200),
    `wednesday` VARCHAR(200),
    `thursday` VARCHAR(200),
    `friday` VARCHAR(200),
    `saturday` VARCHAR(200),
    PRIMARY KEY (`timeperiod_id`)
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_timeperiods_exceptions`
(
    `timeperiod_id` INTEGER NOT NULL,
    `daterange` VARCHAR(255) NOT NULL,
    `timerange` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`timeperiod_id`),
    INDEX `timeperiod_id` (`timeperiod_id`),
    CONSTRAINT `mod_bam_reporting_timeperiods_exceptions_ibfk_1`
        FOREIGN KEY (`timeperiod_id`)
        REFERENCES `mod_bam_reporting_timeperiods` (`timeperiod_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `mod_bam_reporting_timeperiods_exclusions`
(
    `timeperiod_id` INTEGER NOT NULL,
    `excluded_timeperiod_id` INTEGER NOT NULL,
    PRIMARY KEY (`timeperiod_id`,`excluded_timeperiod_id`),
    INDEX `timeperiod_id` (`timeperiod_id`),
    INDEX `excluded_timeperiod_id` (`excluded_timeperiod_id`),
    CONSTRAINT `mod_bam_reporting_timeperiods_exclusions_ibfk_1`
        FOREIGN KEY (`timeperiod_id`)
        REFERENCES `mod_bam_reporting_timeperiods` (`timeperiod_id`)
        ON DELETE CASCADE,
    CONSTRAINT `mod_bam_reporting_timeperiods_exclusions_ibfk_2`
        FOREIGN KEY (`excluded_timeperiod_id`)
        REFERENCES `mod_bam_reporting_timeperiods` (`timeperiod_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}