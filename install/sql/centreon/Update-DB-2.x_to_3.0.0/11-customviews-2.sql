ALTER TABLE custom_view_user_relation ADD COLUMN is_default tinyint(2) DEFAULT 0;

UPDATE custom_view_user_relation r JOIN custom_view_default d ON d.custom_view_id = r.custom_view_id AND d.user_id = r.user_id SET is_default = 1;

DROP TABLE custom_view_default;
