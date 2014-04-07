ALTER TABLE custom_views DROP COLUMN `layout`;
ALTER TABLE custom_views ADD COLUMN `mode` TINYINT(2) DEFAULT 0;
ALTER TABLE custom_views ADD COLUMN `locked` TINYINT(2) DEFAULT 0;
ALTER TABLE custom_views ADD COLUMN `owner_id` INT(11) NOT NULL;
ALTER TABLE custom_views ADD COLUMN `position` TEXT DEFAULT NULL;

ALTER TABLE custom_view_user_relation DROP FOREIGN KEY fk_custom_views_usergroup_id;
ALTER TABLE custom_view_user_relation DROP COLUMN `is_owner`;
ALTER TABLE custom_view_user_relation DROP COLUMN `locked`;
ALTER TABLE custom_view_user_relation DROP COLUMN `usergroup_id`;

ALTER TABLE widgets ADD COLUMN custom_view_id INT (11) NOT NULL;
UPDATE widgets w JOIN widget_views wv ON w.widget_id = wv.widget_id SET w.custom_view_id = wv.custom_view_id;
ALTER TABLE widgets ADD CONSTRAINT `fk_widget_custom_view_id` FOREIGN KEY(custom_view_id) REFERENCES custom_views(custom_view_id) ON DELETE CASCADE;

ALTER TABLE widget_preferences DROP FOREIGN KEY fk_widget_view_id;
ALTER TABLE widget_preferences DROP KEY widget_preferences_unique_index;
ALTER TABLE widget_preferences CHANGE widget_view_id widget_id INT (11);
UPDATE widget_preferences p JOIN widget_views wv ON wv.widget_view_id = p.widget_id SET p.widget_id = wv.widget_id;
DROP TABLE widget_views;
ALTER TABLE widget_preferences ADD CONSTRAINT fk_widget_id FOREIGN KEY(widget_id) REFERENCES widgets(widget_id) ON DELETE CASCADE;
