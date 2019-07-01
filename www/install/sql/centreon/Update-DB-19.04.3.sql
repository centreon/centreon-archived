--
-- Change IP field from varchar(16) to varchar(255)
--
ALTER TABLE `remote_servers` MODIFY COLUMN `ip` VARCHAR(255) NOT NULL;
