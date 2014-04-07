ALTER TABLE `logs` ADD INDEX ( `host_name`(64) );
ALTER TABLE `logs` ADD INDEX ( `service_description`(64) );
ALTER TABLE `logs` ADD INDEX ( `status` );
ALTER TABLE `logs` ADD INDEX ( `instance_name` );
ALTER TABLE `logs` ADD INDEX ( `ctime` ); 