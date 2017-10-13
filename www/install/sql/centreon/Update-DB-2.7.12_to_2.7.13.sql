-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.13' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.12' LIMIT 1;

-- Create downtime cache table for recurrent downtimes
CREATE TABLE IF NOT EXISTS `downtime_cache` (
  `downtime_cache_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`downtime_cache_id`),
  `downtime_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11),
  `start_timestamp` int(11) NOT NULL,
  `end_timestamp` int(11) NOT NULL,
  `start_hour` varchar(255) NOT NULL,
  `end_hour` varchar(255) NOT NULL,
  `dst` tinyint(2) NOT NULL DEFAULT 0,
  CONSTRAINT `downtime_cache_ibfk_1` FOREIGN KEY (`downtime_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_cache_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_cache_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
