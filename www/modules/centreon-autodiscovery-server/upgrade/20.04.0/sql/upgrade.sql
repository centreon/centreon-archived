DROP TABLE `mod_host_disco_provider_mapping`;
ALTER TABLE `mod_host_disco_provider` DROP COLUMN test_option;
ALTER TABLE `mod_host_disco_provider` ADD `attributes` TEXT DEFAULT NULL NULL;
ALTER TABLE `mod_host_disco_provider` ADD need_proxy TINYINT UNSIGNED DEFAULT 0 NOT NULL;
ALTER TABLE `mod_host_disco_provider` DROP COLUMN default_template;
ALTER TABLE `mod_host_disco_provider` ADD host_template_id INT NULL;

