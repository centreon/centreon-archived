-- INSERT INTO `topology` (`topology_id` , `topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) VALUES (NULL, 'Reporting', NULL, 50101, 5010110, 100, 1, './include/options/oreon/generalOpt/generalOpt.php', '&o=reporting', '0', '0', '1');

INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_hour_start', '0', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_minute_start', '0', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_hour_end', '24', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_minute_end', '0', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_Monday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_Tuesday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_Wednesday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_Thursday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_Friday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_Saturday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id`) VALUES (NULL, 'report_Sunday', '1', NULL);
