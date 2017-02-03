-- Change version of Centreon
ALTER TABLE nagios_server ADD COLUMN centreonbroker_logs_path VARCHAR(255);
