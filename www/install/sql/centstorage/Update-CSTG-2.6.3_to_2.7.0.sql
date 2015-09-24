ALTER TABLE `hoststateevents` ADD COLUMN `in_ack` tinyint(4) DEFAULT '0';
ALTER TABLE `servicestateevents` ADD COLUMN `in_ack` tinyint(4) DEFAULT '0';

-- Ticket #2276
ALTER TABLE config ENGINE=InnoDB;
ALTER TABLE data_stats_daily ENGINE=InnoDB;
ALTER TABLE data_stats_monthly ENGINE=InnoDB;
ALTER TABLE data_stats_yearly ENGINE=InnoDB;
ALTER TABLE index_data ENGINE=InnoDB;
ALTER TABLE instance ENGINE=InnoDB;
ALTER TABLE log_action ENGINE=InnoDB;
ALTER TABLE log_action_modification ENGINE=InnoDB;
ALTER TABLE log_archive_last_status ENGINE=InnoDB;
ALTER TABLE log_archive_service ENGINE=InnoDB;
ALTER TABLE log_snmptt ENGINE=InnoDB;
ALTER TABLE metrics ENGINE=InnoDB;
ALTER TABLE statistics ENGINE=InnoDB;


