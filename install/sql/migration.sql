ALTER TABLE custom_view_user_relation DROP FOREIGN KEY fk_custom_views_usergroup_id;
ALTER TABLE custom_view_user_relation DROP FOREIGN KEY fk_custom_views_user_id;
ALTER TABLE custom_view_user_relation DROP FOREIGN KEY fk_custom_view_user_id;
ALTER TABLE `centreon`.`custom_view_user_relation` CHANGE COLUMN `user_id` `user_id` INT(11) NOT NULL , ADD PRIMARY KEY (`custom_view_id`, `user_id`);
DROP TABLE `session`;
CREATE TABLE `session` (
    `session_id` VARCHAR(255) NOT NULL,
    `user_id` INT NOT NULL,
    `session_start_time` INT NOT NULL,
    `last_reload` INT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `route` VARCHAR(255) NOT NULL,
    `update_acl` BOOLEAN DEFAULT 0,
    PRIMARY KEY(`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE widget_preferences DROP FOREIGN KEY fk_widget_view_id;
ALTER TABLE `options` ADD COLUMN `group` VARCHAR(255) NOT NULL DEFAULT 'default' FIRST;