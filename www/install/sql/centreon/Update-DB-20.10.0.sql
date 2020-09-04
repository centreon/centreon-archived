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
UPDATE `contact` SET `default_page` = 200 WHERE `default_page` = 104;

-- Create a new menu page related to remote. Hidden by default on a Central
INSERT INTO `topology`(
    `topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`,
    `topology_url`, `topology_url_opt`,
    `topology_popup`, `topology_modules`, `topology_show`,
    `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`
) VALUES (
    'Remote access', 501, 50120, 25, 1,
    './include/Administration/parameters/parameters.php', '&o=remote',
    '0', '0', '0',
    NULL, NULL, NULL, '1'
);
