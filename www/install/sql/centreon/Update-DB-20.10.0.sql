-- Create user_filter table
CREATE TABLE IF NOT EXISTS `user_filter` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `user_id` int(11) NOT NULL,
    `page_name` varchar(255) NOT NULL,
    `criterias` text,
    `order` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `filter_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create platform_topology table
CREATE TABLE `platform_topology` (
    `ip_address` varchar(255) NOT NULL,
    `hostname` varchar(255) NOT NULL,
    `server_type` tinyint(1) NOT NULL DEFAULT 0,
    `parent` varchar(255),
    PRIMARY KEY (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert the current platform into the platform_topology table
INSERT INTO `platform_topology` (
    `ip_address`,
    `hostname`,
    `server_type`,
    `parent`
) VALUES (
    (SELECT `ns_ip_address` FROM nagios_server WHERE localhost = '1'),
    (SELECT `name` FROM nagios_server WHERE localhost = '1'),
    0,
    NULL
);