-- migrate downtimes.end_time & downtimes.duration columns from int(11) to bigint(20)
ALTER TABLE `downtimes` MODIFY COLUMN `end_time` BIGINT(20) NULL DEFAULT NULL;
ALTER TABLE `downtimes` MODIFY COLUMN `duration` BIGINT(20) NULL DEFAULT NULL;

-- Remove useless reporting tables
ALTER TABLE log_archive_host
  DROP COLUMN UPTimeAverageAck,
  DROP COLUMN UPTimeAverageRecovery,
  DROP COLUMN DOWNTimeAverageAck,
  DROP COLUMN DOWNTimeAverageRecovery,
  DROP COLUMN UNREACHABLETimeAverageAck,
  DROP COLUMN UNREACHABLETimeAverageRecovery;
ALTER TABLE log_archive_service
  DROP COLUMN OKTimeAverageAck,
  DROP COLUMN OKTimeAverageRecovery,
  DROP COLUMN WARNINGTimeAverageAck,
  DROP COLUMN WARNINGTimeAverageRecovery,
  DROP COLUMN UNKNOWNTimeAverageAck,
  DROP COLUMN UNKNOWNTimeAverageRecovery,
  DROP COLUMN CRITICALTimeAverageAck,
  DROP COLUMN CRITICALTimeAverageRecovery;
