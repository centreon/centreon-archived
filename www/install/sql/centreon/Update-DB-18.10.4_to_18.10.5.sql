-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.5' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.4' LIMIT 1;

-- Remove non existing entries
DELETE FROM topology_JS WHERE id_page IN ('201', '2020301', '2020302', '5010103', '5010105');

-- Add HTTPS connexion to Remote Server
ALTER TABLE remote_servers ADD COLUMN `http_method` enum('http','https') NOT NULL DEFAULT 'http';
ALTER TABLE remote_servers ADD COLUMN `http_port` int(11) NULL DEFAULT NULL;
ALTER TABLE remote_servers ADD COLUMN `no_check_certificate` enum('0','1') NOT NULL DEFAULT '1';
