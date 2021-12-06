-- Create remote servers table for keeping track of remote instances
CREATE TABLE IF NOT EXISTS `remote_servers` (
`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
`ip` VARCHAR(16) NOT NULL,
`app_key` VARCHAR(40) NOT NULL,
`version` VARCHAR(16) NOT NULL,
`is_connected` TINYINT(1) NOT NULL DEFAULT 0,
`created_at` TIMESTAMP NOT NULL,
`connected_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add column to topology table to mark which pages are with React
ALTER TABLE `topology` ADD COLUMN `is_react` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `readonly`;

-- Change informations lengths
ALTER TABLE `informations` MODIFY COLUMN `value` varchar (255) NULL;

-- Move "Graphs" & "Broker Statistics" as "Server status" sub menu
UPDATE topology SET topology_parent = '505' WHERE topology_page = '10205';
UPDATE topology SET topology_page = '50501' WHERE topology_page = '10205' and topology_parent = '505';
UPDATE topology SET topology_parent = '505' WHERE topology_page = '10201';
UPDATE topology SET topology_page = '50502' WHERE topology_page = '10201' and topology_parent = '505';

-- Rename "Graphs" menu to "Engine Statistics"
UPDATE topology SET topology_name = 'Engine Statistics' WHERE topology_page = '50502';

-- Rename "Server Status" to "Platform Status"
UPDATE topology SET topology_name = 'Platform Status' WHERE topology_page = '505';

-- Change default page of "Platform Status" menu to "Broker Statistics"
UPDATE topology SET topology_url = './include/Administration/brokerPerformance/brokerPerformance.php' WHERE topology_page = '505' AND topology_name = 'Platform Status';

-- Delete old entries
DELETE FROM topology WHERE topology_page = '102';

-- Remove Zend support
DELETE FROM `options` WHERE `key` = 'backup_zend_conf';

-- Create tasks table
CREATE TABLE IF NOT EXISTS `task` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(40) NOT NULL,
  `status` VARCHAR(40) NOT NULL,
  `parent_id` INT(11) NULL,
  `params` BLOB NULL,
  `created_at` TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add column to nagios_server table for remote-poller relation
SET SESSION innodb_strict_mode=OFF;
ALTER TABLE `nagios_server` ADD COLUMN `remote_id` int(11) NULL AFTER `centreonbroker_logs_path`;
SET SESSION innodb_strict_mode=ON;
ALTER TABLE `nagios_server` ADD CONSTRAINT `nagios_server_remote_id_id` FOREIGN KEY (`remote_id`) REFERENCES `nagios_server` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Update the "About" menu
UPDATE topology SET topology_url = './include/Administration/about/about.php', topology_modules = '0', topology_popup = '0' WHERE topology_page = 506 AND topology_parent = 5;
DELETE FROM topology WHERE topology_parent = 506;

ALTER TABLE `remote_servers` ADD COLUMN `centreon_path` varchar(255) NULL;

-- Insert Multi-step Wizard into Topology
INSERT INTO `topology` (topology_name, topology_url, readonly, is_react) VALUES ('Poller/Remote Wizard', '/poller-wizard/1', '0', '1');
INSERT INTO `topology` (topology_name, topology_url, readonly, is_react) VALUES ('Remote Wizard Step 2', '/poller-wizard/2', '0', '1');
INSERT INTO `topology` (topology_name, topology_url, readonly, is_react) VALUES ('Remote Wizard Step 3', '/poller-wizard/3', '0', '1');
INSERT INTO `topology` (topology_name, topology_url, readonly, is_react) VALUES ('Remote Wizard Final Step', '/poller-wizard/4', '0', '1');
INSERT INTO `topology` (topology_name, topology_url, readonly, is_react) VALUES ('Poller Wizard Step 2', '/poller-wizard/5', '0', '1');
INSERT INTO `topology` (topology_name, topology_url, readonly, is_react) VALUES ('Poller Wizard Step 3', '/poller-wizard/6', '0', '1');
INSERT INTO `topology` (topology_name, topology_url, readonly, is_react) VALUES ('Poller Wizard Final Step', '/poller-wizard/7', '0', '1');
