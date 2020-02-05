--
--  Creation and config of central broker
--
INSERT INTO `cfg_centreonbroker` (`config_id`, `config_name`, `config_filename`, `config_write_timestamp`, `config_write_thread_id`, `config_activate`, `ns_nagios_server`, `event_queue_max_size`, `cache_directory`, `command_file`, `daemon`) VALUES (1,'central-broker-master','central-broker.json','1','0','1', 1 , 100000, '@centreonbroker_varlib@', '@centreonbroker_varlib@/command.sock', 1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'name','central-broker-master-input','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'port','5669','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'buffering_timeout','0','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'host','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'retry_interval','60','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'protocol','bbdo','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'tls','auto','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'private_key','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'public_cert','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'ca_certificate','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'negotiation','yes','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'one_peer_retention_mode','no','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'compression','auto','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'compression_level','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'compression_buffer','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'type','ipv4','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'blockId','2_3','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'name','@centreonbroker_log@/central-broker-master.log','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'config','yes','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'debug','no','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'error','yes','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'info','no','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'level','low','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'max_size','','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'type','file','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'blockId','3_17','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'name','central-broker-master-sql','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_type','mysql','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'retry_interval','60','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'buffering_timeout','0','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_host','@address@','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_port','@port@','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_user','@db_user@','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_password','@db_password@','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_name','@db_storage@','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'queries_per_transaction','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'read_timeout','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'type','sql','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'blockId','1_16','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'name','centreon-broker-master-rrd','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'port','5670','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'buffering_timeout','0','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'host','localhost','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'retry_interval','60','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'protocol','bbdo','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'tls','no','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'private_key','','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'public_cert','','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'ca_certificate','','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'negotiation','yes','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'one_peer_retention_mode','no','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'compression','no','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'compression_level','','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'compression_buffer','','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'type','ipv4','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'blockId','1_3','output',2);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'name','central-broker-master-perfdata','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'interval','60','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'retry_interval','60','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'buffering_timeout','0','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'length','15552000','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_type','mysql','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_host','@address@','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_port','@port@','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_user','@db_user@','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_password','@db_password@','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'db_name','@db_storage@','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'queries_per_transaction','','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'read_timeout','','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'check_replication','no','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'rebuild_check_interval','','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'store_in_data_bin','yes','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'insert_in_index_data','1','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'type','storage','output',3);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (1,'blockId','1_14','output',3);

--
--  Creation and config of central rrd
--

INSERT INTO `cfg_centreonbroker` (`config_id`, `config_name`, `config_filename`, `config_write_timestamp`, `config_write_thread_id`, `config_activate`, `ns_nagios_server`, `event_queue_max_size`, `cache_directory`, `daemon`) VALUES (2,'central-rrd-master','central-rrd.json','1','0','1',1 , 100000, '@centreonbroker_varlib@', 1);

INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'name','central-rrd-master-input','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'port','5670','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'buffering_timeout','0','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'host','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'retry_interval','60','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'protocol','bbdo','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'tls','auto','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'private_key','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'public_cert','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'ca_certificate','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'negotiation','yes','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'one_peer_retention_mode','no','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'compression','auto','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'compression_level','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'compression_buffer','','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'type','ipv4','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'blockId','2_3','input',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'name','@centreonbroker_log@/central-rrd-master.log','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'config','yes','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'debug','no','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'error','yes','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'info','no','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'level','low','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'max_size','','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'type','file','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'blockId','3_17','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'name','central-rrd-master-output','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'metrics_path','@centreon_varlib@/metrics','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'status_path','@centreon_varlib@/status','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'retry_interval','60','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'buffering_timeout','0','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'path','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'port','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'write_metrics','yes','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'write_status','yes','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'type','rrd','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (2,'blockId','1_13','output',1);

--
--  Creation and config of central module
--
INSERT INTO `cfg_centreonbroker` (`config_id`, `config_name`, `config_filename`, `config_write_timestamp`, `config_write_thread_id`, `config_activate`, `ns_nagios_server`, `event_queue_max_size`, `cache_directory`, `daemon`) VALUES (3,'central-module-master','central-module.json','0','0', '1', 1 , 100000, '@monitoring_var_lib@', 0);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'name','@centreonbroker_log@/central-module-master.log','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'config','yes','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'debug','no','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'error','yes','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'info','no','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'level','low','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'max_size','','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'type','file','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'blockId','3_17','logger',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'name','central-module-master-output','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'port','5669','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'host','localhost','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'retry_interval','60','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'buffering_timeout','0','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'protocol','bbdo','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'tls','no','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'private_key','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'public_cert','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'ca_certificate','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'negotiation','yes','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'one_peer_retention_mode','no','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'compression','no','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'compression_level','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'compression_buffer','','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'type','ipv4','output',1);
INSERT INTO `cfg_centreonbroker_info` (`config_id`, `config_key`, `config_value`, `config_group`, `config_group_id`) VALUES (3,'blockId','1_3','output',1);

INSERT INTO `options` (`key`, `value`) VALUES ('enable_perfdata_sync', '0');
INSERT INTO `options` (`key`, `value`) VALUES ('enable_logs_sync', '0');

UPDATE `nagios_server` SET `centreonbroker_cfg_path` = '@broker_etc@' WHERE `id` = 1;
UPDATE `nagios_server` SET `centreonbroker_module_path` = '@centreonbroker_lib@' WHERE `id` = 1;

INSERT INTO cfg_nagios_broker_module (`cfg_nagios_id`, `broker_module`) VALUES (1, '@centreonbroker_cbmod@ @centreonbroker_etc@/central-module.json');
