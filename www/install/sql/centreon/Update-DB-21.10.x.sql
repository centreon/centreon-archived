-- Update remote_servers table
ALTER TABLE `remote_servers`
ADD `server_id` INTEGER,
ADD FOREIGN KEY(server_id) REFERENCES nagios_server(id);