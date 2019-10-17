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
