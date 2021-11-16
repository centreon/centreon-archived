--
-- Insert version
--

INSERT INTO `informations` (`key` ,`value`) VALUES ('version', '22.04.0-beta.1');

--
-- Contenu de la table `contact`
--

INSERT INTO `contact` (`contact_id`, `timeperiod_tp_id`, `timeperiod_tp_id2`, `contact_name`, `contact_alias`, `contact_passwd`, `contact_lang`, `contact_host_notification_options`, `contact_service_notification_options`, `contact_email`, `contact_pager`, `contact_comment`, `contact_oreon`, `contact_admin`, `contact_type_msg`, `contact_activate`, `contact_auth_type`, `contact_ldap_dn`, `contact_enable_notifications`) VALUES(1, 1, 1, '@firstname@ @lastname@', 'admin', MD5('@admin_password@'), 'en_US.UTF-8', 'n', 'n', '@email@', NULL, NULL, '1', '1', 'txt', '1', 'local', NULL, '1');
INSERT INTO `contact` (`contact_id`, `timeperiod_tp_id`, `timeperiod_tp_id2`, `contact_name`, `contact_alias`, `contact_passwd`, `contact_lang`, `contact_host_notification_options`, `contact_service_notification_options`, `contact_email`, `contact_pager`, `contact_comment`, `contact_oreon`, `contact_admin`, `contact_type_msg`, `contact_activate`, `contact_auth_type`, `contact_ldap_dn`) VALUES(17, 1, 1, 'Guest', 'guest', NULL, 'en_US.UTF-8', 'n', 'n', 'guest@localhost', NULL, NULL, '0', '0', 'txt', '0', 'local', NULL);
INSERT INTO `contact` (`contact_id`, `timeperiod_tp_id`, `timeperiod_tp_id2`, `contact_name`, `contact_alias`, `contact_passwd`, `contact_lang`, `contact_host_notification_options`, `contact_service_notification_options`, `contact_email`, `contact_pager`, `contact_comment`, `contact_oreon`, `contact_admin`, `contact_type_msg`, `contact_activate`, `contact_auth_type`, `contact_ldap_dn`) VALUES(18, 1, 1, 'User', 'user', NULL, 'en_US.UTF-8', 'n', 'n', 'user@localhost', NULL, NULL, '0', '0', 'txt', '0', 'local', NULL);


