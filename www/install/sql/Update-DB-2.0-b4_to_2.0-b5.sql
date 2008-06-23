

ALTER TABLE `host` ADD `display_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_alias` ;
ALTER TABLE `host` ADD `initial_state` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_checks_enabled` ;
ALTER TABLE `host` ADD `flap_detection_options` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `host_flap_detection_enabled` ;


ALTER TABLE `service` CHANGE `service_activate` `service_activate` ENUM( '0', '1', '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' 
ALTER TABLE `host` CHANGE `host_activate` `host_activate` ENUM( '0', '1', '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' 