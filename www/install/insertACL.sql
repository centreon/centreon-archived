
INSERT INTO `acl_groups` (`acl_group_id`, `acl_group_name`, `acl_group_alias`, `acl_group_changed`, `acl_group_activate`) VALUES (1, 'ALL', 'ALL', 0, '1');
INSERT INTO `acl_group_contactgroups_relations` (`agcgr_id`, `cg_cg_id`, `acl_group_id`) VALUES (1, 3, 1);

INSERT INTO `acl_actions` (`acl_action_id`, `acl_action_name`, `acl_action_description`, `acl_action_activate`) VALUES (1, 'Simple User', 'Simple User', '1');
INSERT INTO `acl_actions_rules` (`aar_id`, `acl_action_rule_id`, `acl_action_name`) VALUES (1, 1, 'poller_stats'), (2, 1, 'top_counter'), (3, 1, 'service_acknowledgement'), (4, 1, 'service_schedule_check'), (5, 1, 'service_schedule_forced_check'), (6, 1, 'service_schedule_downtime'), (7, 1, 'service_comment'), (8, 1, 'host_acknowledgement'), (9, 1, 'host_schedule_check'), (10, 1, 'host_schedule_forced_check'), (11, 1, 'host_schedule_downtime'), (12, 1, 'host_comment');
INSERT INTO `acl_group_actions_relations` (`agar_id`, `acl_action_id`, `acl_group_id`) VALUES (1, 1, 1);

INSERT INTO `acl_resources` (`acl_res_id`, `acl_res_name`, `acl_res_alias`, `all_hosts`, `all_hostgroups`, `all_servicegroups`, `acl_res_activate`, `acl_res_comment`, `acl_res_status`, `changed`) VALUES (1, 'All Resources', 'All Resources', '1', '1', '1', '1', NULL, NULL, 0);
INSERT INTO `acl_res_group_relations` (`argr_id`, `acl_res_id`, `acl_group_id`) VALUES (1, 1, 1);
