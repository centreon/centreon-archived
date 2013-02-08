ALTER TABLE acknowledgements ADD COLUMN deletion_time int default NULL AFTER comment_data;
ALTER TABLE downtimes ADD COLUMN deletion_time int default NULL AFTER comment_data;

CREATE INDEX service_id	ON services (service_id);
CREATE INDEX start_time ON issues (start_time);
CREATE INDEX entry_time_2 ON acknowledgements (entry_time);
CREATE INDEX entry_time_2 ON downtimes (entry_time);