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

-- Rename and move events view menu
UPDATE `topology` SET `topology_name` = 'Resources Status', `topology_url` = '/monitoring/resources', `topology_parent` = 2, `topology_page` = 200 WHERE `topology_page` = 104;
UPDATE `contact` SET `default_page` = 200 WHERE `default_page` = 104 OR `default_page` IS NULL;

-- Add deprecation column in topology
ALTER TABLE `topology` ADD COLUMN `is_deprecated` enum('0','1') NOT NULL DEFAULT '0' AFTER `topology_show`;

-- Set services and hosts monitoring pages to deprecated
UPDATE `topology` SET `is_deprecated` = '1' WHERE `topology_page` IN (20201, 20202);

-- Add page deprecation column to contact
ALTER TABLE `contact` ADD COLUMN `show_deprecated_pages` enum('0','1') DEFAULT '0' AFTER `default_page`;
UPDATE `contact` SET `show_deprecated_pages` = '1';

-- Create platform_topology table
CREATE TABLE `platform_topology` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `address` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `parent_id` int(11),
    `server_id` int(11),
    PRIMARY KEY (`id`),
    CONSTRAINT `platform_topology_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `platform_topology_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `platform_topology` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
COMMENT='Registration and parent relation Table used to set the platform topology';

-- Modify informations.value column length from 255 to 1024 chars
ALTER TABLE `informations` MODIFY `value` varchar(1024);
