--add the audit log retention column for the retention options menu
ALTER TABLE `config` ADD COLUMN audit_log_retention int(11) DEFAULT 0;
