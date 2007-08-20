ALTER TABLE `index_data` ADD INDEX ( `must_be_rebuild` );
ALTER TABLE `index_data` ADD INDEX ( `trashed` );
ALTER TABLE `index_data` ADD INDEX ( `host_name` );
ALTER TABLE `index_data` ADD INDEX ( `service_description` );

UPDATE `config` SET `len_storage_mysql` = '365' ;
ALTER TABLE `index_data` CHANGE `must_be_rebuild` `must_be_rebuild` ENUM( '0', '1', '2' ) NULL DEFAULT '0';


