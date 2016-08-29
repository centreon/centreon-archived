-- Issue #4649 - [logAnalyserBroker] Doesn't work
UPDATE `config` SET nagios_log_file = '/var/log/centreon-engine/centengine.log', archive_log = 1;
