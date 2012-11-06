--- Add audit log option
ALTER TABLE  `config` ADD  `audit_log_option` ENUM(  '0',  '1' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '1';
