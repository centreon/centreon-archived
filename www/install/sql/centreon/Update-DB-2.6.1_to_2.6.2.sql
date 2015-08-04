-- Add default socket path for Centreon Broker
INSERT INTO `options` (`key`, `value`) VALUES ('broker_socket_path', '@CENTREONBROKER_VARLIB@/command');

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.6.2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.1' LIMIT 1;