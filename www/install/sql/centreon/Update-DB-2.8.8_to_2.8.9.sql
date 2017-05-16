-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.9' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.8' LIMIT 1;

-- Insert Centreon Partitioning base conf
INSERT INTO `options` (`key`, `value`)
VALUES
  ('partitioning_retention', 365),
  ('partitioning_retention_forward', 10),
  ('partitioning_backup_directory', '/var/cache/centreon/backup');
