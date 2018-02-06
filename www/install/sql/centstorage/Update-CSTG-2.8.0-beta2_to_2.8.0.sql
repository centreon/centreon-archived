-- Issue #4649 - [logAnalyserBroker] Doesn't work
UPDATE `config` SET nagios_log_file = '/var/log/centreon-engine/centengine.log', archive_log = 1;

-- Issue #4624 - improve poller listing loading time
CREATE INDEX action_log_date_idx ON log_action (action_log_date);


ALTER TABLE centreon_acl DROP INDEX group_id_by_id;
ALTER TABLE centreon_acl ADD INDEX `index1` (`group_id`,`host_id`,`service_id`);