--
-- Contenu de la table `contact_param`
--
INSERT INTO `contact_param` (`id`, `cp_key`, `cp_value`, `cp_contact_id`) VALUES (1, 'report_hour_start', '0', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (2, 'report_minute_start', '0', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (3, 'report_hour_end', '24', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (4, 'report_minute_end', '0', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (5, 'report_Monday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (6, 'report_Tuesday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (7, 'report_Wednesday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (8, 'report_Thursday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (9, 'report_Friday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (10, 'report_Saturday', '1', NULL);
INSERT INTO `contact_param` (`id`, `cp_key` , `cp_value` , `cp_contact_id` ) VALUES (11, 'report_Sunday', '1', NULL);

--
-- Contenu de la table `contactgroup`
--

INSERT INTO `contactgroup` (`cg_id`, `cg_name`, `cg_alias`, `cg_comment`, `cg_activate`) VALUES(3, 'Guest', 'Guests Group', '', '1');
INSERT INTO `contactgroup` (`cg_id`, `cg_name`, `cg_alias`, `cg_comment`, `cg_activate`) VALUES(5, 'Supervisors', 'Centreon supervisors', '', '1');

--
-- Contenu de la table `contactgroup_contact_relation`
--

INSERT INTO `contactgroup_contact_relation` (`contact_contact_id`, `contactgroup_cg_id`) VALUES(17, 3);
INSERT INTO `contactgroup_contact_relation` (`contact_contact_id`, `contactgroup_cg_id`) VALUES(1, 5);
INSERT INTO `contactgroup_contact_relation` (`contact_contact_id`, `contactgroup_cg_id`) VALUES(18, 3);

--
-- Contenu de la table `service_categories`
--

INSERT INTO `service_categories` (`sc_id`, `sc_name`, `sc_description`, `sc_activate`) VALUES(1, 'Ping', 'ping', '1');
INSERT INTO `service_categories` (`sc_id`, `sc_name`, `sc_description`, `sc_activate`) VALUES(2, 'Traffic', 'Traffic', '1');
INSERT INTO `service_categories` (`sc_id`, `sc_name`, `sc_description`, `sc_activate`) VALUES(3, 'Disk', 'Disk', '0');
INSERT INTO `service_categories` (`sc_id`, `sc_name`, `sc_description`, `sc_activate`) VALUES(4, 'Memory', 'Memory', '0');

--
-- Contenu de la table `css_color_menu`
--

INSERT INTO `css_color_menu` (`id_css_color_menu`, `menu_nb`, `css_name`) VALUES(1, 1, 'blue_css.php');
INSERT INTO `css_color_menu` (`id_css_color_menu`, `menu_nb`, `css_name`) VALUES(2, 2, 'green_css.php');
INSERT INTO `css_color_menu` (`id_css_color_menu`, `menu_nb`, `css_name`) VALUES(3, 3, 'yellow_css.php');
INSERT INTO `css_color_menu` (`id_css_color_menu`, `menu_nb`, `css_name`) VALUES(4, 4, 'red_css.php');
INSERT INTO `css_color_menu` (`id_css_color_menu`, `menu_nb`, `css_name`) VALUES(5, 5, 'blue_css.php');
INSERT INTO `css_color_menu` (`id_css_color_menu`, `menu_nb`, `css_name`) VALUES(6, 6, 'blue_css.php');

--
-- Contenu de la table `options`
--
INSERT INTO `options` (`key`, `value`) VALUES
('ldap_dns_use_ssl', '0'),
('ldap_dns_use_tls', '0'),
('ldap_auth_enable', '0'),
('ldap_auto_import', '0'),
('ldap_srv_dns', '0'),
('ldap_dns_use_domain', ''),
('ldap_search_timeout','60'),
('ldap_search_limit','60'),
('ldap_last_acl_update', '0'),
('ldap_contact_tmpl', '0'),
('broker','broker'),
('oreon_path','@centreon_dir@/'),
('oreon_web_path','/centreon/'),
('session_expire','120'),
('maxViewMonitoring','30'),
('maxGraphPerformances','18'),
('maxViewConfiguration','30'),
('AjaxTimeReloadMonitoring','15'),
('AjaxTimeReloadStatistic','15'),
('template','Centreon-2'),
('color_up','#88b917'),
('color_down','#e00b3d'),
('color_unreachable','#818285'),
('color_ok','#88b917'),
('color_warning','#ff9a13'),
('color_critical','#e00b3d'),
('color_pending','#2AD1D4'),
('color_unknown','#bcbdc0'),
('color_ack','#FAED60'),
('color_host_down','#'),
('color_host_unreachable','#9CD9F1'),
('color_line_critical','#F96461'),
('color_downtime','#FBC5E8'),
('global_sort_type','host_name'),
('global_sort_order','ASC'),
('problem_sort_type','last_state_change'),
('problem_sort_order','ASC'),
('nagios_path_plugins','@plugin_dir@/'),
('nagios_version','3'),
('mailer_path_bin','@mail@'),
('snmp_community','public'),
('snmp_version','1'),
('snmptt_unknowntrap_log_file','snmpttunknown.log'),
('snmpttconvertmib_path_bin','@centreon_dir@/bin/snmpttconvertmib'),
('perl_library_path','/usr/local/lib'),
('rrdtool_path_bin','@rrdtool_dir@'),
('rrdtool_version','1.4.8'),
('debug_path','@centreon_log@/'),
('debug_auth','0'),
('debug_nagios_import','0'),
('debug_rrdtool','0'),
('debug_ldap_import','0'),
('debug_inventory','0'),
('enable_autologin','0'),
('display_autologin_shortcut','1'),
('monitoring_ack_svc', '1'),
('monitoring_dwt_duration', '3600'),
('monitoring_dwt_duration_scale', 's'),
('monitoring_ack_active_checks', '1'),
('monitoring_ack_persistent', '1'),
('monitoring_ack_notify', '0'),
('monitoring_ack_sticky', '1'),
('monitoring_dwt_fixed', '1'),
('monitoring_dwt_svc', '1'),
('tactical_host_limit', '100'),
('tactical_service_limit', '100'),
('tactical_refresh_interval', '20'),
('index_data', '1'),
('interval_length', '60'),
('nagios_path_img','@centreon_dir@/www/img/media/'),
('selectPaginationSize', 60),
('display_downtime_chart','0'),
('display_comment_chart','0'),
('gorgone_illegal_characters', '`'),
('inheritance_mode', '3'),
('gorgone_api_address', '127.0.0.1'),
('gorgone_api_port', '8085'),
('gorgone_api_ssl', '0'),
('gorgone_api_allow_self_signed', '1'),
('gorgone_cmd_timeout', '5'),
('enable_broker_stats', '0'),
('openid_connect_enable', '0'),
('openid_connect_mode', '1'),
('openid_connect_trusted_clients', ''),
('openid_connect_blacklist_clients', ''),
('openid_connect_base_url', ''),
('openid_connect_authorization_endpoint', ''),
('openid_connect_token_endpoint', ''),
('openid_connect_introspection_endpoint', ''),
('openid_connect_userinfo_endpoint', ''),
('openid_connect_end_session_endpoint', ''),
('openid_connect_scope', ''),
('openid_connect_login_claim', ''),
('openid_connect_redirect_url', ''),
('openid_connect_client_id', ''),
('openid_connect_client_secret', ''),
('openid_connect_client_basic_auth', '0'),
('openid_connect_verify_peer', '0');

--
-- Contenu de la table `giv_components_template`
--

INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(1, 'Default', 1, NULL, 'Default', NULL, '1', '#FFFFFF', NULL, NULL, NULL, '1', '1', NULL, '1', '1', NULL, 1, '10', NULL, 'Default', '0', NULL, '1', NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(2, 'Ping-pl', 1, NULL, 'pl', '#F30B23', '0', '#F30B23', NULL, NULL, '1', '1', NULL, NULL, '1', NULL, NULL, 1, '80', NULL, 'Packet Loss', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(3, 'Storage-used', 1, NULL, 'used', '#2B28D7', '0', '#2B28D7', NULL, NULL, '1', NULL, NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Usage', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(4, 'Cpu-total_cpu_avg', 1, NULL, 'total_cpu_avg', '#4C65B2', '0', '#4C65B2', NULL, NULL, NULL, '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Average Cpu Usage', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(5, 'Uptime', 1, NULL, 'uptime', '#FF0000', '0', '#FF0000', NULL, NULL, '1', NULL, NULL, NULL, NULL, '1', NULL, 1, '80', NULL, 'Uptime', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(6, 'Interface-traffic_in', 1, NULL, 'traffic_in', '#FF0000', '0', '#FF0000', NULL, NULL, '1', '1', NULL, NULL, '1', '1', NULL, 1, '80', '1', 'Inbound Traffic', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(7, 'Interface-traffic_out', 1, NULL, 'traffic_out', '#1EE045', '0', '#1EE045', NULL, NULL, '1', '1', NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Outbound Traffic', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(8, 'Load-load1', 3, NULL, 'load1', '#F3D30B', '0', '#F3D30B', NULL, NULL, '1', NULL, NULL, NULL, '1', NULL, NULL, 1, '80', NULL, 'Average Load Last Minute', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(9, 'Load-load5', 2, NULL, 'load5', '#E97B15', '0', '#E97B15', NULL, NULL, '1', NULL, NULL, NULL, '1', NULL, NULL, 1, '80', NULL, 'Average Load Last 5 Minutes', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(10, 'Load-load15', 1, NULL, 'load15', '#F11F0D', '0', '#F11F0D', NULL, NULL, '1', NULL, NULL, NULL, '1', NULL, NULL, 1, '80', NULL, 'Average Load Last 15 Minutes', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(11, 'BA_Level', 1, NULL, 'BA_Level', '#597f00', '0', '#88b917', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '50', NULL, 'BA Health Level', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(12, 'Cpu-Detailed-idle', 11, NULL, 'idle', '#60D8FF', '0', '#60D8FF', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Idle', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(13, 'Cpu-Detailed-system', 8, NULL, 'system', '#F3D30B', '0', '#F3D30B', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'System', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(14, 'Cpu-Detailed-user', 9, NULL, 'user', '#01FD9D', '0', '#01FD9D', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'User', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(15, 'Cpu-Detailed-wait', 10, NULL, 'wait', '#FA0417', '0', '#FA0417', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Wait', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(16, 'Cpu-Detailed-nice', 4, NULL, 'nice', '#B740BE', '0', '#B740BE', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Nice', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(17, 'Cpu-Detailed-guestnice', 5, NULL, 'guestnice', '#5972A6', '0', '#5972A6', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Guest Nice', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(18, 'Cpu-Detailed-guest', 6, NULL, 'guest', '#ADC23C', '0', '#ADC23C', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Guest', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(19, 'Cpu-Detailed-interrupt', 7, NULL, 'interrupt', '#F9A105', '0', '#F9A105', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Interrupt', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(20, 'Cpu-Detailed-kernel', 3, NULL, 'kernel', '#29A21F', '0', '#29A21F', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Kernel', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(21, 'Cpu-Detailed-softirq', 2, NULL, 'softirq', '#B7474E', '0', '#B7474E', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Software Interrupt', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(22, 'Cpu-Detailed-steal', 1, NULL, 'steal', '#6911EE', '0', '#6911EE', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Steal', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(23, 'Ping-rta', 1, NULL, 'rta', '#29AFEE', '0', '#29AFEE', NULL, NULL, '1', NULL, NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Round-Trip Average Time', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(24, 'Ping-rtmax', 2, NULL, 'rtmax', '#525256', '0', '#525256', NULL, NULL, NULL, '1', NULL, NULL, NULL, '1', NULL, 2, '80', NULL, 'Round-Trip Maximum Time', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(25, 'Ping-rtmin', 2, NULL, 'rtmin', '#191777', '0', '#191777', NULL, NULL, NULL, NULL, '1', NULL, NULL, '1', NULL, 2, '80', NULL, 'Round-Trip Minimum Time', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(26, 'Time', 1, NULL, 'time', '#29AFEE', '0', '#29AFEE', NULL, NULL, '1', NULL, NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Time', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(27, 'Process-nbproc', 1, NULL, 'nbproc', '#525256', '0', '#525256', NULL, NULL, NULL, '1', '1', NULL, NULL, '1', NULL, 2, '80', NULL, 'Number of Processes', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(28, 'Memory-buffer', 2, NULL, 'buffer', '#7B7C84', '0', '#7B7C84', NULL, NULL, '1', NULL, NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Buffer', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(29, 'Memory-cached', 3, NULL, 'cached', '#35B7C9', '0', '#35B7C9', NULL, NULL, '1', NULL, NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Cached', '0', '1', NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(30, 'Interface-packets_discard_in', 1, NULL, 'packets_discard_in', '#6F9669', '0', '#6F9669', NULL, NULL, '1', '1', NULL, NULL, '1', '1', NULL, 1, '80', '1', 'Inbound Packets Discard', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(31, 'Interface-packets_error_in', 1, NULL, 'packets_error_in', '#B940BE', '0', '#B940BE', NULL, NULL, '1', '1', NULL, NULL, '1', '1', NULL, 1, '80', '1', 'Inbound Packets Error', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(32, 'Interface-packets_discard_out', 1, NULL, 'packets_discard_out', '#2D53B2', '0', '#2D53B2', NULL, NULL, '1', '1', NULL, NULL, '1', '1', NULL, 1, '80', '1', 'Outbound Packets Discard', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(33, 'Interface-packets_error_out', 1, NULL, 'packets_error_out', '#C26F3D', '0', '#C26F3D', NULL, NULL, '1', '1', NULL, NULL, '1', '1', NULL, 1, '80', '1', 'Outbound Packets Error', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(34, 'Storage-provisioned', 1, NULL, 'provisioned', '#659A69', '0', '#659A69', NULL, NULL, '1', NULL, NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Provisioned', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(35, 'Disk-riops', 1, NULL, 'riops', '#0684F8', '0', '#0684F8', NULL, NULL, '1', NULL, NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Read IOPS', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(36, 'Disk-wiops', 1, NULL, 'wiops', '#EE1169', '0', '#EE1169', NULL, NULL, '1', NULL, NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Write IOPS', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(37, 'VM-Count-poweredoff', 2, NULL, 'poweredoff', '#F30B5A', '0', '#F30B5A', NULL, NULL, '1', '1', '1', '1', '1', '1', NULL, 1, '80', NULL, 'Powered Off', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(38, 'VM-Count-poweredon', 1, NULL, 'poweredon', '#11EE5C', '0', '#11EE5C', NULL, NULL, '1', '1', '1', '1', '1', '1', NULL, 1, '80', NULL, 'Powered On', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(39, 'VM-Count-suspended', 3, NULL, 'suspended', '#62635E', '0', '#62635E', NULL, NULL, '1', '1', '1', '1', '1', '1', NULL, 1, '80', NULL, 'Suspended', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(40, 'Esx-alarm_critical', 1, NULL, 'alarm_critical', '#D52941', '0', '#D52941', NULL, NULL, '1', '1', '1', '1', NULL, '1', NULL, 1, '80', NULL, 'Critical Alarms', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(41, 'Esx-alarm_warning', 1, NULL, 'alarm_warning', '#E7BD17', '0', '#E7BD17', NULL, NULL, '1', '1', '1', '1', NULL, '1', NULL, 1, '80', NULL, 'Warning Alarms', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(42, 'Cpu-cpu_total', 1, NULL, 'cpu_total', '#4C65B2', '0', '#4C65B2', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Average Cpu Usage', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(43, 'Cpu-cpu_total_MHz', 2, NULL, 'cpu_total_MHz', '#FC7DEA', '0', '#FC7DEA', NULL, NULL, '1', '1', '1', NULL, '1', '1', NULL, 1, '80', NULL, 'Average Cpu Usage (MHz)', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(45, 'Esx-swap_in', 1, NULL, 'swap_in', '#FF0000', '0', '#FF0000', NULL, NULL, '1', '1', NULL, NULL, '1', '1', NULL, 1, '80', '1', 'Swap In', '0', NULL, NULL, NULL);
INSERT INTO `giv_components_template` (`compo_id`, `name`, `ds_order`, `ds_hidecurve`, `ds_name`, `ds_color_line`, `ds_color_line_mode`, `ds_color_area`, `ds_color_area_warn`, `ds_color_area_crit`, `ds_filled`, `ds_max`, `ds_min`, `ds_minmax_int`, `ds_average`, `ds_last`, `ds_total`, `ds_tickness`, `ds_transparency`, `ds_invert`, `ds_legend`, `ds_jumpline`, `ds_stack`, `default_tpl1`, `comment`) VALUES(46, 'Esx-swap_out', 1, NULL, 'swap_out', '#1EE045', '0', '#1EE045', NULL, NULL, '1', '1', NULL, NULL, '1', '1', NULL, 1, '80', NULL, 'Swap Out', '0', NULL, NULL, NULL);

--
-- Contenu de la table `giv_graphs_template`
--

INSERT INTO `giv_graphs_template` (`graph_id`, `name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `default_tpl1`, `stacked`, `split_component`, `scaled`, `comment`) VALUES(1, 'Default_Graph', 'Value', 550, 140, 1000, 0, NULL, '1', NULL, NULL, '1', NULL);
INSERT INTO `giv_graphs_template` (`graph_id`, `name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `default_tpl1`, `stacked`, `split_component`, `scaled`, `comment`) VALUES(2, 'Latency', 'Latency', 550, 140, 1000, 0, NULL, NULL, NULL, NULL, '1', NULL);
INSERT INTO `giv_graphs_template` (`graph_id`, `name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `default_tpl1`, `stacked`, `split_component`, `scaled`, `comment`) VALUES(3, 'Storage', 'Storage', 550, 140, 1024, 0, NULL, NULL, NULL, NULL, '1', NULL);
INSERT INTO `giv_graphs_template` (`graph_id`, `name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `default_tpl1`, `stacked`, `split_component`, `scaled`, `comment`) VALUES(4, 'Memory', 'Memory', 550, 140, 1024, 0, NULL, NULL, NULL, NULL, '1', NULL);
INSERT INTO `giv_graphs_template` (`graph_id`, `name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `default_tpl1`, `stacked`, `split_component`, `scaled`, `comment`) VALUES(5, 'CPU', 'Processor Use', 550, 140, 1000, 0, 110, '0', NULL, NULL, '1', NULL);
INSERT INTO `giv_graphs_template` (`graph_id`, `name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `default_tpl1`, `stacked`, `split_component`, `scaled`, `comment`) VALUES(6, 'Uptime', 'Uptime', 550, 140, 1000, 0, NULL, NULL, NULL, NULL, '1', NULL);
INSERT INTO `giv_graphs_template` (`graph_id`, `name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `default_tpl1`, `stacked`, `split_component`, `scaled`, `comment`) VALUES(7, 'Traffic', 'Traffic', 550, 140, 1000, NULL, NULL, NULL, NULL, NULL, '1', NULL);
INSERT INTO `giv_graphs_template` (`graph_id`, `name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `default_tpl1`, `stacked`, `split_component`, `scaled`, `comment`) VALUES(8, 'Load_Average', 'Load_Average', 550, 140, 1000, 0, NULL, NULL, NULL, NULL, '1', NULL);


--
-- Contenu de la table Connector
--
INSERT INTO `connector` (`id`, `name`, `description`, `command_line`, `enabled`, `created`, `modified`) VALUES
(1, 'Perl Connector', '', 'centreon_connector_perl', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 'SSH Connector', '', 'centreon_connector_ssh', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());


--
-- Contenu de la table `traps_vendor`
--

INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES(1, 'Cisco', 'Cisco Networks', '');
INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES(2, 'HP', 'HP Networks', '');
INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES(3, '3com', '3Com', NULL);
INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES(4, 'Linksys', 'Linksys', '');
INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES(6, 'Dell', 'Dell', '');
INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES(7, 'Generic', 'Generic', 'References Generic Traps');
INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES(9, 'Zebra', 'Zebra', '');
INSERT INTO `traps_vendor` (`id`, `name`, `alias`, `description`) VALUES(11, 'HP-Compaq', 'HP and Compaq Systems', '');

--
-- Contenu de la table `traps`
--

INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(1, 'linkDown', '.1.3.6.1.6.3.1.1.5.3', 'Link down on interface $2. State: $4.', '2', 7, 'A linkDown(2) trap signifies that the sending protocol entity recognizes a failure in one of the communication links represented in the agent&#039;s configuration.');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(2, 'linkUp', '.1.3.6.1.6.3.1.1.5.4', 'Link up on interface $2. State: $4.', '0', 7, 'A linkUp(3) trap signifies that the sending protocol entity recognizes that one of the communication links represented in the agent&#039;s configuration has come up.');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(3, 'warmStart', '.1.3.6.1.6.3.1.1.5.2', 'SNMP is reinitializing', '0', 7, 'A warmStart(1) trap signifies that the sending protocol entity is reinitializing itself such that neither the agent configuration nor the protocol entity implementation is altered.');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(4, 'coldStart', '.1.3.6.1.6.3.1.1.5.1', 'SNMP is restarting', '1', 7, 'A coldStart(0) trap signifies that the sending protocol entity is reinitializing itself such that the agent&#039;s configuration or the protocol entity implementation may be altered.');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(6, 'unitDeparture', '.1.3.6.1.4.1.43.0.89', 'This trap is raised by the lowest numbered unit in a stack when $*', '0', 3, 'This trap is raised by the lowest numbered unit in a stack when\na communications loss is detected to another unit. The raising of this\ntrap is optional.\nVariables:\n  1: stackUnitDesc\n  2: stackUnitSerialNumber\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(7, 'remPollSuccessTrap', '.1.3.6.1.4.1.43.0.74', 'This trap is generated by the EventTable (if the eventEntry  $*', '0', 3, 'This trap is generated by the EventTable (if the eventEntry \nis configured to send traps) when the remPollTable receives a reply to a poll \nafter a sequence of un- successful polls.\nVariables:\n  1: remPollAddress\n  2: remPollProtocol\n  3: remPollInformation\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(8, 'remPollFailureTrap', '.1.3.6.1.4.1.43.0.75', 'This trap is generated by the EventTable (if the eventEntry  $*', '0', 3, 'This trap is generated by the EventTable (if the eventEntry \nis configured to send traps) when the remPollTable fails to receive a reply to a poll.\nVariables:\n  1: remPollAddress\n  2: remPollProtocol\n  3: remPollInformation\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(9, 'secureAddressLearned', '.1.3.6.1.4.1.43.0.71', 'This trap is sent when a new station has been learned. The  $*', '0', 3, 'This trap is sent when a new station has been learned. The \nslot and port on which the address was received are in the first and\nsecond index of secureAddrRowStatus, and the MAC address of the learned\nstation is in the third index.\nVariables:\n  1: secureAddrRowStatus\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(10, 'secureAddressLearned2', '.1.3.6.1.4.1.43.0.85', 'This trap is sent when a new station has been learned. $*', '0', 3, 'This trap is sent when a new station has been learned.\nThe slot and port on which the address was received are\nin  the  first and second index of secureAddrRowStatus,\nand  the MAC  address of the learned station is in  the\nthird index.\nVariables:\n  1: secureAddrRowStatus\n  2: a3ComVlanIfGlobalIdentifier\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(11, 'secureViolation2', '.1.3.6.1.4.1.43.0.78', 'This trap is sent whenever a security violation has occurred.  $*', '0', 3, 'This trap is sent whenever a security violation has occurred. \nThe slot and port on which the violation occured are in the first and\nsecond index of secureAddrRowStatus, and the MAC address of the offending\nstation is in the third index. rptrPortAdminSTATUS indicates if the port\nhas been disabled because of the violation. The implementation may not\nsend violation traps from the same port at intervals of less than 5\nseconds\nVariables:\n  1: secureAddrRowStatus\n  2: rptrPortAdminStatus\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(12, 'secureViolation3', '.1.3.6.1.4.1.43.0.86', 'This trap is sent whenever a security violation has  $*', '0', 3, 'This trap is sent whenever a security violation has \noccured. The slot and port on which the violation occured are in the \nfirst and second index of secureAddrRowStatus, and the MAC address of \nthe offending station is in the third index. ifAdminStatus indicates\nif the port has been disabled because of the violation. \nThe implementation may not send violation traps from the same port\nat intervals of less than 5 seconds.\nVariables:\n  1: secureAddrRowStatus\n  2: ifAdminStatus\n  3: a3ComVlanIfGlobalIdentifier\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(13, 'secureViolation4', '.1.3.6.1.4.1.43.0.88', 'This trap is sent whenever a security violation has  $*', '0', 3, 'This trap is sent whenever a security violation has \noccurred. The slot and port on which the violation occurred are in the \nfirst and second index of secureAddrRowStatus, and the MAC address of \nthe offending station is in the third index. ifAdminStatus indicates\nif the port has been disabled because of the violation. \nThe implementation may not send violation traps from the same port\nat intervals of less than 5 seconds.\nVariables:\n  1: secureAddrRowStatus\n  2: ifAdminStatus\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(14, 'eventGenericTrap', '.1.3.6.1.4.1.43.0.82', 'This trap is sent to management stations when an internal  $*', '0', 3, 'This trap is sent to management stations when an internal \nRMON event is tiggered and there is no applicable specific trap. The trap \nidentifies the event index triggered and includes a value for \n''eventDescription''. Note that the text is not the literal value of the event \ndescription column in this table but rather the descriptive text that would have \nbeen added to the event log.\nVariables:\n  1: eventDescription\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(15, 'brDatabaseFull', '.1.3.6.1.4.1.43.0.65', 'This trap indicates that either the Filtering  $*', '0', 3, 'This trap indicates that either the Filtering \ndatabase, the permanent database or the ATM Downlink \ndatabase has become full. If the database occupancy \nexceeds 90% this trap will be sent also. The variable \nbindings enable the trap to be identified as refering to \nthe filtering, permanent, or ATM Downlink database, and \nto differentiate between 90% or 100% full.\nVariables:\n  1: brDatabaseType\n  2: brDatabaseLevel\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(16, 'trapPrinterOnline', '.1.3.6.1.4.1.683.6.0.1', 'Printer On-Line: Port $1 is On-Line', '0', 9, 'The printer is on-line.  This trap will be sent\nout after a printer error condition has been cleared.\nVariables:\n  1: outputIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(17, 'trapPrinterOffline', '.1.3.6.1.4.1.683.6.0.2', 'Printer Off-Line: Port $1 is Off-Line', '0', 9, 'The printer is off-line.\nVariables:\n  1: outputIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(18, 'trapNoPrinterAttached', '.1.3.6.1.4.1.683.6.0.3', 'No Printer Attached: Port $1 - No Printer Attached', '0', 9, 'No printer is attached to the output port.\nVariables:\n  1: outputIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(19, 'trapPrinterTonerLow', '.1.3.6.1.4.1.683.6.0.4', 'Toner Low: Port $1 - Toner Low', '1', 9, 'The printer toner is low.\nVariables:\n  1: outputIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(20, 'trapPrinterPaperOut', '.1.3.6.1.4.1.683.6.0.5', 'Printer Out of Paper: Port $1 - Out of Paper', '1', 9, 'The printer is out of paper.\nVariables:\n  1: outputIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(21, 'trapPrinterPaperJam', '.1.3.6.1.4.1.683.6.0.6', 'Paper Jam: Port $1 - Paper Jam', '2', 9, 'The printer has a paper jam.\nVariables:\n  1: outputIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(22, 'trapPrinterDoorOpen', '.1.3.6.1.4.1.683.6.0.7', 'Printer Door Open: Port $1 - Printer Door Open', '0', 9, 'The printer door is open.\nVariables:\n  1: outputIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(23, 'trapPrinterError', '.1.3.6.1.4.1.683.6.0.16', 'General Printer Error: Port $1 - Printer Error', '1', 9, 'General printer error.\nVariables:\n  1: outputIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(281, 'alertAutomaticSystemRecovery', '.1.3.6.1.4.1.674.10892.1.0.1006', 'Server Automatic System Recovery: $3', '2', 6, 'Automatic system recovery (ASR) was performed.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(295, 'alertAmperageProbeNormal', '.1.3.6.1.4.1.674.10892.1.0.1202', 'Server Amperage Normal: $3', '0', 6, 'Amperage probe has returned to a normal value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(296, 'alertAmperageProbeWarning', '.1.3.6.1.4.1.674.10892.1.0.1203', 'Server Amperage Warning: $3', '1', 6, 'Amperage probe has detected a warning value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(297, 'alertAmperageProbeFailure', '.1.3.6.1.4.1.674.10892.1.0.1204', 'Server Amperage Failure: $3', '2', 6, 'Amperage probe has detected a failure value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(298, 'alertAmperageProbeNonRecoverable', '.1.3.6.1.4.1.674.10892.1.0.1205', 'Server Amperage Non-recoverable: $3', '2', 6, 'Amperage probe has detected a non-recoverable value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(300, 'alertChassisIntrusionDetected', '.1.3.6.1.4.1.674.10892.1.0.1254', 'Server Chassis Intrusion Detected: $3', '2', 6, 'Chassis intrusion has been detected.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(313, 'alertACPowerCordNoPowerNonRedundant', '.1.3.6.1.4.1.674.10892.1.0.1501', 'Server AC Cord No Power Non-redundant: $3', '0', 6, 'AC power cord does not have power, and the reduncancy mode\nfor its AC power switch has been set to non-redundant.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(314, 'alertACPowerCordNormal', '.1.3.6.1.4.1.674.10892.1.0.1502', 'Server AC Cord Normal: $3', '0', 6, 'AC power cord has regained power.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(315, 'alertACPowerCordFailure', '.1.3.6.1.4.1.674.10892.1.0.1504', 'Server AC Cord Failure: $3', '2', 6, 'AC power cord has lost power.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(325, 'alertBatteryNormal', '.1.3.6.1.4.1.674.10892.1.0.1702', 'Server Battery Normal: $3', '0', 6, 'Battery has returned to normal.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(326, 'alertBatteryWarning', '.1.3.6.1.4.1.674.10892.1.0.1703', 'Server Battery Warning: $3', '1', 6, 'Battery has detected a warning.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(327, 'alertBatteryFailure', '.1.3.6.1.4.1.674.10892.1.0.1704', 'Server Battery Failure: $3', '2', 6, 'Battery has detected a failure.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(328, 'failoverEvent', '.1.3.6.1.4.1.4413.1.2.3.0.1', '$*', '0', 6, '\nThis trap is generated to indicate that adapter Fail-Over \nevent has occured\nVariables:\n  1: trapAdapterName\n  2: trapTeamName\n  3: trapCauseDirection\n  4: trapAdapterActivityCause\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(344, 'dmtfAlert309', '.1.3.6.1.4.1.674.10890.1.0.309', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Current sensor warning returned to normal\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(345, 'dmtfAlert310', '.1.3.6.1.4.1.674.10890.1.0.310', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Power supply lost redundancy detected\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(346, 'dmtfAlert311', '.1.3.6.1.4.1.674.10890.1.0.311', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Power supply regained redundancy\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(347, 'dmtfAlert312', '.1.3.6.1.4.1.674.10890.1.0.312', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Power supply degraded redundancy detected\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(348, 'dmtfAlert313', '.1.3.6.1.4.1.674.10890.1.0.313', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Power supply degraded redundancy returned to normal\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(349, 'dmtfAlert314', '.1.3.6.1.4.1.674.10890.1.0.314', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Power supply failure detected\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(350, 'dmtfAlert315', '.1.3.6.1.4.1.674.10890.1.0.315', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Power supply failure returned to normal\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(351, 'dmtfAlert320', '.1.3.6.1.4.1.674.10890.1.0.320', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Chassis intrusion detected\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(352, 'dmtfAlert321', '.1.3.6.1.4.1.674.10890.1.0.321', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Chassis intrusion returned to normal\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(353, 'dmtfAlert330', '.1.3.6.1.4.1.674.10890.1.0.330', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'System Up\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(354, 'dmtfAlert323', '.1.3.6.1.4.1.674.10890.1.0.323', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Memory ECC fault detected\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(355, 'dmtfAlert325', '.1.3.6.1.4.1.674.10890.1.0.325', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Lost connection to storage system\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(356, 'dmtfAlert326', '.1.3.6.1.4.1.674.10890.1.0.326', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Established connection to storage system\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(357, 'dmtfAlert327', '.1.3.6.1.4.1.674.10890.1.0.327', 'DMI BASEBRDD Alert Event: Event Data: ($3 on $1)', '2', 6, 'Reestablished connection to storage system\n--NMS trap annotation\nVariables:\n  1: a9999AlertSystem\n  2: a9999AlertGroup\n  3: a9999AlertMessage\n  4: a9999AlertSeverity\n  5: a9999AlertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(363, 'virtualDiskCreated', '.1.3.6.1.4.1.674.10893.1.1.200.0.505', 'Array Manager Event: Virtual Disk created: ', '0', 6, '%s, virtual disk (%s) created.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(364, 'virtualDiskDeleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.506', 'Array Manager Event: Virtual Disk deleted: ', '1', 6, '%s, virtual disk (%s) deleted.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(365, 'virtualDiskConfigChanged', '.1.3.6.1.4.1.674.10893.1.1.200.0.507', 'Array Manager Event: Virtual Disk created: ', '0', 6, '%s, virtual disk (%s) configuration changed from %s to %s\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n  3: oldVDConfigEv\n  4: newVDConfigEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(366, 'virtualDiskFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.508', 'Array Manager Event: Virtual Disk failed: ', '3', 6, '%s, virtual disk (%s) failed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(367, 'virtualDiskDegraded', '.1.3.6.1.4.1.674.10893.1.1.200.0.509', 'Array Manager Event: Virtual Disk degraded: ', '1', 6, '%s, virtual disk (%s) degraded.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(368, 'vdFailedRedundancy', '.1.3.6.1.4.1.674.10893.1.1.200.0.510', 'Array Manager Event: Virtual Disk failed redundancy: ', '1', 6, '%s, virtual disk (%s) is no longer redundant.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(370, 'vdFormatStarted', '.1.3.6.1.4.1.674.10893.1.1.200.0.521', 'Array Manager Event: Virtual Disk format started: ', '1', 6, '%s, virtual disk (%s) format started.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(371, 'adFormatStarted', '.1.3.6.1.4.1.674.10893.1.1.200.0.522', 'Array Manager Event: Array Disk format started: ', '1', 6, '%s, %s format started.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(372, 'vdInitializeStarted', '.1.3.6.1.4.1.674.10893.1.1.200.0.523', 'Array Manager Event: Virtual Disk initialization started: ', '0', 6, '%s, virtual disk (%s) initialization started.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(373, 'adInitializeStarted', '.1.3.6.1.4.1.674.10893.1.1.200.0.524', 'Array Manager Event: Array Disk initialize started: ', '1', 6, '%s, %s initialize started.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(374, 'vdReconfigStarted', '.1.3.6.1.4.1.674.10893.1.1.200.0.525', 'Array Manager Event: Virtual Disk Reconfiguration started: ', '1', 6, '%s, virtual disk (%s) reconfiguration from \n%s stripe size to %s stripe size started.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n  3: oldVDConfigEv\n  4: newVDConfigEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(375, 'vdRebuildStarted', '.1.3.6.1.4.1.674.10893.1.1.200.0.526', 'Array Manager Event: Virtual Disk rebuild started: ', '1', 6, '%s, virtual disk (%s) rebuild started.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(376, 'adRebuildStarted', '.1.3.6.1.4.1.674.10893.1.1.200.0.527', 'Array Manager Event: Array Disk rebuild started: ', '1', 6, '%s, %s rebuild started.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(377, 'adDiagStarted', '.1.3.6.1.4.1.674.10893.1.1.200.0.528', 'Array Manager Event: Array Disk diagnostics started: ', '1', 6, '%s, %s diagnostics started.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(379, 'vdFormatCancelled', '.1.3.6.1.4.1.674.10893.1.1.200.0.530', 'Array Manager Event: Virtual Disk format cancelled: ', '1', 6, '%s, virtual disk (%s) format cancelled.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(380, 'adFormatCancelled', '.1.3.6.1.4.1.674.10893.1.1.200.0.531', 'Array Manager Event: Array Disk format cancelled: ', '1', 6, '%s, %s format cancelled.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(381, 'vdInitializeCancelled', '.1.3.6.1.4.1.674.10893.1.1.200.0.532', 'Array Manager Event: Virtual Disk initialization cancelled: ', '1', 6, '%s, virtual disk (%s) initialization cancelled.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(382, 'adInitializeCancelled', '.1.3.6.1.4.1.674.10893.1.1.200.0.533', 'Array Manager Event: Array Disk initialize cancelled: ', '1', 6, '%s, %s initialize cancelled.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(383, 'vdReconfigCancelled', '.1.3.6.1.4.1.674.10893.1.1.200.0.534', 'Array Manager Event: Virtual Disk Reconfiguration cancelled: ', '1', 6, '%s, virtual disk (%s) reconfiguration cancelled.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(384, 'vdRebuildCancelled', '.1.3.6.1.4.1.674.10893.1.1.200.0.535', 'Array Manager Event: Virtual Disk rebuild cancelled: ', '1', 6, '%s, virtual disk (%s) rebuild cancelled.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(385, 'adRebuildCancelled', '.1.3.6.1.4.1.674.10893.1.1.200.0.536', 'Array Manager Event: Array Disk rebuild cancelled: ', '1', 6, '%s, %s rebuild cancelled.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(386, 'adDiagCancelled', '.1.3.6.1.4.1.674.10893.1.1.200.0.537', 'Array Manager Event: Array Disk diagnostics cancelled: ', '1', 6, '%s, %s diagnostics cancelled.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(388, 'vdFormatFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.539', 'Array Manager Event: Virtual Disk format failed: ', '3', 6, '%s, virtual disk (%s) format failed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(389, 'adFormatFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.540', 'Array Manager Event: Array Disk format failed: ', '3', 6, '%s, %s format failed.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(390, 'vdInitializeFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.541', 'Array Manager Event: Virtual Disk initialization Failed: ', '3', 6, '%s, virtual disk (%s) initialization Failed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(391, 'adInitializeFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.542', 'Array Manager Event: Array Disk initialize failed: ', '3', 6, '%s, %s initialize failed.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(392, 'vdReconfigFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.543', 'Array Manager Event: Virtual Disk Reconfiguration failed: ', '3', 6, '%s, virtual disk (%s) reconfiguration failed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(393, 'vdRebuildFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.544', 'Array Manager Event: Virtual Disk rebuild failed: ', '3', 6, '%s, virtual disk (%s) rebuild failed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(394, 'adRebuildFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.545', 'Array Manager Event: Array Disk rebuild failed: ', '3', 6, '%s, %s rebuild failed.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(395, 'adDiagFailed', '.1.3.6.1.4.1.674.10893.1.1.200.0.546', 'Array Manager Event: Array Disk diagnostics failed: ', '3', 6, '%s, %s diagnostics failed.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(397, 'vdFormatCompleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.548', 'Array Manager Event: Virtual Disk format completed: ', '0', 6, '%s, virtual disk (%s) format completed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(398, 'adFormatCompleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.549', 'Array Manager Event: Array Disk format completed: ', '0', 6, '%s, %s format completed.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(399, 'vdInitializeCompleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.550', 'Array Manager Event: Virtual Disk initialization completed: ', '0', 6, '%s, virtual disk (%s) initialization completed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(400, 'adInitializeCompleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.551', 'Array Manager Event: Array Disk initialize completed: ', '0', 6, '%s, %s initialize completed.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(401, 'vdReconfigCompleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.552', 'Array Manager Event: Virtual Disk Reconfiguration completed: ', '1', 6, '%s, virtual disk (%s) reconfiguration completed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(402, 'vdRebuildCompleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.553', 'Array Manager Event: Virtual Disk rebuild completed: ', '0', 6, '%s, virtual disk (%s) rebuild completed.\nVariables:\n  1: controllerNameEv\n  2: virtualDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(403, 'adRebuildCompleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.554', 'Array Manager Event: Array Disk rebuild completed: ', '0', 6, '%s, %s rebuild completed.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(404, 'adDiagCompleted', '.1.3.6.1.4.1.674.10893.1.1.200.0.555', 'Array Manager Event: Array Disk diagnostics completed: ', '0', 6, '%s, %s diagnostics completed.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(405, 'percPredictiveFailure', '.1.3.6.1.4.1.674.10893.1.1.200.0.570', 'Array Manager Event: Predictive Failure reported: ', '1', 6, '%s, %s reported predictive failure.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(406, 'percSCSISenseData', '.1.3.6.1.4.1.674.10893.1.1.200.0.571', 'Array Manager Event: SCSI sense data: ', '1', 6, '%s, %s SCSI sense failure.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(413, 'perc2SmartFPTExceeded', '.1.3.6.1.4.1.674.10893.1.1.200.0.585', 'Array Manager Event: Smart FPT Exceeded: ', '0', 6, '%s SMART:FPT EXCEEDED on %s.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(414, 'perc2SmartConfigChange', '.1.3.6.1.4.1.674.10893.1.1.200.0.586', 'Array Manager Event: Smart Config Change: ', '1', 6, '%s SMART configuration change on %s.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(415, 'perc2SmartWarning', '.1.3.6.1.4.1.674.10893.1.1.200.0.587', 'Array Manager Event: Smart Warning: ', '1', 6, '%s SMART Warning on %s.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(416, 'perc2SmartWarningTemp', '.1.3.6.1.4.1.674.10893.1.1.200.0.588', 'Array Manager Event: Smart warning temperature: ', '1', 6, '%s SMART Warning temperature on %s.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(417, 'perc2SmartWarningDegraded', '.1.3.6.1.4.1.674.10893.1.1.200.0.589', 'Array Manager Event: Smart warning degraded: ', '1', 6, '%s SMART warning degraded on %s.\nVariables:\n  1: controllerNameEv\n  2: arrayDiskNameEv\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(418, 'perc2SmartFPTExceededTest', '.1.3.6.1.4.1.674.10893.1.1.200.0.590', '    Internal Controller has encountered Strong-ARM processor specific error$1 ', '1', 6, 'Internal Controller has encountered Strong-ARM processor specific error\nVariables:\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(464, 'snTrapChasPwrSupply', '.1.3.6.1.4.1.11.0.1', 'The SNMP trap that is generated when a power supply fails $*', '0', 2, 'The SNMP trap that is generated when a power supply fails\nto operate normally.\nVariables:\n  1: snChasPwrSupplyStatus\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(465, 'snTrapLockedAddressViolation', '.1.3.6.1.4.1.11.0.2', 'The SNMP trap that is generated when more source MAC addresses $*', '0', 2, 'The SNMP trap that is generated when more source MAC addresses\nare received from a port than the maximum number of addresses\nconfigured to that port.\nVariables:\n  1: snSwViolatorPortNumber\n  2: snSwViolatorMacAddress\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(466, 'snTrapOspfIfStateChange', '.1.3.6.1.4.1.11.0.3', 'An ospfIfStateChange trap signifies that there $*', '0', 2, 'An ospfIfStateChange trap signifies that there\nhas been a change in the state of a non-virtual\nOSPF interface. This trap should  be  generated\nwhen  the interface state regresses (e.g., goes\nfrom Dr to Down) or progresses  to  a  terminal\nstate  (i.e.,  Point-to-Point, DR Other, Dr, or\nBackup).\nVariables:\n  1: snOspfRouterId\n  2: snOspfIfStatusIpAddress\n  3: snOspfIfStatusState\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(467, 'snTrapOspfVirtIfStateChange', '.1.3.6.1.4.1.11.0.4', 'An ospfIfStateChange trap signifies that there $*', '0', 2, 'An ospfIfStateChange trap signifies that there\nhas  been a change in the state of an OSPF vir-\ntual interface.\nThis trap should be generated when  the  inter-\nface  state  regresses  (e.g., goes from Point-\nto-Point to Down) or progresses to  a  terminal\nstate (i.e., Point-to-Point).\nVariables:\n  1: snOspfRouterId\n  2: snOspfVirtIfStatusAreaID\n  3: snOspfVirtIfStatusNeighbor\n  4: snOspfVirtIfStatusState\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(468, 'snOspfNbrStateChange', '.1.3.6.1.4.1.11.0.5', 'An  ospfNbrStateChange  trap  signifies   that $*', '0', 2, 'An  ospfNbrStateChange  trap  signifies   that\nthere  has been a change in the state of a non-\nvirtual OSPF neighbor.   This  trap  should  be\ngenerated  when  the  neighbor  state regresses\n(e.g., goes from Attempt or Full  to  1-Way  or\nDown)  or progresses to a terminal state (e.g.,\n2-Way or Full).  When an  neighbor  transitions\nfrom  or  to Full on non-broadcast multi-access\nand broadcast networks, the trap should be gen-\nerated  by the designated router.  A designated\nrouter transitioning to Down will be  noted  by\nospfIfStateChange.\nVariables:\n  1: snOspfRouterId\n  2: snOspfNbrIpAddr\n  3: snOspfNbrRtrId\n  4: snOspfNbrState\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(469, 'snOspfVirtNbrStateChange', '.1.3.6.1.4.1.11.0.6', 'An ospfIfStateChange trap signifies that there $*', '0', 2, 'An ospfIfStateChange trap signifies that there\nhas  been a change in the state of an OSPF vir-\ntual neighbor.  This trap should  be  generated\nwhen  the  neighbor state regresses (e.g., goes\nfrom Attempt or  Full  to  1-Way  or  Down)  or\nprogresses to a terminal state (e.g., Full).\nVariables:\n  1: snOspfRouterId\n  2: snOspfVirtNbrArea\n  3: snOspfVirtNbrRtrId\n  4: snOspfVirtNbrState\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(470, 'snOspfIfConfigError', '.1.3.6.1.4.1.11.0.7', 'An ospfIfConfigError  trap  signifies  that  a $*', '0', 2, 'An ospfIfConfigError  trap  signifies  that  a\npacket  has  been received on a non-virtual in-\nterface  from  a  router  whose   configuration\nparameters  conflict  with this router''s confi-\nguration parameters.  Note that the  event  op-\ntionMismatch  should  cause  a  trap only if it\nprevents an adjacency from forming.\nVariables:\n  1: snOspfRouterId\n  2: snOspfIfStatusIpAddress\n  3: snOspfPacketSrc\n  4: snOspfConfigErrorType\n  5: snOspfPacketType\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(471, 'snOspfVirtIfConfigError', '.1.3.6.1.4.1.11.0.8', 'An ospfConfigError trap signifies that a pack- $*', '0', 2, 'An ospfConfigError trap signifies that a pack-\net  has  been  received  on a virtual interface\nfrom a router  whose  configuration  parameters\nconflict   with   this  router''s  configuration\nparameters.  Note that the event optionMismatch\nshould  cause a trap only if it prevents an ad-\njacency from forming.\nVariables:\n  1: snOspfRouterId\n  2: snOspfVirtIfStatusAreaID\n  3: snOspfVirtIfStatusNeighbor\n  4: snOspfConfigErrorType\n  5: snOspfPacketType\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(472, 'snOspfIfAuthFailure', '.1.3.6.1.4.1.11.0.9', 'An ospfIfAuthFailure  trap  signifies  that  a $*', '0', 2, 'An ospfIfAuthFailure  trap  signifies  that  a\npacket  has  been received on a non-virtual in-\nterface from a router whose authentication  key\nor  authentication  type  conflicts  with  this\nrouter''s authentication key  or  authentication\ntype.\nVariables:\n  1: snOspfRouterId\n  2: snOspfIfStatusIpAddress\n  3: snOspfPacketSrc\n  4: snOspfConfigErrorType\n  5: snOspfPacketType\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(473, 'snOspfVirtIfAuthFailure', '.1.3.6.1.4.1.11.0.10', 'An ospfVirtIfAuthFailure trap signifies that a $*', '0', 2, 'An ospfVirtIfAuthFailure trap signifies that a\npacket has been received on a virtual interface\nfrom a router whose authentication key  or  au-\nthentication  type conflicts with this router''s\nauthentication key or authentication type.\nVariables:\n  1: snOspfRouterId\n  2: snOspfVirtIfStatusAreaID\n  3: snOspfVirtIfStatusNeighbor\n  4: snOspfConfigErrorType\n  5: snOspfPacketType\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(474, 'snOspfIfRxBadPacket', '.1.3.6.1.4.1.11.0.11', 'An ospfIfRxBadPacket trap  signifies  that  an $*', '0', 2, 'An ospfIfRxBadPacket trap  signifies  that  an\nOSPF  packet has been received on a non-virtual\ninterface that cannot be parsed.\nVariables:\n  1: snOspfRouterId\n  2: snOspfIfStatusIpAddress\n  3: snOspfPacketSrc\n  4: snOspfPacketType\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(475, 'snOspfVirtIfRxBadPacket', '.1.3.6.1.4.1.11.0.12', 'An ospfRxBadPacket trap signifies that an OSPF $*', '0', 2, 'An ospfRxBadPacket trap signifies that an OSPF\npacket has been received on a virtual interface\nthat cannot be parsed.\nVariables:\n  1: snOspfRouterId\n  2: snOspfVirtIfStatusAreaID\n  3: snOspfVirtIfStatusNeighbor\n  4: snOspfPacketType\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(476, 'snOspfTxRetransmit', '.1.3.6.1.4.1.11.0.13', 'An ospfTxRetransmit  trap  signifies  than  an $*', '0', 2, 'An ospfTxRetransmit  trap  signifies  than  an\nOSPF  packet  has  been retransmitted on a non-\nvirtual interface.  All packets that may be re-\ntransmitted  are associated with an LSDB entry.\nThe LS type, LS ID, and Router ID are  used  to\nidentify the LSDB entry.\nVariables:\n  1: snOspfRouterId\n  2: snOspfIfStatusIpAddress\n  3: snOspfNbrRtrId\n  4: snOspfPacketType\n  5: snOspfLsdbType\n  6: snOspfLsdbLsId\n  7: snOspfLsdbRouterId\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(477, 'ospfVirtIfTxRetransmit', '.1.3.6.1.4.1.11.0.14', 'An ospfTxRetransmit  trap  signifies  than  an $*', '0', 2, 'An ospfTxRetransmit  trap  signifies  than  an\nOSPF packet has been retransmitted on a virtual\ninterface.  All packets that may be retransmit-\nted  are  associated with an LSDB entry. The LS\ntype, LS ID, and Router ID are used to identify\nthe LSDB entry.\nVariables:\n  1: snOspfRouterId\n  2: snOspfVirtIfStatusAreaID\n  3: snOspfVirtIfStatusNeighbor\n  4: snOspfPacketType\n  5: snOspfLsdbType\n  6: snOspfLsdbLsId\n  7: snOspfLsdbRouterId\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(478, 'snOspfOriginateLsa', '.1.3.6.1.4.1.11.0.15', 'An ospfOriginateLsa trap signifies that a  new $*', '0', 2, 'An ospfOriginateLsa trap signifies that a  new\nLSA  has  been originated by this router.  This\ntrap should not be invoked for simple refreshes\nof  LSAs  (which happesn every 30 minutes), but\ninstead will only be invoked  when  an  LSA  is\n(re)originated due to a topology change.  Addi-\ntionally, this trap does not include LSAs  that\nare  being  flushed  because  they have reached\nMaxAge.\nVariables:\n  1: snOspfRouterId\n  2: snOspfLsdbAreaId\n  3: snOspfLsdbType\n  4: snOspfLsdbLsId\n  5: snOspfLsdbRouterId\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(479, 'snOspfMaxAgeLsa', '.1.3.6.1.4.1.11.0.16', 'An ospfMaxAgeLsa trap signifies  that  one  of $*', '0', 2, 'An ospfMaxAgeLsa trap signifies  that  one  of\nthe LSA in the router''s link-state database has\naged to MaxAge.\nVariables:\n  1: snOspfRouterId\n  2: snOspfLsdbAreaId\n  3: snOspfLsdbType\n  4: snOspfLsdbLsId\n  5: snOspfLsdbRouterId\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(480, 'snOspfLsdbOverflow', '.1.3.6.1.4.1.11.0.17', 'An ospfLsdbOverflow trap  signifies  that  the $*', '0', 2, 'An ospfLsdbOverflow trap  signifies  that  the\nnumber of LSAs in the router''s link-state data-\nbase has exceeded ospfExtLsdbLimit.\nVariables:\n  1: snOspfRouterId\n  2: snOspfExtLsdbLimit\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(481, 'snOspfLsdbApproachingOverflow', '.1.3.6.1.4.1.11.0.18', 'An ospfLsdbApproachingOverflow trap  signifies $*', '0', 2, 'An ospfLsdbApproachingOverflow trap  signifies\nthat  the  number of LSAs in the router''s link-\nstate database has exceeded ninety  percent  of\nospfExtLsdbLimit.\nVariables:\n  1: snOspfRouterId\n  2: snOspfExtLsdbLimit\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(482, 'snTrapL4MaxSessionLimitReached', '.1.3.6.1.4.1.11.0.19', 'The SNMP trap that is generated when the maximum number $*', '0', 2, 'The SNMP trap that is generated when the maximum number\nof connections reached.\nVariables:\n  1: snL4MaxSessionLimit\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(483, 'snTrapL4TcpSynLimitReached', '.1.3.6.1.4.1.11.0.20', 'The SNMP trap that is generated when the number of TCP $*', '0', 2, 'The SNMP trap that is generated when the number of TCP\nSYN limits reached.\nVariables:\n  1: snL4TcpSynLimit\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(484, 'snTrapL4RealServerUp', '.1.3.6.1.4.1.11.0.21', 'The SNMP trap that is generated when the load balancing $*', '0', 2, 'The SNMP trap that is generated when the load balancing\nreal server is up.\nVariables:\n  1: snL4TrapRealServerIP\n  2: snL4TrapRealServerName\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(485, 'snTrapL4RealServerDown', '.1.3.6.1.4.1.11.0.22', 'The SNMP trap that is generated when the load balancing $*', '0', 2, 'The SNMP trap that is generated when the load balancing\nreal server is down.\nVariables:\n  1: snL4TrapRealServerIP\n  2: snL4TrapRealServerName\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(486, 'snTrapL4RealServerPortUp', '.1.3.6.1.4.1.11.0.23', 'The SNMP trap that is generated when the load balancing $*', '0', 2, 'The SNMP trap that is generated when the load balancing\nreal server TCP port is up.\nVariables:\n  1: snL4TrapRealServerIP\n  2: snL4TrapRealServerName\n  3: snL4TrapRealServerPort\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(487, 'snTrapL4RealServerPortDown', '.1.3.6.1.4.1.11.0.24', 'The SNMP trap that is generated when the load balancing $*', '0', 2, 'The SNMP trap that is generated when the load balancing\nreal server TCP port is down.\nVariables:\n  1: snL4TrapRealServerIP\n  2: snL4TrapRealServerName\n  3: snL4TrapRealServerPort\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(488, 'snTrapL4RealServerMaxConnectionLimitReached', '.1.3.6.1.4.1.11.0.25', 'The SNMP trap that is generated when the real server reaches $*', '0', 2, 'The SNMP trap that is generated when the real server reaches\nmaximum number of connections.\nVariables:\n  1: snL4TrapRealServerIP\n  2: snL4TrapRealServerName\n  3: snL4TrapRealServerCurConnections\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(489, 'snTrapL4BecomeStandby', '.1.3.6.1.4.1.11.0.26', 'The SNMP trap that is generated when the server load $*', '0', 2, 'The SNMP trap that is generated when the server load\nbalancing switch changes state from active to standby.\nVariables:\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(490, 'snTrapL4BecomeActive', '.1.3.6.1.4.1.11.0.27', 'The SNMP trap that is generated when the server load $*', '0', 2, 'The SNMP trap that is generated when the server load\nbalancing switch changes state from standby to active.\nVariables:\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(491, 'snTrapModuleInserted', '.1.3.6.1.4.1.11.0.28', 'The SNMP trap that is generated when a module was inserted $*', '0', 2, 'The SNMP trap that is generated when a module was inserted\nto the chassis during system running.\nVariables:\n  1: snAgentBrdIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(492, 'snTrapModuleRemoved', '.1.3.6.1.4.1.11.0.29', 'The SNMP trap that is generated when a module was removed $*', '0', 2, 'The SNMP trap that is generated when a module was removed\nfrom the chassis during system running.\nVariables:\n  1: snAgentBrdIndex\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(493, 'snTrapChasPwrSupplyFailed', '.1.3.6.1.4.1.11.0.30', 'The SNMP trap that is generated when a power supply fails $*', '0', 2, 'The SNMP trap that is generated when a power supply fails\nto operate normally.\nVariables:\n  1: snChasPwrSupplyIndex\n  2: snChasPwrSupplyDescription\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(494, 'snTrapChasFanFailed', '.1.3.6.1.4.1.11.0.31', 'The SNMP trap that is generated when a fan fails $*', '0', 2, 'The SNMP trap that is generated when a fan fails\nto operate normally.\nVariables:\n  1: snChasFanIndex\n  2: snChasFanDescription\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(495, 'snTrapLockedAddressViolation2', '.1.3.6.1.4.1.11.0.32', 'The SNMP trap that is generated when more source MAC addresses $*', '0', 2, 'The SNMP trap that is generated when more source MAC addresses\nare received from a port than the maximum number of addresses\nconfigured to that port.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(496, 'snTrapFsrpIfStateChange', '.1.3.6.1.4.1.11.0.33', 'The SNMP trap that is generated when a FSRP routing device $*', '0', 2, 'The SNMP trap that is generated when a FSRP routing device\nchanged state from active to standby or vice-versa.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(497, 'snTrapVrrpIfStateChange', '.1.3.6.1.4.1.11.0.34', 'The SNMP trap that is generated when a VRRP routing device $*', '0', 2, 'The SNMP trap that is generated when a VRRP routing device\nchanged state from master to backup or vice-versa.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(498, 'snTrapMgmtModuleRedunStateChange', '.1.3.6.1.4.1.11.0.35', 'The SNMP trap that is generated when the management module $*', '0', 2, 'The SNMP trap that is generated when the management module\nchanges redundancy state.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(499, 'snTrapTemperatureWarning', '.1.3.6.1.4.1.11.0.36', 'The SNMP trap that is generated when the actual temperature $*', '0', 2, 'The SNMP trap that is generated when the actual temperature\nreading is above the warning temperature threshold.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(500, 'snTrapAccessListDeny', '.1.3.6.1.4.1.11.0.37', 'The SNMP trap that is generated when a packet was denied $*', '0', 2, 'The SNMP trap that is generated when a packet was denied\nby an access list.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(501, 'snTrapMacFilterDeny', '.1.3.6.1.4.1.11.0.38', 'The SNMP trap that is generated when a packet was denied $*', '0', 2, 'The SNMP trap that is generated when a packet was denied\nby a MAC address filter.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(502, 'snTrapL4GslbRemoteUp', '.1.3.6.1.4.1.11.0.39', 'The SNMP trap that is generated when the connection to the remote SI is established. $*', '0', 2, 'The SNMP trap that is generated when the connection to the remote SI is established.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(503, 'snTrapL4GslbRemoteDown', '.1.3.6.1.4.1.11.0.40', 'The SNMP trap that is generated when the connection to the remote SI is down. $*', '0', 2, 'The SNMP trap that is generated when the connection to the remote SI is down.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(504, 'snTrapL4GslbRemoteControllerUp', '.1.3.6.1.4.1.11.0.41', 'The SNMP trap that is generated when the connection to the GSLB SI is established. $*', '0', 2, 'The SNMP trap that is generated when the connection to the GSLB SI is established.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(505, 'snTrapL4GslbRemoteControllerDown', '.1.3.6.1.4.1.11.0.42', 'The SNMP trap that is generated when the connection to the GSLB SI is down. $*', '0', 2, 'The SNMP trap that is generated when the connection to the GSLB SI is down.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(506, 'snTrapL4GslbHealthCheckIpUp', '.1.3.6.1.4.1.11.0.43', 'The SNMP trap that is generated when GSLB health check for an address transitions $*', '0', 2, 'The SNMP trap that is generated when GSLB health check for an address transitions\nfrom down to active state.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(507, 'snTrapL4GslbHealthCheckIpDown', '.1.3.6.1.4.1.11.0.44', 'The SNMP trap that is generated when GSLB health check for an address transitions $*', '0', 2, 'The SNMP trap that is generated when GSLB health check for an address transitions\nfrom active to down state.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(508, 'snTrapL4GslbHealthCheckIpPortUp', '.1.3.6.1.4.1.11.0.45', 'The SNMP trap that is generated when a given port for a health check address is up. $*', '0', 2, 'The SNMP trap that is generated when a given port for a health check address is up.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(509, 'snTrapL4GslbHealthCheckIpPortDown', '.1.3.6.1.4.1.11.0.46', 'The SNMP trap that is generated when a given port for a health check address is down. $*', '0', 2, 'The SNMP trap that is generated when a given port for a health check address is down.\nVariables:\n  1: snAgGblTrapMessage\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(512, 'tapeAlertTrap3', '.1.3.6.1.4.1.11.2.3.9.7.1.0.3', 'HP TapeAlert #3: Hba$1:Channel$2:ScsiID$3 Tape Device Has Detected Read or Write Data Error', '1', 2, 'The operation has stopped because an error has\noccured while reading or writing data which the\ndrive cannot correct.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(513, 'tapeAlertTrap4', '.1.3.6.1.4.1.11.2.3.9.7.1.0.4', 'HP TapeAlert #4: Hba$1:Channel$2:ScsiID$3 Tape Device Has Detected Faulty Media [$4]', '2', 2, 'Your data is at risk:\n1. Copy any data you require from this tape\n2. Do not use this tape again\n3. Restart the operation with a different tape\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n  4: currentMedia\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(514, 'tapeAlertTrap5', '.1.3.6.1.4.1.11.2.3.9.7.1.0.5', 'HP TapeAlert #5: Hba$1:Channel$2:ScsiID$3 Tape Device May Have Read Fault - Call Helpline', '2', 2, 'The tape is damaged or the drive is faulty.\nCall the tape drive supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(515, 'tapeAlertTrap6', '.1.3.6.1.4.1.11.2.3.9.7.1.0.6', 'HP TapeAlert #6: Hba$1:Channel$2:ScsiID$3 Tape Device May Have Write Fault - Call Helpline', '2', 2, 'The tape is from a faulty batch or the tape drive\nis faulty:\n1. Use a good tape to test the drive.\n2. If the problem persists, call the tape drive \nsupplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(516, 'tapeAlertTrap7', '.1.3.6.1.4.1.11.2.3.9.7.1.0.7', 'HP TapeAlert #7: Hba$1:Channel$2:ScsiID$3 Tape Device Has Detected Worn Out Media [$4]', '1', 2, 'The tape cartridge has reached the end of its \ncalculated useful life:\n1. Copy any data you need to another tape\n2. Discard the old tape.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n  4: currentMedia\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(517, 'tapeAlertTrap8', '.1.3.6.1.4.1.11.2.3.9.7.1.0.8', 'HP TapeAlert #8: Hba$1:Channel$2:ScsiID$3 Media Loaded In Tape Device Is Not Data Grade', '1', 2, 'The tape cartridge is not data-grade. \nAny data you back up to the tape is at risk. \nReplace the cartridge with a data-grade tape.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(518, 'tapeAlertTrap9', '.1.3.6.1.4.1.11.2.3.9.7.1.0.9', 'HP TapeAlert #9: Hba$1:Channel$2:ScsiID$3 Tape Device Attempted Backup to Write-Protected Tape', '2', 2, 'You are trying to write to a write-protected \ncartridge. Remove the write-protection or use \nanother tape.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(533, 'tapeAlertTrap29', '.1.3.6.1.4.1.11.2.3.9.7.1.0.29', 'HP TapeAlert #29: Hba$1:Channel$2:ScsiID$3 Tape Device Requires Preventative Maintenance', '1', 2, 'Preventive maintenance of the tape drive is required.\nCheck the tape drive users manual for device specific \npreventive maintenance tasks or call the tape drive \nsupplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(534, 'tapeAlertTrap30', '.1.3.6.1.4.1.11.2.3.9.7.1.0.30', 'HP TapeAlert #30: Hba$1:Channel$2:ScsiID$3 Tape Device Has Hardware Fault - Reset Drive', '2', 2, 'The tape drive has a hardware fault:\n1. Eject the tape or magazine.\n2. Reset the drive.\n3. Restart the operation.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(535, 'tapeAlertTrap31', '.1.3.6.1.4.1.11.2.3.9.7.1.0.31', 'HP TapeAlert #31: Hba$1:Channel$2:ScsiID$3 Tape Device Has Hardware Fault - Call Helpline', '2', 2, 'The tape drive has a hardware fault:\n1. Turn the tape drive off and then on again.\n2. Restart the operation.\n3. If the problem persists, call the tape \ndrive supplier helpline.\nCheck the tape drive users manual for device specific \ninstructions on turning the device power on and off.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(536, 'tapeAlertTrap32', '.1.3.6.1.4.1.11.2.3.9.7.1.0.32', 'HP TapeAlert #32: Hba$1:Channel$2:ScsiID$3 Tape Device Has Host Interface Fault', '1', 2, 'The tape drive has a problem with the host \ninterface:\n1. Check the cables and cable connections.\n2. Restart the operation.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(537, 'tapeAlertTrap33', '.1.3.6.1.4.1.11.2.3.9.7.1.0.33', 'HP TapeAlert #33: Hba$1:Channel$2:ScsiID$3 Tape Device Has Eject Media Request', '2', 2, 'The operation has failed:\n1. Eject the tape or magazine.\n2. Insert the tape or magazine again.\n3. Restart the operation.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(538, 'tapeAlertTrap34', '.1.3.6.1.4.1.11.2.3.9.7.1.0.34', 'HP TapeAlert #34: Hba$1:Channel$2:ScsiID$3 Tape Device Has Firmware Download Fault', '1', 2, 'The firmware download has failed because you \nhave tried to use the incorrect firmware for \nthis tape drive.\nObtain the correct firmware and try again.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(539, 'tapeAlertTrap35', '.1.3.6.1.4.1.11.2.3.9.7.1.0.35', 'HP TapeAlert #35: Hba$1:Channel$2:ScsiID$3 Tape Device Humidity Specification Exceeded', '1', 2, 'Environmental conditions inside the tape drive are \nexceeding the humidity specifications\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(540, 'tapeAlertTrap36', '.1.3.6.1.4.1.11.2.3.9.7.1.0.36', 'HP TapeAlert #36: Hba$1:Channel$2:ScsiID$3 Tape Device Temperature Specification Exceeded', '1', 2, 'Environmental conditions inside the tape drive are \nexceeding the temperature specifications\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(541, 'tapeAlertTrap37', '.1.3.6.1.4.1.11.2.3.9.7.1.0.37', 'HP TapeAlert #37: Hba$1:Channel$2:ScsiID$3 Tape Device Voltage Specification Exceeded', '1', 2, 'The voltage supply to the tape drive exceeds specifications\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(542, 'tapeAlertTrap38', '.1.3.6.1.4.1.11.2.3.9.7.1.0.38', 'HP TapeAlert #38: Hba$1:Channel$2:ScsiID$3 Tape Device Predicted to Fail - Call Helpline', '2', 2, 'A hardware failure of the tape drive is predicted. \nCall the tape drive supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(543, 'tapeAlertTrap39', '.1.3.6.1.4.1.11.2.3.9.7.1.0.39', 'HP TapeAlert #39: Hba$1:Channel$2:ScsiID$3 Tape Device May Have Hardware Fault - Run Diagnostics', '1', 2, 'The tape drive may have a hardware fault.\nRun extended diagnostics to verify and diagnose the problem.\nCheck the tape drive users manual for device specific \ninstructions on running extended diagnostic tests.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(544, 'tapeAlertTrap40', '.1.3.6.1.4.1.11.2.3.9.7.1.0.40', 'HP TapeAlert #40: Hba$1:Channel$2:ScsiID$3 Tape Device Has Autoloader Communications Fault', '2', 2, 'The changer mechanism is having difficulty \ncommunicating with the tape drive:\n1. Turn the autoloader off then on.\n2. Restart the operation.\n3. If problem persists, call the tape drive \nsupplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(545, 'tapeAlertTrap41', '.1.3.6.1.4.1.11.2.3.9.7.1.0.41', 'HP TapeAlert #41: Hba$1:Channel$2:ScsiID$3 Tape Device Detects Stray Tape In Autoloader', '2', 2, 'A tape has been left in the autoloader by a \nprevious hardware fault:\n1. Insert an empty magazine to clear the fault.\n2. If the fault does not clear, turn the autoloader \noff and then on again.\n3. If the problem persists, call the tape drive \nsupplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(546, 'tapeAlertTrap42', '.1.3.6.1.4.1.11.2.3.9.7.1.0.42', 'HP TapeAlert #42: Hba$1:Channel$2:ScsiID$3 Tape Device Has Autoloader Mechanism Fault', '1', 2, 'There is a problem with the autoloader mechanism.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(547, 'tapeAlertTrap43', '.1.3.6.1.4.1.11.2.3.9.7.1.0.43', 'HP TapeAlert #43: Hba$1:Channel$2:ScsiID$3 Tape Device Has Autoloader Door Open', '2', 2, 'The operation has failed because the autoloader \ndoor is open:\n1. Clear any obstructions from the autoloader door.\n2. Eject the magazine and then insert it again.\n3. If the fault does not clear, turn the autoloader \noff and then on again.\n4. If the problem persists, call the tape drive \nsupplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(548, 'tapeAlertTrap44', '.1.3.6.1.4.1.11.2.3.9.7.1.0.44', 'HP TapeAlert #44: Hba$1:Channel$2:ScsiID$3 Autoloader Has Hardware Fault - Call Helpline', '2', 2, 'The autoloader has a hardware fault:\n1. Turn the autoloader off and then on again.\n2. Restart the operation.\n3. If the problem persists, call the tape drive supplier helpline.\nCheck the autoloader users manual for device specific \ninstructions on turning the device power on and off.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(549, 'tapeAlertTrap45', '.1.3.6.1.4.1.11.2.3.9.7.1.0.45', 'HP TapeAlert #45: Hba$1:Channel$2:ScsiID$3 Autoloader Cannot Operate Without Magazine', '2', 2, 'The autoloader cannot operate without the magazine.\n1. Insert the magazine into the autoloader\n2. Restart the operation.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(550, 'tapeAlertTrap46', '.1.3.6.1.4.1.11.2.3.9.7.1.0.46', 'HP TapeAlert #46: Hba$1:Channel$2:ScsiID$3 Autoloader Predicted to Fail - Call Helpline', '1', 2, 'A hardware failure of the changer mechanism is predicted. \nCall the tape drive supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(557, 'tapeAlertTrap262', '.1.3.6.1.4.1.11.2.3.9.7.1.0.262', 'HP TapeAlert #262: Hba$1:Channel$2:ScsiID$3 Library Predicted to Fail - Call Helpline', '1', 2, 'A hardware failure of the library is predicted. \nCall the library supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(559, 'tapeAlertTrap264', '.1.3.6.1.4.1.11.2.3.9.7.1.0.264', 'HP TapeAlert #264: Hba$1:Channel$2:ScsiID$3 Library Humidity Specification Exceeded', '2', 2, 'General environmental conditions inside the library have \nexceeded the humidity specifications.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(561, 'tapeAlertTrap266', '.1.3.6.1.4.1.11.2.3.9.7.1.0.266', 'HP TapeAlert #266: Hba$1:Channel$2:ScsiID$3 Library Voltage Specification Exceeded', '2', 2, 'he voltage supply to the library exceeds specifications.  \nThere is a potential problem with the power supply or failure \nof a redundant power supply\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(562, 'tapeAlertTrap267', '.1.3.6.1.4.1.11.2.3.9.7.1.0.267', 'HP TapeAlert #267: Hba$1:Channel$2:ScsiID$3 Library Detects Stray Tape In Drive', '2', 2, 'A cartridge has been left in a drive inside the library by a \nprevious hardware fault:\n1. Insert an empty magazine to clear the fault.\n2. If the fault does not clear, turn the library off and then on again.\n3. If the problem persists, call the library supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(563, 'tapeAlertTrap268', '.1.3.6.1.4.1.11.2.3.9.7.1.0.268', 'HP TapeAlert #268: Hba$1:Channel$2:ScsiID$3 Library Has Problems Picking Cartridge from Slot', '1', 2, 'There is a potential problem with a drive ejecting cartridges short or \nwith the library mechanism picking a cartridge from a slot.\n1. No action needs to be taken at this time. \n2. If the problem persists, call the library supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(564, 'tapeAlertTrap269', '.1.3.6.1.4.1.11.2.3.9.7.1.0.269', 'HP TapeAlert #269: Hba$1:Channel$2:ScsiID$3 Library Has Problems Placing Cartridge into Slot', '1', 2, 'There is a potential problem with the library mechanism \nplacing a cartridge into a slot\n1. No action needs to be taken at this time. \n2. If the problem persists, call the library supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(565, 'tapeAlertTrap270', '.1.3.6.1.4.1.11.2.3.9.7.1.0.270', 'HP TapeAlert #270: Hba$1:Channel$2:ScsiID$3 Library Has Problems Loading Cartridge into Drive', '1', 2, 'There is a potential problem with a drive or the library \nmechanism loading cartridges, or an incompatible cartridge.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(566, 'tapeAlertTrap271', '.1.3.6.1.4.1.11.2.3.9.7.1.0.271', 'HP TapeAlert #271: Hba$1:Channel$2:ScsiID$3 Library Door is Open', '2', 2, 'The operation has failed because the library door is open:\n1. Clear any obstructions from the library door.\n2. Close the library door.\n3. If the problem persists, call the library supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(567, 'tapeAlertTrap272', '.1.3.6.1.4.1.11.2.3.9.7.1.0.272', 'HP TapeAlert #272: Hba$1:Channel$2:ScsiID$3 Library Has Mechanical Fault with Mailslot', '2', 2, 'There is a mechanical problem with the \nlibrary media import/export mailslot.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(568, 'tapeAlertTrap273', '.1.3.6.1.4.1.11.2.3.9.7.1.0.273', 'HP TapeAlert #273: Hba$1:Channel$2:ScsiID$3 Library Cannot Operate Without Magazine', '2', 2, 'The library cannot operate without the magazine.\n1. Insert the magazine into the library\n2. Restart the operation.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(569, 'tapeAlertTrap274', '.1.3.6.1.4.1.11.2.3.9.7.1.0.274', 'HP TapeAlert #274: Hba$1:Channel$2:ScsiID$3 Library Security Has Been Compromised', '1', 2, 'Library security has been compromised\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(570, 'tapeAlertTrap275', '.1.3.6.1.4.1.11.2.3.9.7.1.0.275', 'HP TapeAlert #275: Hba$1:Channel$2:ScsiID$3 Library Security Mode Changed', '0', 2, 'The security mode of the library has been changed.\nThe library has either been put into secure mode, \nor the library has exited the secure mode.\nThis is for information purposes only. No action is required.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(571, 'tapeAlertTrap276', '.1.3.6.1.4.1.11.2.3.9.7.1.0.276', 'HP TapeAlert #276: Hba$1:Channel$2:ScsiID$3 Library Manually Turned Offline', '0', 2, 'The library has been manually turned offline and is unavailable for use.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(572, 'tapeAlertTrap277', '.1.3.6.1.4.1.11.2.3.9.7.1.0.277', 'HP TapeAlert #277: Hba$1:Channel$2:ScsiID$3 Library Drive Turned Offline', '0', 2, 'A drive inside the library has been taken offline.\nThis is for information purposes only. No action is required.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(573, 'tapeAlertTrap278', '.1.3.6.1.4.1.11.2.3.9.7.1.0.278', 'HP TapeAlert #278: Hba$1:Channel$2:ScsiID$3 Library Has Problems Reading Barcode Labels', '1', 2, 'There is a potential problem with the barcode label or \nthe scanner hardware in the library mechanism.\n1. No action needs to be taken at this time. \n2. If the problem persists, call the library supplier helpline.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(574, 'tapeAlertTrap279', '.1.3.6.1.4.1.11.2.3.9.7.1.0.279', 'HP TapeAlert #279: Hba$1:Channel$2:ScsiID$3 Library Inventory is Inconsistent', '2', 2, 'The library has detected a inconsistency in its inventory.\n1. Redo the library inventory to correct inconsistency.\n2. Restart the operation \nCheck the applications users manual or the hardware users \nmanual for specific instructions on redoing the library inventory.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(575, 'tapeAlertTrap280', '.1.3.6.1.4.1.11.2.3.9.7.1.0.280', 'HP TapeAlert #280: Hba$1:Channel$2:ScsiID$3 Invalid Library Operation Attempted', '1', 2, 'A library operation has been attempted that is invalid at this time.\nVariables:\n  1: hbaNumber\n  2: hbaChannel\n  3: driveScsiID\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(576, 'alertSystemUp', '.1.3.6.1.4.1.674.10892.1.0.1001', 'Server Administrator Startup Complete: $3', '0', 6, 'Server Administrator has completed its initialization.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(577, 'alertThermalShutdown', '.1.3.6.1.4.1.674.10892.1.0.1004', 'Server Thermal Shutdown: $3', '2', 6, 'Thermal shutdown protection has been initiated.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(578, 'alertTemperatureProbeNormal', '.1.3.6.1.4.1.674.10892.1.0.1052', 'Server Temperature Normal: $3', '0', 6, 'Temperature probe has returned to a normal value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(579, 'alertTemperatureProbeWarning', '.1.3.6.1.4.1.674.10892.1.0.1053', 'Server Temperature Warning: $3', '1', 6, 'Temperature probe has detected a warning value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(580, 'alertTemperatureProbeFailure', '.1.3.6.1.4.1.674.10892.1.0.1054', 'Server Temperature Failure: $3', '2', 6, 'Temperature probe has detected a failure value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(581, 'alertTemperatureProbeNonRecoverable', '.1.3.6.1.4.1.674.10892.1.0.1055', 'Server Temperature Non-recoverable: $3', '2', 6, 'Temperature probe has detected a non-recoverable value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(582, 'alertCoolingDeviceNormal', '.1.3.6.1.4.1.674.10892.1.0.1102', 'Server Cooling Device Normal: $3', '0', 6, 'Cooling device sensor has returned to a normal value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(583, 'alertCoolingDeviceWarning', '.1.3.6.1.4.1.674.10892.1.0.1103', 'Server Cooling Device Warning: $3', '1', 6, 'Cooling device sensor has detected a warning value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(584, 'alertCoolingDeviceFailure', '.1.3.6.1.4.1.674.10892.1.0.1104', 'Server Cooling Device Failure: $3', '2', 6, 'Cooling device sensor has detected a failure value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(585, 'alertCoolingDeviceNonRecoverable', '.1.3.6.1.4.1.674.10892.1.0.1105', 'Server Cooling Device Non-recoverable: $3', '2', 6, 'Cooling device sensor has detected a non-recoverable value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(586, 'alertVoltageProbeNormal', '.1.3.6.1.4.1.674.10892.1.0.1152', 'Server Voltage Normal: $3', '0', 6, 'Voltage probe has returned to a normal value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(587, 'alertVoltageProbeWarning', '.1.3.6.1.4.1.674.10892.1.0.1153', 'Server Voltage Warning: $3', '1', 6, 'Voltage probe has detected a warning value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(588, 'alertVoltageProbeFailure', '.1.3.6.1.4.1.674.10892.1.0.1154', 'Server Voltage Failure: $3', '2', 6, 'Voltage probe has detected a failure value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(589, 'alertVoltageProbeNonRecoverable', '.1.3.6.1.4.1.674.10892.1.0.1155', 'Server Voltage Non-recoverable: $3', '2', 6, 'Voltage probe has detected a non-recoverable value.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(590, 'alertChassisIntrusionNormal', '.1.3.6.1.4.1.674.10892.1.0.1252', 'Server Chassis Intrusion Normal: $3', '0', 6, 'Chassis intrusion has returned to normal.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(591, 'alertRedundancyNormal', '.1.3.6.1.4.1.674.10892.1.0.1304', 'Server Redundancy Normal: $3', '0', 6, 'Redundancy has returned to normal.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(592, 'alertRedundancyDegraded', '.1.3.6.1.4.1.674.10892.1.0.1305', 'Server Redundancy Degraded: $3', '1', 6, 'Redundancy has been degraded.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(593, 'alertRedundancyLost', '.1.3.6.1.4.1.674.10892.1.0.1306', 'Server Redundancy Lost: $3', '2', 6, 'Redundancy has been lost.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(594, 'alertPowerSupplyNormal', '.1.3.6.1.4.1.674.10892.1.0.1352', 'Server Power Supply Normal: $3', '0', 6, 'Power supply has returned to normal.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(595, 'alertPowerSupplyFailure', '.1.3.6.1.4.1.674.10892.1.0.1354', 'Server Power Supply Failure: $3', '2', 6, 'Power supply has failed.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(596, 'alertMemoryDeviceWarning', '.1.3.6.1.4.1.674.10892.1.0.1403', 'Server Memory ECC Count Warning: $3', '1', 6, 'Memory device ECC correctable error count crossed a warning threshold.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(597, 'alertMemoryDeviceFailure', '.1.3.6.1.4.1.674.10892.1.0.1404', 'Server Memory ECC Count Failure: $3', '2', 6, 'Memory device ECC correctable error count crossed a failure threshold.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(598, 'alertMemoryDeviceNonRecoverable', '.1.3.6.1.4.1.674.10892.1.0.1405', 'Server Memory ECC Count Non-recoverable: $3', '2', 6, 'Memory device ECC correctable error count crossed a non-recoverable threshold.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(599, 'alertFanEnclosureInsertion', '.1.3.6.1.4.1.674.10892.1.0.1452', 'Server Fan Enclosure Insertion: $3', '0', 6, 'Fan enclosure has been inserted into system.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(600, 'alertFanEnclosureRemoval', '.1.3.6.1.4.1.674.10892.1.0.1453', 'Server Fan Enclosure Removal: $3', '1', 6, 'Fan enclosure has been removed from system.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(601, 'alertFanEnclosureExtendedRemoval', '.1.3.6.1.4.1.674.10892.1.0.1454', 'Server Fan Enclosure Extended Removal: $3', '2', 6, 'Fan enclosure has been removed from system for an extended amount of time.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(602, 'alertLogNormal', '.1.3.6.1.4.1.674.10892.1.0.1552', 'Server Log Normal: $3', '0', 6, 'Log size returned to a normal level.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(603, 'alertLogWarning', '.1.3.6.1.4.1.674.10892.1.0.1553', 'Server Log Warning: $3', '1', 6, 'Log size is near or at capacity.\n--Novell NMS trap annotation\nVariables:\n  1: alertSystem\n  2: alertTableIndexOID\n  3: alertMessage\n  4: alertCurrentStatus\n  5: alertPreviousStatus\n  6: alertData\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(604, 'ciscoConfigManEvent', '.1.3.6.1.4.1.9.9.43.2.0.1', 'Notification of a configuration management event as $*', '0', 1, 'Notification of a configuration management event as\nrecorded in ccmHistoryEventTable.\nVariables:\n  1: ccmHistoryEventCommandSource\n  2: ccmHistoryEventConfigSource\n  3: ccmHistoryEventConfigDestination\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(605, 'ccmCLIRunningConfigChanged', '.1.3.6.1.4.1.9.9.43.2.0.2', 'This notification indicates that the running $*', '0', 1, 'This notification indicates that the running\nconfiguration of the managed system has changed\nfrom the CLI.\nIf the managed system supports a separate \nconfiguration mode(where the configuration commands \nare entered under a  configuration session which \naffects the running configuration of the system), \nthen this notification is sent when the configuration \nmode is exited.\nDuring this configuration session there can be \none or more running configuration changes.\nVariables:\n  1: ccmHistoryRunningLastChanged\n  2: ccmHistoryEventTerminalType\n');
INSERT INTO `traps` (`traps_id`, `traps_name`, `traps_oid`, `traps_args`, `traps_status`, `manufacturer_id`, `traps_comments`) VALUES(606, 'ccmCTIDRolledOver', '.1.3.6.1.4.1.9.9.43.2.0.3', 'This notification indicates that the Config Change Tracking $*', '0', 1, 'This notification indicates that the Config Change Tracking\nID has rolled over and will be reset.\nVariables:\n');


-- Insert init configuration Centreon Broker

--
-- Contenu de la table `cb_log`
--

INSERT INTO `cb_log` (`id`, `name`) VALUES
(1, 'core'),
(2, 'config'),
(3, 'sql'),
(4, 'processing'),
(5, 'perfdata'),
(6, 'bbdo'),
(7, 'tcp'),
(8, 'tls'),
(9, 'lua'),
(10, 'bam');

--
-- Contenu de la table `cb_log_level`
--

INSERT INTO `cb_log_level` (`id`, `name`) VALUES
(1, 'disabled'),
(2, 'critical'),
(3, 'error'),
(4, 'warning'),
(5, 'info'),
(6, 'debug'),
(7, 'trace');

--
-- Contenu de la table `cb_tag`
--

INSERT INTO `cb_tag` (`cb_tag_id`, `tagname`) VALUES
(2, 'input'),
(3, 'logger'),
(1, 'output');

--
-- Contenu de la table `cb_module`
--

INSERT INTO `cb_module` (`cb_module_id`, `name`, `libname`, `loading_pos`, `is_bundle`, `is_activated`) VALUES
(1, 'SQL', 'sql.so', 80, 0, 1),
(2, 'TCP', 'tcp.so', 50, 0, 1),
(3, 'file', 'file.so', 50, 0, 1),
(6, 'NEB', 'neb.so', 10, 0, 1),
(7, 'RRD', 'rrd.so', 30, 0, 1),
(8, 'Storage', 'storage.so', 20, 0, 1),
(9, 'Core', NULL, NULL, 1, 1),
(10, 'Centreon Storage', NULL, NULL, 1, 1),
(11, 'Compression', 'compression.so', 60, 0, 1),
(12, 'Failover', NULL, NULL, 0, 1),
(17, 'Dumper', 'dumper.so', 20, 0, 1),
(18, 'Graphite', 'graphite.so', 21, 0, 1),
(19, 'InfluxDB', 'influxdb.so', 22, 0, 1),
(20, 'Correlation', 'correlation.so', 30, 0, 1),
(21, 'Generic', 'lua.so', 40, 0, 1);


--
-- Contenu de la table `cb_type`
--

INSERT INTO `cb_type` (`cb_type_id`, `type_name`, `type_shortname`, `cb_module_id`) VALUES
(3, 'IPv4', 'ipv4', 2),
(10, 'IPv6', 'ipv6', 2),
(11, 'File', 'file', 3),
(13, 'RRD file generator', 'rrd', 7),
(14, 'Perfdata Generator (Centreon Storage)', 'storage', 8),
(16, 'Broker SQL database', 'sql', 1),
(17, 'File', 'file', 9),
(18, 'Standard', 'standard', 9),
(19, 'Syslog', 'syslog', 9),
(20, 'Compressor', 'compressor', 11),
(21, 'Failover', 'failover', 12),
(24, 'Monitoring', 'monitoring', 9),
(28, 'Database configuration reader', 'db_cfg_reader', 17),
(29, 'Database configuration writer', 'db_cfg_writer', 17),
(30, 'Storage - Graphite', 'graphite', 18),
(31, 'Storage - InfluxDB', 'influxdb', 19),
(33, 'Stream connector', 'lua', 21);

--
-- Contenu de la table `cb_field`
--

INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`) VALUES
(1, 'port', 'Connection port', 'Port to listen on (empty host) or to connect to (with host filled).', 'int', NULL),
(2, 'host', 'Host to connect to', 'IP address or hostname of the host to connect to (leave blank for listening mode).', 'text', NULL),
(3, 'ca_certificate', 'Trusted CA''s certificate', 'Trusted CA''s certificate.', 'text', NULL),
(4, 'private_key', 'Private key file.', 'Private key file path when TLS encryption is used.', 'text', NULL),
(5, 'public_cert', 'Public certificate', 'Public certificate file path when TLS encryption is used.', 'text', NULL),
(6, 'tls', 'Enable TLS encryption', 'Enable TLS encryption.', 'radio', NULL),
(7, 'db_host', 'DB host', 'IP address or hostname of the database server.', 'text', NULL),
(8, 'db_user', 'DB user', 'Database user.', 'text', NULL),
(9, 'db_password', 'DB password', 'Password of database user.', 'password', NULL),
(10, 'db_name', 'DB name', 'Database name.', 'text', NULL),
(11, 'path', 'File path', 'Path to the file.', 'text', NULL),
(12, 'protocol', 'Serialization protocol', 'Serialization protocol.', 'select', NULL),
(13, 'metrics_path', 'RRD file directory for metrics', 'RRD file directory, for example /var/lib/centreon/metrics', 'text', 'D=centreon_storage:T=config:C=RRDdatabase_path:CK=id:K=1'),
(14, 'status_path', 'RRD file directory for statuses', 'RRD file directory, for example /var/lib/centreon/status', 'text', 'D=centreon_storage:T=config:C=RRDdatabase_status_path:CK=id:K=1'),
(15, 'db_type', 'DB type', 'Target DBMS.', 'select', NULL),
(16, 'interval', 'Interval length', 'Interval length in seconds.', 'int', 'T=options:C=value:CK=key:K=interval_length'),
(17, 'length', 'RRD length', 'RRD storage duration in seconds.', 'int', 'D=centreon_storage:T=config:C=len_storage_rrd:RPN=86400 *:CK=id:K=1'),
(18, 'db_port', 'DB port', 'Port on which the DB server listens', 'int', NULL),
(19, 'name', 'Name of the logger', 'For a file logger this is the path to the file. For a standard logger, one of ''stdout'' or ''stderr''.', 'text', NULL),
(20, 'config', 'Configuration messages', 'Enable or not configuration messages logging.', 'radio', NULL),
(21, 'debug', 'Debug messages', 'Enable or not debug messages logging.', 'radio', NULL),
(22, 'error', 'Error messages', 'Enable or not error messages logging.', 'radio', NULL),
(23, 'info', 'Informational messages', 'Enable or not informational messages logging.', 'radio', NULL),
(24, 'level', 'Logging level', 'How much messages must be logged.', 'select', NULL),
(25, 'compression', 'Compression (zlib)', 'Enable or not data stream compression.', 'radio', NULL),
(26, 'compression_level', 'Compression level', 'Ranges from 0 (no compression) to 9 (best compression). Default is -1 (zlib compression)', 'int', NULL),
(27, 'compression_buffer', 'Compression buffer size', 'The higher the buffer size is, the best compression. This however increase data streaming latency. Use with caution.', 'int', NULL),
(28, 'failover', 'Failover name', 'Name of the input or output object that will act as failover.', 'text', NULL),
(31, 'retry_interval', 'Retry interval', 'Time in seconds to wait between each connection attempt.', 'int', NULL),
(32, 'buffering_timeout', 'Buffering timeout', 'Time in seconds to wait before launching failover.', 'int', NULL),
(33, 'fifo', 'File for Centreon Broker statistics', 'File where Centreon Broker statistics will be stored', 'text', NULL),
(34, 'queries_per_transaction', 'Maximum queries per transaction', 'The maximum queries per transaction before commit.', 'int', NULL),
(35, 'read_timeout', 'Transaction commit timeout', 'The transaction timeout before running commit.', 'int', NULL),
(36, 'rrd_cached_option', 'Enable RRDCached', 'Enable rrdcached option for Centreon, please see Centreon documentation to configure it.', 'radio', NULL),
(37, 'rrd_cached', 'RRDCacheD listening socket/port', 'The absolute path to unix socket or TCP port for communicating with rrdcached daemon.', 'text', NULL),
(38, 'max_size', 'Max file size in bytes', 'The maximum size of log file.', 'int', NULL),
(39, 'check_replication', 'Replication enabled', 'When enabled, the broker engine will check whether or not the replication is up to date before attempting to update data.', 'radio', NULL),
(40, 'rebuild_check_interval', 'Rebuild check interval in seconds', 'The interval between check if some metrics must be rebuild. The default value is 300s', 'int', NULL),
(41, 'max_size', 'Maximum size of file', 'Maximum size in bytes.', 'int', NULL),
(42, 'store_in_data_bin', 'Store in performance data in data_bin', 'It should be enabled to control whether or not Centreon Broker should insert performance data in the data_bin table.', 'radio', NULL),
(43, 'insert_in_index_data', 'Insert in index data', "Whether or not Broker should create entries in the index_data table. This process should be done by Centreon and this option should only be enabled by advanced users knowing what they\'re doing", 'text', 'T=options:C=value:CK=key:K=index_data'),
(44, 'write_metrics', 'Write metrics', 'This can be used to disable graph update and therefore reduce I/O', 'radio', NULL),
(45, 'write_status', 'Write status', 'This can be used to disable graph update and therefore reduce I/O', 'radio', NULL),
(46, 'negotiation', 'Enable negotiation', 'Enable negotiation option (use only for version of Centren Broker >= 2.5)', 'radio', NULL),
(48, 'one_peer_retention_mode', 'One peer retention', 'This allows the retention to work even if the socket is listening', 'radio', NULL),
(49, 'cleanup_check_interval', 'Cleanup check interval', 'Interval in seconds before delete data from deleted pollers.', 'int', NULL),
(50, 'instance_timeout', 'Instance timeout', 'Interval in seconds before change status of resources from a disconnected poller', 'int', NULL),
(51, 'metric_naming', 'Metric naming', 'How to name entries for metrics. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text', NULL),
(52, 'status_naming', 'Status naming', 'How to name entries for statuses. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text', NULL),
(63, 'cache', 'Cache', 'Enable caching', 'radio', NULL),
(64, 'storage_db_host', 'Storage DB host', 'IP address or hostname of the database server.', 'text', NULL),
(65, 'storage_db_user', 'Storage DB user', 'Database user.', 'text', NULL),
(66, 'storage_db_password', 'Storage DB password', 'Password of database user.', 'password', NULL),
(67, 'storage_db_name', 'Storage DB name', 'Database name.', 'text', NULL),
(68, 'storage_db_port', 'Storage DB port', 'Port on which the DB server listens', 'int', NULL),
(69, 'storage_db_type', 'Storage DB type', 'Target DBMS.', 'select', NULL),
(74, 'path', 'Path', 'Path of the lua script.', 'text', NULL),
(75, 'connections_count', 'Number of connection to the database', 'Usually cpus/2', 'int', NULL),
(76, 'tls_hostname', 'TLS Host name', 'Expected TLS certificate common name (CN) - leave blank if unsure.', 'text', NULL);

INSERT INTO `cb_fieldgroup` (`cb_fieldgroup_id`, `groupname`, `displayname`, `multiple`, `group_parent_id`) VALUES
(1, 'filters', '', 0, NULL),
(2, 'metrics_column', 'Metrics column', 1, NULL),
(3, 'status_column', 'Status column', 1, NULL),
(4, 'lua_parameter', 'lua parameter', 1, NULL);

INSERT INTO `cb_field` (`cb_field_id`, `fieldname`, `displayname`, `description`, `fieldtype`, `external`, `cb_fieldgroup_id`) VALUES
(47, 'category', 'Filter category', 'Category filter for flux in output', 'multiselect', NULL, 1),
(53, 'metrics_timeseries', 'Metrics timeseries', 'How to name entries for metrics timeseries. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text', NULL, NULL),
(54, 'status_timeseries', 'Status timeseries', 'How to name entries for statuses timeseries. This string supports macros such as $METRIC$, $HOST$, $SERVICE$ and $INSTANCE$', 'text', NULL, NULL),
(55, 'name', 'Name', 'Name of the column (macros accepted)', 'text', NULL, 2),
(56, 'value', 'Value', 'Value of the column (macros accepted)', 'text', NULL, 2),
(57, 'type', 'Type', 'Type of the column', 'select', NULL, 2),
(58, 'is_tag', 'Tag', 'Whether or not this column is a tag', 'radio', NULL, 2),
(59, 'name', 'Name', 'Name of the column (macros accepted)', 'text', NULL, 3),
(60, 'value', 'Value', 'Value of the column (macros accepted)', 'text', NULL, 3),
(61, 'type', 'Type', 'Type of the column', 'select', NULL, 3),
(62, 'is_tag', 'Tag', 'Whether or not this column is a tag', 'radio', NULL, 3),
(71, 'name', 'Name', 'Name of the metric.', 'text', NULL, 4),
(72, 'value', 'Value', 'Value of the metric.', 'text', NULL, 4),
(73, 'type', 'Type', 'Type of the metric.', 'select', NULL, 4);

--
-- Contenu de la table `cb_list`
--

INSERT INTO `cb_list` (`cb_list_id`, `cb_field_id`, `default_value`) VALUES
(5, 6, 'no'),
(1, 20, 'yes'),
(1, 21, 'no'),
(1, 22, 'yes'),
(1, 23, 'no'),
(5, 25, 'no'),
(2, 12, NULL),
(3, 15, NULL),
(3, 69, NULL),
(4, 24, NULL),
(1, 39, 'no'),
(1, 42, 'yes'),
(1, 44, 'yes'),
(1, 45, 'yes'),
(1, 46, 'yes'),
(6, 47, NULL),
(1, 48, 'no'),
(7, 57, 'string'),
(8, 58, 'false'),
(9, 61, 'string'),
(10, 62, 'false'),
(1, 63, 'yes'),
(11, 73, 'string'),
(12, 36, 'disable');

--
-- Contenu de la table `cb_list_values`
--

INSERT INTO `cb_list_values` (`cb_list_id`, `value_name`, `value_value`) VALUES
(1, 'No', 'no'),
(1, 'Yes', 'yes'),
(2, 'BBDO Protocol', 'bbdo'),
(3, 'DB2', 'db2'),
(3, 'InterBase', 'ibase'),
(3, 'MySQL', 'mysql'),
(3, 'ODBC', 'odbc'),
(3, 'Oracle', 'oci'),
(3, 'PostgreSQL', 'postgresql'),
(3, 'SQLite', 'sqlite'),
(3, 'Sysbase', 'tds'),
(4, 'Base', 'low'),
(4, 'Detailed', 'medium'),
(4, 'Very detailed', 'high'),
(5, 'No', 'no'),
(5, 'Yes', 'yes'),
(5, 'Auto', 'auto'),
(6, 'Neb', 'neb'),
(6, 'Storage', 'storage'),
(6, 'Correlation', 'correlation'),
(6, 'Dumper', 'dumper'),
(7, 'String', 'string'),
(7, 'Number', 'number'),
(8, 'True', 'true'),
(8, 'False', 'false'),
(9, 'String', 'string'),
(9, 'Number', 'number'),
(10, 'True', 'true'),
(10, 'False', 'false'),
(11, 'String', 'string'),
(11, 'Number', 'number'),
(11, 'Password', 'password'),
(12, 'Disable', 'disable'),
(12, 'TCP Port', 'tcp'),
(12, 'UNIX Socket', 'unix');

--
-- Contenu de la table `cb_module_relation`
--

INSERT INTO `cb_module_relation` (`cb_module_id`, `module_depend_id`, `inherit_config`) VALUES
(1, 6, 0),
(1, 8, 0),
(1, 12, 1),
(2, 11, 1),
(2, 12, 1),
(3, 11, 1),
(3, 12, 1),
(7, 8, 0),
(7, 12, 1),
(8, 6, 0),
(8, 12, 1);

--
-- Contenu de la table `cb_tag_type_relation`
--

INSERT INTO `cb_tag_type_relation` (`cb_tag_id`, `cb_type_id`, `cb_type_uniq`) VALUES
(1, 3, 0),
(1, 10, 0),
(1, 11, 0),
(1, 13, 0),
(1, 14, 0),
(1, 16, 0),
(2, 3, 0),
(2, 10, 0),
(2, 11, 0),
(3, 17, 0),
(3, 18, 0),
(3, 19, 0),
(3, 24, 0),
(1, 28, 1),
(1, 29, 1),
(1, 30, 0),
(1, 31, 0),
(1, 33, 0);

--
-- Contenu de la table `cb_type_field_relation`
--

INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES
(3, 1, 1, 1),
(3, 2, 0, 2),
(3, 3, 0, 7),
(3, 4, 0, 5),
(3, 5, 0, 6),
(3, 6, 1, 4),
(3, 46, 1, 8),
(3, 12, 1, 3),
(10, 1, 1, 1),
(10, 2, 0, 2),
(10, 3, 0, 7),
(10, 4, 0, 5),
(10, 5, 0, 6),
(10, 6, 1, 4),
(10, 46, 1, 8),
(10, 12, 1, 3),
(11, 11, 1, 1),
(11, 12, 1, 2),
(11, 41, 0, 3),
(13, 37, 0, 4),
(13, 13, 1, 1),
(13, 14, 1, 2),
(14, 7, 1, 4),
(14, 18, 1, 5),
(14, 8, 1, 6),
(14, 9, 0, 7),
(14, 10, 1, 8),
(14, 15, 1, 3),
(14, 16, 1, 1),
(14, 17, 1, 2),
(16, 7, 1, 2),
(16, 8, 1, 4),
(16, 18, 1, 3),
(16, 9, 0, 5),
(16, 10, 1, 6),
(16, 15, 1, 1),
(17, 19, 1, 1),
(17, 20, 1, 2),
(17, 21, 1, 3),
(17, 22, 1, 4),
(17, 23, 1, 5),
(17, 24, 1, 6),
(18, 19, 1, 1),
(18, 20, 1, 2),
(18, 21, 1, 3),
(18, 22, 1, 4),
(18, 23, 1, 5),
(18, 24, 1, 6),
(19, 20, 1, 1),
(19, 21, 1, 2),
(19, 22, 1, 3),
(19, 23, 1, 4),
(19, 24, 1, 5),
(20, 25, 0, 101),
(20, 26, 0, 102),
(20, 27, 0, 103),
(16, 34, 0, 7),
(16, 35, 0, 8),
(14, 34, 0, 9),
(14, 35, 0, 10),
(14, 42, 1, 13),
(14, 43, 1, 14),
(17, 38, 0, 7),
(24, 19, 1, 1),
(24, 20, 1, 2),
(24, 21, 1, 3),
(24, 22, 1, 4),
(24, 23, 1, 5),
(24, 24, 1, 6),
(14, 39, 0, 11),
(16, 39, 0, 8),
(14, 40, 0, 12),
(13, 42, 1, 5),
(13, 43, 1, 6),
(13, 44, 1, 5),
(13, 45, 1, 6),
(3, 47, 0, 17),
(10, 47, 0, 17),
(11, 47, 0, 17),
(13, 47, 0, 17),
(14, 47, 0, 17),
(16, 47, 0, 17),
(3, 48, 0, 16),
(10, 48, 0, 16),
(16, 49, 0, 18),
(16, 50, 0, 19),
(21, 28, 0, 8),
(21, 31, 0, 8),
(21, 32, 0, 8),
(28, 15, 1, 1),
(28, 7, 1, 2),
(28, 18, 1, 3),
(28, 8, 1, 4),
(28, 9, 0, 5),
(28, 10, 1, 6),
(29, 15, 1, 1),
(29, 7, 1, 2),
(29, 18, 1, 3),
(29, 8, 1, 4),
(29, 9, 0, 5),
(29, 10, 1, 6),
(30, 7, 1, 1),
(30, 18, 0, 2),
(30, 8, 0, 3),
(30, 9, 0, 4),
(30, 34, 0, 5),
(30, 51, 1, 7),
(30, 52, 1, 8),
(31, 63, 0, 1),
(31, 7, 1, 2),
(31, 18, 0, 3),
(31, 8, 1, 4),
(31, 9, 0, 5),
(31, 10, 1, 6),
(31, 53, 1, 7),
(31, 55, 0, 8),
(31, 56, 0, 9),
(31, 57, 0, 10),
(31, 58, 0, 11),
(31, 54, 1, 12),
(31, 59, 0, 13),
(31, 60, 0, 14),
(31, 61, 0, 15),
(31, 62, 0, 16),
(33, 74, 1, 1),
(33, 47, 0, 2),
(33, 72, 0, 3),
(33, 71, 0, 4),
(3, 76, 0, 5);

--
-- Contenu de la table `cb_type_field_relation`
--
INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`, `jshook_name`, `jshook_arguments`) VALUES
(33, 73, 0, 5, 'luaArguments', '{"target": "lua_parameter__value_%d"}'),
(13, 36, 0, 3, 'rrdArguments', '{"target": "rrd_cached"}'),
(16, 75, 0, 7, 'countConnections', '{"target": "connections_count"}'),
(14, 75, 0, 7, 'countConnections', '{"target": "connections_count"}');

--
-- Contenu de la table `widget_parameters_field_type`
--
INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES
                                           ('text', 0),
                                           ('boolean', 0),
                                           ('hidden', 0),
                                           ('password', 0),
                                           ('list', 0),
                                           ('range', 0),
                                           ('compare', 0),
                                           ('sort', 0),
                                           ('date', 0),
                                           ('host', 1),
                                           ('hostTemplate', 1),
                                           ('serviceTemplate', 1),
                                           ('hostgroup', 1),
                                           ('servicegroup', 1),
                                           ('service', 1),
                                           ('poller', 1),
                                           ('hostCategories',1),
                                           ('serviceCategories',1),
                                           ('metric',1),
                                           ('ba', 1),
                                           ('bv', 1),
                                           ('hostCategoriesMulti', 1),
                                           ('hostGroupMulti', 1),
                                           ('hostMulti', 1),
                                           ('metricMulti', 1),
                                           ('serviceCategory', 1),
                                           ('hostCategory', 1),
                                           ('serviceMulti', 1),
                                           ('pollerMulti', 1),
                                           ('serviceGroupMulti', 1),
                                           ('hostSeverityMulti', 1),
                                           ('serviceSeverityMulti', 1),
                                           ('integer', 0);

INSERT INTO timezone (`timezone_name`, `timezone_offset`, `timezone_dst_offset`) VALUES
                        ('Africa/Abidjan', '-00:00', '-00:00'),
                        ('Africa/Accra', '-00:00', '-00:00'),
                        ('Africa/Addis_Ababa', '+03:00', '+03:00'),
                        ('Africa/Algiers', '+01:00', '+01:00'),
                        ('Africa/Asmara', '+03:00', '+03:00'),
                        ('Africa/Bamako', '-00:00', '-00:00'),
                        ('Africa/Bangui', '+01:00', '+01:00'),
                        ('Africa/Banjul', '-00:00', '-00:00'),
                        ('Africa/Bissau', '-00:00', '-00:00'),
                        ('Africa/Blantyre', '+02:00', '+02:00'),
                        ('Africa/Brazzaville', '+01:00', '+01:00'),
                        ('Africa/Bujumbura', '+02:00', '+02:00'),
                        ('Africa/Cairo', '+02:00', '+02:00'),
                        ('Africa/Casablanca', '-00:00', '+01:00'),
                        ('Africa/Ceuta', '+01:00', '+02:00'),
                        ('Africa/Conakry', '-00:00', '-00:00'),
                        ('Africa/Dakar', '-00:00', '-00:00'),
                        ('Africa/Dar_es_Salaam', '+03:00', '+03:00'),
                        ('Africa/Djibouti', '+03:00', '+03:00'),
                        ('Africa/Douala', '+01:00', '+01:00'),
                        ('Africa/El_Aaiun', '-00:00', '+01:00'),
                        ('Africa/Freetown', '-00:00', '-00:00'),
                        ('Africa/Gaborone', '+02:00', '+02:00'),
                        ('Africa/Harare', '+02:00', '+02:00'),
                        ('Africa/Johannesburg', '+02:00', '+02:00'),
                        ('Africa/Juba', '+03:00', '+03:00'),
                        ('Africa/Kampala', '+03:00', '+03:00'),
                        ('Africa/Khartoum', '+03:00', '+03:00'),
                        ('Africa/Kigali', '+02:00', '+02:00'),
                        ('Africa/Kinshasa', '+01:00', '+01:00'),
                        ('Africa/Lagos', '+01:00', '+01:00'),
                        ('Africa/Libreville', '+01:00', '+01:00'),
                        ('Africa/Lome', '-00:00', '-00:00'),
                        ('Africa/Luanda', '+01:00', '+01:00'),
                        ('Africa/Lubumbashi', '+02:00', '+02:00'),
                        ('Africa/Lusaka', '+02:00', '+02:00'),
                        ('Africa/Malabo', '+01:00', '+01:00'),
                        ('Africa/Maputo', '+02:00', '+02:00'),
                        ('Africa/Maseru', '+02:00', '+02:00'),
                        ('Africa/Mbabane', '+02:00', '+02:00'),
                        ('Africa/Mogadishu', '+03:00', '+03:00'),
                        ('Africa/Monrovia', '-00:00', '-00:00'),
                        ('Africa/Nairobi', '+03:00', '+03:00'),
                        ('Africa/Ndjamena', '+01:00', '+01:00'),
                        ('Africa/Niamey', '+01:00', '+01:00'),
                        ('Africa/Nouakchott', '-00:00', '-00:00'),
                        ('Africa/Ouagadougou', '-00:00', '-00:00'),
                        ('Africa/Porto-Novo', '+01:00', '+01:00'),
                        ('Africa/Sao_Tome', '-00:00', '-00:00'),
                        ('Africa/Tripoli', '+02:00', '+02:00'),
                        ('Africa/Tunis', '+01:00', '+01:00'),
                        ('Africa/Windhoek', '+02:00', '+01:00'),
                        ('America/Adak', '-10:00', '-09:00'),
                        ('America/Anchorage', '-09:00', '-08:00'),
                        ('America/Anguilla', '-04:00', '-04:00'),
                        ('America/Antigua', '-04:00', '-04:00'),
                        ('America/Araguaina', '-03:00', '-03:00'),
                        ('America/Argentina/Buenos_Aires', '-03:00', '-03:00'),
                        ('America/Argentina/Catamarca', '-03:00', '-03:00'),
                        ('America/Argentina/Cordoba', '-03:00', '-03:00'),
                        ('America/Argentina/Jujuy', '-03:00', '-03:00'),
                        ('America/Argentina/La_Rioja', '-03:00', '-03:00'),
                        ('America/Argentina/Mendoza', '-03:00', '-03:00'),
                        ('America/Argentina/Rio_Gallegos', '-03:00', '-03:00'),
                        ('America/Argentina/Salta', '-03:00', '-03:00'),
                        ('America/Argentina/San_Juan', '-03:00', '-03:00'),
                        ('America/Argentina/San_Luis', '-03:00', '-03:00'),
                        ('America/Argentina/Tucuman', '-03:00', '-03:00'),
                        ('America/Argentina/Ushuaia', '-03:00', '-03:00'),
                        ('America/Aruba', '-04:00', '-04:00'),
                        ('America/Asuncion', '-03:00', '-04:00'),
                        ('America/Atikokan', '-05:00', '-05:00'),
                        ('America/Bahia', '-03:00', '-03:00'),
                        ('America/Bahia_Banderas', '-06:00', '-05:00'),
                        ('America/Barbados', '-04:00', '-04:00'),
                        ('America/Belem', '-03:00', '-03:00'),
                        ('America/Belize', '-06:00', '-06:00'),
                        ('America/Blanc-Sablon', '-04:00', '-04:00'),
                        ('America/Boa_Vista', '-04:00', '-04:00'),
                        ('America/Bogota', '-05:00', '-05:00'),
                        ('America/Boise', '-07:00', '-06:00'),
                        ('America/Cambridge_Bay', '-07:00', '-06:00'),
                        ('America/Campo_Grande', '-03:00', '-04:00'),
                        ('America/Cancun', '-06:00', '-05:00'),
                        ('America/Caracas', '-04:30', '-04:30'),
                        ('America/Cayenne', '-03:00', '-03:00'),
                        ('America/Cayman', '-05:00', '-05:00'),
                        ('America/Chicago', '-06:00', '-05:00'),
                        ('America/Chihuahua', '-07:00', '-06:00'),
                        ('America/Costa_Rica', '-06:00', '-06:00'),
                        ('America/Creston', '-07:00', '-07:00'),
                        ('America/Cuiaba', '-03:00', '-04:00'),
                        ('America/Curacao', '-04:00', '-04:00'),
                        ('America/Danmarkshavn', '-00:00', '-00:00'),
                        ('America/Dawson', '-08:00', '-07:00'),
                        ('America/Dawson_Creek', '-07:00', '-07:00'),
                        ('America/Denver', '-07:00', '-06:00'),
                        ('America/Detroit', '-05:00', '-04:00'),
                        ('America/Dominica', '-04:00', '-04:00'),
                        ('America/Edmonton', '-07:00', '-06:00'),
                        ('America/Eirunepe', '-05:00', '-05:00'),
                        ('America/El_Salvador', '-06:00', '-06:00'),
                        ('America/Fortaleza', '-03:00', '-03:00'),
                        ('America/Glace_Bay', '-04:00', '-03:00'),
                        ('America/Godthab', '-03:00', '-02:00'),
                        ('America/Goose_Bay', '-04:00', '-03:00'),
                        ('America/Grand_Turk', '-05:00', '-04:00'),
                        ('America/Grenada', '-04:00', '-04:00'),
                        ('America/Guadeloupe', '-04:00', '-04:00'),
                        ('America/Guatemala', '-06:00', '-06:00'),
                        ('America/Guayaquil', '-05:00', '-05:00'),
                        ('America/Guyana', '-04:00', '-04:00'),
                        ('America/Halifax', '-04:00', '-03:00'),
                        ('America/Havana', '-05:00', '-04:00'),
                        ('America/Hermosillo', '-07:00', '-07:00'),
                        ('America/Indiana/Indianapolis', '-05:00', '-04:00'),
                        ('America/Indiana/Knox', '-06:00', '-05:00'),
                        ('America/Indiana/Marengo', '-05:00', '-04:00'),
                        ('America/Indiana/Petersburg', '-05:00', '-04:00'),
                        ('America/Indiana/Tell_City', '-06:00', '-05:00'),
                        ('America/Indiana/Vevay', '-05:00', '-04:00'),
                        ('America/Indiana/Vincennes', '-05:00', '-04:00'),
                        ('America/Indiana/Winamac', '-05:00', '-04:00'),
                        ('America/Inuvik', '-07:00', '-06:00'),
                        ('America/Iqaluit', '-05:00', '-04:00'),
                        ('America/Jamaica', '-05:00', '-05:00'),
                        ('America/Juneau', '-09:00', '-08:00'),
                        ('America/Kentucky/Louisville', '-05:00', '-04:00'),
                        ('America/Kentucky/Monticello', '-05:00', '-04:00'),
                        ('America/Kralendijk', '-04:00', '-04:00'),
                        ('America/La_Paz', '-04:00', '-04:00'),
                        ('America/Lima', '-05:00', '-05:00'),
                        ('America/Los_Angeles', '-08:00', '-07:00'),
                        ('America/Lower_Princes', '-04:00', '-04:00'),
                        ('America/Maceio', '-03:00', '-03:00'),
                        ('America/Managua', '-06:00', '-06:00'),
                        ('America/Manaus', '-04:00', '-04:00'),
                        ('America/Marigot', '-04:00', '-04:00'),
                        ('America/Martinique', '-04:00', '-04:00'),
                        ('America/Matamoros', '-06:00', '-05:00'),
                        ('America/Mazatlan', '-07:00', '-06:00'),
                        ('America/Menominee', '-06:00', '-05:00'),
                        ('America/Merida', '-06:00', '-05:00'),
                        ('America/Metlakatla', '-08:00', '-08:00'),
                        ('America/Mexico_City', '-06:00', '-05:00'),
                        ('America/Miquelon', '-03:00', '-02:00'),
                        ('America/Moncton', '-04:00', '-03:00'),
                        ('America/Monterrey', '-06:00', '-05:00'),
                        ('America/Montevideo', '-02:00', '-03:00'),
                        ('America/Montserrat', '-04:00', '-04:00'),
                        ('America/Nassau', '-05:00', '-04:00'),
                        ('America/New_York', '-05:00', '-04:00'),
                        ('America/Nipigon', '-05:00', '-04:00'),
                        ('America/Nome', '-09:00', '-08:00'),
                        ('America/Noronha', '-02:00', '-02:00'),
                        ('America/North_Dakota/Beulah', '-06:00', '-05:00'),
                        ('America/North_Dakota/Center', '-06:00', '-05:00'),
                        ('America/North_Dakota/New_Salem', '-06:00', '-05:00'),
                        ('America/Ojinaga', '-07:00', '-06:00'),
                        ('America/Panama', '-05:00', '-05:00'),
                        ('America/Pangnirtung', '-05:00', '-04:00'),
                        ('America/Paramaribo', '-03:00', '-03:00'),
                        ('America/Phoenix', '-07:00', '-07:00'),
                        ('America/Port-au-Prince', '-05:00', '-04:00'),
                        ('America/Port_of_Spain', '-04:00', '-04:00'),
                        ('America/Porto_Velho', '-04:00', '-04:00'),
                        ('America/Puerto_Rico', '-04:00', '-04:00'),
                        ('America/Rainy_River', '-06:00', '-05:00'),
                        ('America/Rankin_Inlet', '-06:00', '-05:00'),
                        ('America/Recife', '-03:00', '-03:00'),
                        ('America/Regina', '-06:00', '-06:00'),
                        ('America/Resolute', '-06:00', '-05:00'),
                        ('America/Rio_Branco', '-05:00', '-05:00'),
                        ('America/Santa_Isabel', '-08:00', '-07:00'),
                        ('America/Santarem', '-03:00', '-03:00'),
                        ('America/Santiago', '-03:00', '-03:00'),
                        ('America/Santo_Domingo', '-04:00', '-04:00'),
                        ('America/Sao_Paulo', '-02:00', '-03:00'),
                        ('America/Scoresbysund', '-01:00', '-00:00'),
                        ('America/Sitka', '-09:00', '-08:00'),
                        ('America/St_Barthelemy', '-04:00', '-04:00'),
                        ('America/St_Johns', '-03:30', '-02:30'),
                        ('America/St_Kitts', '-04:00', '-04:00'),
                        ('America/St_Lucia', '-04:00', '-04:00'),
                        ('America/St_Thomas', '-04:00', '-04:00'),
                        ('America/St_Vincent', '-04:00', '-04:00'),
                        ('America/Swift_Current', '-06:00', '-06:00'),
                        ('America/Tegucigalpa', '-06:00', '-06:00'),
                        ('America/Thule', '-04:00', '-03:00'),
                        ('America/Thunder_Bay', '-05:00', '-04:00'),
                        ('America/Tijuana', '-08:00', '-07:00'),
                        ('America/Toronto', '-05:00', '-04:00'),
                        ('America/Tortola', '-04:00', '-04:00'),
                        ('America/Vancouver', '-08:00', '-07:00'),
                        ('America/Whitehorse', '-08:00', '-07:00'),
                        ('America/Winnipeg', '-06:00', '-05:00'),
                        ('America/Yakutat', '-09:00', '-08:00'),
                        ('America/Yellowknife', '-07:00', '-06:00'),
                        ('Antarctica/Casey', '+08:00', '+08:00'),
                        ('Antarctica/Davis', '+07:00', '+07:00'),
                        ('Antarctica/DumontDUrville', '+10:00', '+10:00'),
                        ('Antarctica/Macquarie', '+11:00', '+11:00'),
                        ('Antarctica/Mawson', '+05:00', '+05:00'),
                        ('Antarctica/McMurdo', '+13:00', '+12:00'),
                        ('Antarctica/Palmer', '-03:00', '-03:00'),
                        ('Antarctica/Rothera', '-03:00', '-03:00'),
                        ('Antarctica/Syowa', '+03:00', '+03:00'),
                        ('Antarctica/Troll', '+02:00', '+02:00'),
                        ('Antarctica/Vostok', '+06:00', '+06:00'),
                        ('Arctic/Longyearbyen', '+01:00', '+02:00'),
                        ('Asia/Aden', '+03:00', '+03:00'),
                        ('Asia/Almaty', '+06:00', '+06:00'),
                        ('Asia/Amman', '+02:00', '+03:00'),
                        ('Asia/Anadyr', '+12:00', '+12:00'),
                        ('Asia/Aqtau', '+05:00', '+05:00'),
                        ('Asia/Aqtobe', '+05:00', '+05:00'),
                        ('Asia/Ashgabat', '+05:00', '+05:00'),
                        ('Asia/Baghdad', '+03:00', '+03:00'),
                        ('Asia/Bahrain', '+03:00', '+03:00'),
                        ('Asia/Baku', '+04:00', '+05:00'),
                        ('Asia/Bangkok', '+07:00', '+07:00'),
                        ('Asia/Beirut', '+02:00', '+03:00'),
                        ('Asia/Bishkek', '+06:00', '+06:00'),
                        ('Asia/Brunei', '+08:00', '+08:00'),
                        ('Asia/Chita', '+08:00', '+08:00'),
                        ('Asia/Choibalsan', '+08:00', '+09:00'),
                        ('Asia/Colombo', '+05:30', '+05:30'),
                        ('Asia/Damascus', '+02:00', '+03:00'),
                        ('Asia/Dhaka', '+06:00', '+06:00'),
                        ('Asia/Dili', '+09:00', '+09:00'),
                        ('Asia/Dubai', '+04:00', '+04:00'),
                        ('Asia/Dushanbe', '+05:00', '+05:00'),
                        ('Asia/Gaza', '+02:00', '+03:00'),
                        ('Asia/Hebron', '+02:00', '+03:00'),
                        ('Asia/Ho_Chi_Minh', '+07:00', '+07:00'),
                        ('Asia/Hong_Kong', '+08:00', '+08:00'),
                        ('Asia/Hovd', '+07:00', '+08:00'),
                        ('Asia/Irkutsk', '+08:00', '+08:00'),
                        ('Asia/Jakarta', '+07:00', '+07:00'),
                        ('Asia/Jayapura', '+09:00', '+09:00'),
                        ('Asia/Jerusalem', '+02:00', '+03:00'),
                        ('Asia/Kabul', '+04:30', '+04:30'),
                        ('Asia/Kamchatka', '+12:00', '+12:00'),
                        ('Asia/Karachi', '+05:00', '+05:00'),
                        ('Asia/Kathmandu', '+05:45', '+05:45'),
                        ('Asia/Khandyga', '+09:00', '+09:00'),
                        ('Asia/Kolkata', '+05:30', '+05:30'),
                        ('Asia/Krasnoyarsk', '+07:00', '+07:00'),
                        ('Asia/Kuala_Lumpur', '+08:00', '+08:00'),
                        ('Asia/Kuching', '+08:00', '+08:00'),
                        ('Asia/Kuwait', '+03:00', '+03:00'),
                        ('Asia/Macau', '+08:00', '+08:00'),
                        ('Asia/Magadan', '+10:00', '+10:00'),
                        ('Asia/Makassar', '+08:00', '+08:00'),
                        ('Asia/Manila', '+08:00', '+08:00'),
                        ('Asia/Muscat', '+04:00', '+04:00'),
                        ('Asia/Nicosia', '+02:00', '+03:00'),
                        ('Asia/Novokuznetsk', '+07:00', '+07:00'),
                        ('Asia/Novosibirsk', '+06:00', '+06:00'),
                        ('Asia/Omsk', '+06:00', '+06:00'),
                        ('Asia/Oral', '+05:00', '+05:00'),
                        ('Asia/Phnom_Penh', '+07:00', '+07:00'),
                        ('Asia/Pontianak', '+07:00', '+07:00'),
                        ('Asia/Pyongyang', '+09:00', '+09:00'),
                        ('Asia/Qatar', '+03:00', '+03:00'),
                        ('Asia/Qyzylorda', '+06:00', '+06:00'),
                        ('Asia/Rangoon', '+06:30', '+06:30'),
                        ('Asia/Riyadh', '+03:00', '+03:00'),
                        ('Asia/Sakhalin', '+10:00', '+10:00'),
                        ('Asia/Samarkand', '+05:00', '+05:00'),
                        ('Asia/Seoul', '+09:00', '+09:00'),
                        ('Asia/Shanghai', '+08:00', '+08:00'),
                        ('Asia/Singapore', '+08:00', '+08:00'),
                        ('Asia/Srednekolymsk', '+11:00', '+11:00'),
                        ('Asia/Taipei', '+08:00', '+08:00'),
                        ('Asia/Tashkent', '+05:00', '+05:00'),
                        ('Asia/Tbilisi', '+04:00', '+04:00'),
                        ('Asia/Tehran', '+03:30', '+04:30'),
                        ('Asia/Thimphu', '+06:00', '+06:00'),
                        ('Asia/Tokyo', '+09:00', '+09:00'),
                        ('Asia/Ulaanbaatar', '+08:00', '+09:00'),
                        ('Asia/Urumqi', '+06:00', '+06:00'),
                        ('Asia/Ust-Nera', '+10:00', '+10:00'),
                        ('Asia/Vientiane', '+07:00', '+07:00'),
                        ('Asia/Vladivostok', '+10:00', '+10:00'),
                        ('Asia/Yakutsk', '+09:00', '+09:00'),
                        ('Asia/Yangon', '+06:30', '+06:30'),
                        ('Asia/Yekaterinburg', '+05:00', '+05:00'),
                        ('Asia/Yerevan', '+04:00', '+04:00'),
                        ('Atlantic/Azores', '-01:00', '-00:00'),
                        ('Atlantic/Bermuda', '-04:00', '-03:00'),
                        ('Atlantic/Canary', '-00:00', '+01:00'),
                        ('Atlantic/Cape_Verde', '-01:00', '-01:00'),
                        ('Atlantic/Faroe', '-00:00', '+01:00'),
                        ('Atlantic/Madeira', '-00:00', '+01:00'),
                        ('Atlantic/Reykjavik', '-00:00', '-00:00'),
                        ('Atlantic/South_Georgia', '-02:00', '-02:00'),
                        ('Atlantic/St_Helena', '-00:00', '-00:00'),
                        ('Atlantic/Stanley', '-03:00', '-03:00'),
                        ('Australia/Adelaide', '+10:30', '+09:30'),
                        ('Australia/Brisbane', '+10:00', '+10:00'),
                        ('Australia/Broken_Hill', '+10:30', '+09:30'),
                        ('Australia/Currie', '+11:00', '+10:00'),
                        ('Australia/Darwin', '+09:30', '+09:30'),
                        ('Australia/Eucla', '+08:45', '+08:45'),
                        ('Australia/Hobart', '+11:00', '+10:00'),
                        ('Australia/Lindeman', '+10:00', '+10:00'),
                        ('Australia/Lord_Howe', '+11:00', '+10:30'),
                        ('Australia/Melbourne', '+11:00', '+10:00'),
                        ('Australia/Perth', '+08:00', '+08:00'),
                        ('Australia/Sydney', '+11:00', '+10:00'),
                        ('Europe/Amsterdam', '+01:00', '+02:00'),
                        ('Europe/Andorra', '+01:00', '+02:00'),
                        ('Europe/Athens', '+02:00', '+03:00'),
                        ('Europe/Belgrade', '+01:00', '+02:00'),
                        ('Europe/Berlin', '+01:00', '+02:00'),
                        ('Europe/Bratislava', '+01:00', '+02:00'),
                        ('Europe/Brussels', '+01:00', '+02:00'),
                        ('Europe/Bucharest', '+02:00', '+03:00'),
                        ('Europe/Budapest', '+01:00', '+02:00'),
                        ('Europe/Busingen', '+01:00', '+02:00'),
                        ('Europe/Chisinau', '+02:00', '+03:00'),
                        ('Europe/Copenhagen', '+01:00', '+02:00'),
                        ('Europe/Dublin', '-00:00', '+01:00'),
                        ('Europe/Gibraltar', '+01:00', '+02:00'),
                        ('Europe/Guernsey', '-00:00', '+01:00'),
                        ('Europe/Helsinki', '+02:00', '+03:00'),
                        ('Europe/Isle_of_Man', '-00:00', '+01:00'),
                        ('Europe/Istanbul', '+02:00', '+03:00'),
                        ('Europe/Jersey', '-00:00', '+01:00'),
                        ('Europe/Kaliningrad', '+02:00', '+02:00'),
                        ('Europe/Kiev', '+02:00', '+03:00'),
                        ('Europe/Lisbon', '-00:00', '+01:00'),
                        ('Europe/Ljubljana', '+01:00', '+02:00'),
                        ('Europe/London', '-00:00', '+01:00'),
                        ('Europe/Luxembourg', '+01:00', '+02:00'),
                        ('Europe/Madrid', '+01:00', '+02:00'),
                        ('Europe/Malta', '+01:00', '+02:00'),
                        ('Europe/Mariehamn', '+02:00', '+03:00'),
                        ('Europe/Minsk', '+03:00', '+03:00'),
                        ('Europe/Monaco', '+01:00', '+02:00'),
                        ('Europe/Moscow', '+03:00', '+03:00'),
                        ('Europe/Oslo', '+01:00', '+02:00'),
                        ('Europe/Paris', '+01:00', '+02:00'),
                        ('Europe/Podgorica', '+01:00', '+02:00'),
                        ('Europe/Prague', '+01:00', '+02:00'),
                        ('Europe/Riga', '+02:00', '+03:00'),
                        ('Europe/Rome', '+01:00', '+02:00'),
                        ('Europe/Samara', '+04:00', '+04:00'),
                        ('Europe/San_Marino', '+01:00', '+02:00'),
                        ('Europe/Sarajevo', '+01:00', '+02:00'),
                        ('Europe/Simferopol', '+03:00', '+03:00'),
                        ('Europe/Skopje', '+01:00', '+02:00'),
                        ('Europe/Sofia', '+02:00', '+03:00'),
                        ('Europe/Stockholm', '+01:00', '+02:00'),
                        ('Europe/Tallinn', '+02:00', '+03:00'),
                        ('Europe/Tirane', '+01:00', '+02:00'),
                        ('Europe/Uzhgorod', '+02:00', '+03:00'),
                        ('Europe/Vaduz', '+01:00', '+02:00'),
                        ('Europe/Vatican', '+01:00', '+02:00'),
                        ('Europe/Vienna', '+01:00', '+02:00'),
                        ('Europe/Vilnius', '+02:00', '+03:00'),
                        ('Europe/Volgograd', '+03:00', '+03:00'),
                        ('Europe/Warsaw', '+01:00', '+02:00'),
                        ('Europe/Zagreb', '+01:00', '+02:00'),
                        ('Europe/Zaporozhye', '+02:00', '+03:00'),
                        ('Europe/Zurich', '+01:00', '+02:00'),
                        ('Indian/Antananarivo', '+03:00', '+03:00'),
                        ('Indian/Chagos', '+06:00', '+06:00'),
                        ('Indian/Christmas', '+07:00', '+07:00'),
                        ('Indian/Cocos', '+06:30', '+06:30'),
                        ('Indian/Comoro', '+03:00', '+03:00'),
                        ('Indian/Kerguelen', '+05:00', '+05:00'),
                        ('Indian/Mahe', '+04:00', '+04:00'),
                        ('Indian/Maldives', '+05:00', '+05:00'),
                        ('Indian/Mauritius', '+04:00', '+04:00'),
                        ('Indian/Mayotte', '+03:00', '+03:00'),
                        ('Indian/Reunion', '+04:00', '+04:00'),
                        ('Pacific/Apia', '+14:00', '+13:00'),
                        ('Pacific/Auckland', '+13:00', '+12:00'),
                        ('Pacific/Bougainville', '+11:00', '+11:00'),
                        ('Pacific/Chatham', '+13:45', '+12:45'),
                        ('Pacific/Chuuk', '+10:00', '+10:00'),
                        ('Pacific/Easter', '-05:00', '-05:00'),
                        ('Pacific/Efate', '+11:00', '+11:00'),
                        ('Pacific/Enderbury', '+13:00', '+13:00'),
                        ('Pacific/Fakaofo', '+13:00', '+13:00'),
                        ('Pacific/Fiji', '+13:00', '+12:00'),
                        ('Pacific/Funafuti', '+12:00', '+12:00'),
                        ('Pacific/Galapagos', '-06:00', '-06:00'),
                        ('Pacific/Gambier', '-08:59', '-08:59'),
                        ('Pacific/Guadalcanal', '+11:00', '+11:00'),
                        ('Pacific/Guam', '+10:00', '+10:00'),
                        ('Pacific/Honolulu', '-10:00', '-10:00'),
                        ('Pacific/Johnston', '-10:00', '-10:00'),
                        ('Pacific/Kiritimati', '+14:00', '+14:00'),
                        ('Pacific/Kosrae', '+11:00', '+11:00'),
                        ('Pacific/Kwajalein', '+12:00', '+12:00'),
                        ('Pacific/Majuro', '+12:00', '+12:00'),
                        ('Pacific/Marquesas', '-09:30', '-09:30'),
                        ('Pacific/Midway', '-11:00', '-11:00'),
                        ('Pacific/Nauru', '+12:00', '+12:00'),
                        ('Pacific/Niue', '-11:00', '-11:00'),
                        ('Pacific/Norfolk', '+11:30', '+11:30'),
                        ('Pacific/Noumea', '+11:00', '+11:00'),
                        ('Pacific/Pago_Pago', '-11:00', '-11:00'),
                        ('Pacific/Palau', '+09:00', '+09:00'),
                        ('Pacific/Pitcairn', '-08:00', '-08:00'),
                        ('Pacific/Pohnpei', '+11:00', '+11:00'),
                        ('Pacific/Port_Moresby', '+10:00', '+10:00'),
                        ('Pacific/Rarotonga', '-10:00', '-10:00'),
                        ('Pacific/Saipan', '+10:00', '+10:00'),
                        ('Pacific/Tahiti', '-10:00', '-10:00'),
                        ('Pacific/Tarawa', '+12:00', '+12:00'),
                        ('Pacific/Tongatapu', '+13:00', '+13:00'),
                        ('Pacific/Wake', '+12:00', '+12:00'),
                        ('Pacific/Wallis', '+12:00', '+12:00'),
                        ('GMT', '-00:00', '-00:00'),
                        ('UTC', '-00:00', '-00:00');

INSERT INTO `locale` ( `locale_short_name`, `locale_long_name`, `locale_img`) VALUES
('en', 'English', 'en.png'),
('fr', 'French', 'fr.png');

-- Insert Centreon Backup base conf
INSERT INTO `options` (`key`, `value`)
VALUES
('backup_enabled', '0'),
('backup_configuration_files', '1'),
('backup_database_centreon', '1'),
('backup_database_centreon_storage', '1'),
('backup_database_type', '1'),
('backup_database_full', ''),
('backup_database_partial', ''),
('backup_backup_directory', '/var/cache/centreon/backup'),
('backup_tmp_directory', '/tmp/backup'),
('backup_retention', '7'),
('backup_mysql_conf', '/etc/my.cnf.d/centreon.cnf');

-- Insert Centreon Knowledge base conf
INSERT INTO `options` (`key`, `value`)
VALUES ('kb_db_name', ''),
('kb_db_user', ''),
('kb_db_password', ''),
('kb_db_host', ''),
('kb_db_prefix', ''),
('kb_wiki_url', '');


-- Insert Centreon Partitioning base conf
INSERT INTO `options` (`key`, `value`)
VALUES
  ('partitioning_retention', 365),
  ('partitioning_retention_forward', 10),
  ('partitioning_backup_directory', '/var/cache/centreon/backup');

-- Insert local authentication provider configuration
INSERT INTO `provider_configuration` (type, name, is_active, is_forced)
VALUES ('local', 'local', true, true);

INSERT INTO `password_security_policy`
(`password_length`, `uppercase_characters`, `lowercase_characters`, `integer_characters`, `special_characters`,
`attempts`, `blocking_duration`, `password_expiration`, `delay_before_new_password`)
VALUES (12, '1', '1', '1', '1', 5, 900, 7776000, 3600);
