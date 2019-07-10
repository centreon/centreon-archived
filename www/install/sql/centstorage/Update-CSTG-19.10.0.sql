
--
-- Alter existing tables to conform with strict mode.
--
ALTER TABLE `log_action_modification` MODIFY COLUMN `field_value` text NOT NULL;