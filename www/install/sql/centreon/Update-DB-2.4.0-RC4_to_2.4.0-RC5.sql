--
-- Adds new fields to `cfg_cgi`
--

ALTER TABLE `cfg_cgi` ADD `action_url_target` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER  `cgi_activate`;
ALTER TABLE `cfg_cgi` ADD `escape_html_tags` ENUM('0', '1', '2') NULL DEFAULT '2' AFTER `action_url_target`;
ALTER TABLE `cfg_cgi` ADD `lock_author_names` ENUM('0', '1', '2') NULL DEFAULT '2' AFTER `escape_html_tags`;
ALTER TABLE `cfg_cgi` ADD `notes_url_target` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lock_author_names`;
