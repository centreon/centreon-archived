-- Insert Centreon Partitioning base conf
INSERT INTO `options` (`key`, `value`)
VALUES
  ('partitioning_retention', 365),
  ('partitioning_retention_forward', 10),
  ('partitioning_backup_directory', '/var/cache/centreon/backup');
