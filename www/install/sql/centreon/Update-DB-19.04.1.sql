-- Add HTTPS connexion to Remote Server
ALTER TABLE remote_servers ADD COLUMN `http_method` enum('http','https') NOT NULL DEFAULT 'http';
ALTER TABLE remote_servers ADD COLUMN `http_port` int(11) NULL DEFAULT NULL;
ALTER TABLE remote_servers ADD COLUMN `no_check_certificate` enum('0','1') NOT NULL DEFAULT '0';
