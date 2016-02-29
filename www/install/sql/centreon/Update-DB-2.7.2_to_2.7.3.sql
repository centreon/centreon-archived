-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.2' LIMIT 1;

-- Set the default number of elements for select2
INSERT INTO `options` (`key`, `value`) VALUES ('selectPaginationSize', 60);
INSERT INTO timezone (`timezone_name`, `timezone_offset`, `timezone_dst_offset`) VALUES ('GMT', '-00:00', '-00:00'), ('UTC', '-00:00', '-00:00');
