<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

/*
This file contains all Oreon's text. This method facilitate us to do a multi-language tool.
It will be easy to guess what variables are corresponding to.
*/

/* Error Code */

$lang['not_allowed'] = "You are not allowed to reach this page";
$lang['not_dbPPConnect'] = "Problem with Perfparse Database connection";
$lang['errCode'][-2] = "Object definition not complete";
$lang['errCode'][-3] = "Object definition already exist";
$lang['errCode'][-4] = "Invalid Email, xxx@xxx.xx format";
$lang['errCode'][-5] = "Definition is circular";
$lang['errCode'][-6] = "You have to choose a Host or a Hostgroup";
$lang['errCode'][-7] = "Password is not correct";
$lang['errCode'][-8] = "The date of beginning must be lower than the completion date";
$lang['errCode'][-9] = "Some values are missing";
$lang['errCode'][2] = "Object definition has been changed";
$lang['errCode'][3] = "Object definition has been created";
$lang['errCode'][4] = "Password has been changed";
$lang['errCode'][5] = "Host has been duplicated";

# Pagination 

$lang['pagin_page'] = "Page";

# Menu Level 1

$lang['m_home'] = "Home";
$lang['m_configuration'] = "Configuration";
$lang['m_monitoring'] = "Monitoring";
$lang['m_reporting'] = "Reporting";
$lang['m_views'] = "Oreon' views";
$lang['m_options'] = "Options";
$lang['m_logout'] = "Logout";
$lang['m_help'] = "Help";


# Menu Level 3

$lang['m_main_menu'] = "Main Menu";
$lang['m_connected_users'] = "Online Users";

# Monitoring menu

$lang['m_host_detail'] = "Host detail";
$lang['m_hosts_problems'] = "Hosts problems";
$lang['m_hostgroup_detail'] = "Host Group detail";

$lang['m_service_detail'] = "Service detail";
$lang['m_services_problems'] = "Services problems";
$lang['m_servicegroup_detail'] = "Service Group detail";
$lang['m_service_by_service_group'] = "Services by Svc Grp";

$lang['m_status_scheduling'] = "Status and scheduling";
$lang['m_status_summary'] = "Status Summary";
$lang['m_status_resume'] = "Status Resume";

$lang['m_status_grid'] = "Status Grid";
$lang['m_scheduling'] = "Scheduling Queue";

$lang['m_tools'] = "Tools";
$lang['m_process_info'] = "Process Info";
$lang['m_event_log'] = "Event Log";
$lang['m_downtime'] = "Downtime";
$lang['m_comments'] = "Comments";

$lang['m_alerts'] = "Alerts history";

# Log Menu

$lang['m_all_logs'] = "All Logs";
$lang['m_notify_logs'] = "Notifications";
$lang['m_alerts_log'] = "Alerts";
$lang['m_warning_log'] = "Errors/Warnings";

# Reporting menu

$lang['m_report'] = "Reports";
$lang['m_rtList'] = "Report List";
$lang['m_rtStat'] = "Stats";
$lang['m_rtNotif'] = "Diffusion";
$lang['m_rtMailList'] = "Diffusion List";
$lang['m_rtMail'] = "Mail DB";

$lang['m_message'] = "Message";
$lang['m_status_map'] = "Carte de Status des Hosts";
$lang['m_cartography'] = "Cartography";

$lang['m_dashboard'] = "Dashboard";
$lang['m_dashboardHost'] = "Host";
$lang['m_dashboardService'] = "Service";

# Graph menu

$lang['m_views_loc'] = "Localisation";
$lang['m_views_cty'] = "Countries & Cities";
$lang['m_views_map'] = "Maps";
$lang['m_views_graphs'] = "Graphs";
$lang['m_views_graphCustom'] = "Custom Graphs";
$lang['m_views_graphShow'] = "Simple Graphs Renderer";
$lang['m_views_graphPlu'] = "Graphs Plugins";
$lang['m_views_graphTmp'] = "Graphs Templates";
$lang['m_views_compoTmp'] = "Components Templates";
#$lang['m_views_mine'] = "My Views";

# Options menu

$lang['m_opt_conf'] = "Oreon";
$lang['m_general'] = "General Options";
$lang['m_lang'] = "Language";
$lang['m_modules'] = "Modules";
$lang['m_plugins'] = "Plugins";
$lang['m_myAccount'] = "My Account";

$lang['m_acl'] = "ACL";
$lang['lca_list'] = "Access Control Lists";

$lang['m_db'] = "Database";
$lang['m_extract_db'] = "Extract Database";

$lang['m_server_status'] = "System";

$lang['m_about'] = "About";
$lang['m_web'] = "Site Web";
$lang['m_forum'] = "Forum";
$lang['m_wiki'] = "Wiki";
$lang['m_bug'] = "Bug Track";
$lang['m_donate'] = "Donation";
$lang['m_pro'] = "Professional";

$lang['m_sessions'] = "Sessions";

# Configuration menu

$lang['m_host'] = "Hosts";
$lang['m_hostgroup'] = "Host Groups";
$lang['m_host_extended_info'] = "Host Extended Infos";

$lang['m_service'] = "Services";
$lang['m_serviceByHost'] = "Services By Host";
$lang['m_serviceByHostGroup'] = "Services By Host Group";
$lang['m_servicegroup'] = "Service Groups";
$lang['m_service_extended_info'] = "Service Extended Infos";
$lang['m_meta_service'] = "Meta Services";
$lang['m_mnftr'] = "Manufacturer";
$lang['m_mibs'] = "Load MIBs";

$lang['m_notification'] = "Users";
$lang['m_contact'] = "Contacts";
$lang['m_contactgroup'] = "Contact Groups";
$lang['m_timeperiod'] = "Time Periods";

$lang['m_escalation'] = "Escalations";
$lang['m_hostgroupesc'] = "Host Group Escalations";
$lang['m_hostesc'] = "Host Escalations";
$lang['m_serviceesc'] = "Service Escalations";
$lang['m_metaserviceesc'] = "Meta Service Escalations";

$lang['m_dependencies'] = "Dependencies";
$lang['m_service_dependencies'] = "Service Dependencies";
$lang['m_host_dependencies'] = "Host Dependencies";

$lang['m_template'] = "Templates";
$lang['m_host_template_model'] = "Host Template Models";
$lang['m_service_template_model'] = "Service Template Models";

$lang['m_nagios'] = "Nagios";
$lang['m_nagiosCFG'] = "Nagios CFG";
$lang['m_cgi'] = "CGI CFG";
$lang['m_resource'] = "Resource CFG";
$lang['m_perfparse'] = "Perfparse CFG";
$lang['m_load_nagios'] = "Load";
$lang['m_gen_nagios'] = "Generate";

$lang['m_commandNotif'] = "Notification Commands";
$lang['m_commandCheck'] = "Check Commands";
$lang['m_commandMisc'] = "Various Commands";
$lang['m_commands'] = "Commands";

/* ID Menu */

$lang['m_idCards'] = "ID Cards";
$lang['m_id_serv'] = "Servers";
$lang['m_id_network'] = "Network Equipments";
$lang['m_idUpdate'] = "Manual Update";
$lang['m_id_manu'] = "Manufacturer";

/* Monitoring */

$lang['mon_last_update'] = "Last update :";
$lang['mon_up'] = "UP";
$lang['mon_down'] = "DOWN";
$lang['mon_unreachable'] = "UNREACHABLE";
$lang['mon_ok'] = "OK";
$lang['mon_critical'] = "CRITICAL";
$lang['mon_warning'] = "WARNING";
$lang['mon_pending'] = "PENDING";
$lang['mon_unknown'] = "UNKNOWN";
$lang['mon_status'] = "Status";
$lang['mon_ip'] = "IP";
$lang['mon_last_check'] = "Last Check";
$lang['mon_next_check'] = "Next Check";
$lang['mon_active_check'] = "Active Check";
$lang['mon_duration'] = "Duration";
$lang['mon_retry'] = "Retry";
$lang['mon_status_information'] = "Status information";
$lang['mon_service_overview_fah'] = "Service Overview For All Host Groups";
$lang['mon_service_overview_fas'] = "Service Overview For All Service Groups";
$lang['mon_status_summary_foh'] = "Status Summary For All Host Groups";
$lang['mon_status_grid_fah'] = "Status Grid for ALL Host Groups";
$lang['mon_sv_hg_detail1'] = "Service details";
$lang['mon_sv_hg_detail2'] = "for Host Group";
$lang['mon_sv_hg_detail3'] = "for Host";
$lang['mon_host_status_total'] = "Host Status Total";
$lang['mon_service_status_total'] = "Service Status Total";
$lang['mon_scheduling'] = "Scheduling queue";
$lang['mon_actions'] = "Actions";
$lang['mon_active'] = "ACTIVE";
$lang['mon_inactive'] = "INACTIVE";
$lang['mon_request_submit_host'] = "Your request had been submitted. <br><br>You&#146;re gonna be send at the Host page'.";
$lang['Details'] = "Details";
$lang['mon_checkOutput'] = "check output";
$lang['mon_dataPerform'] = "data perform";


/* Monitoring command */

$lang['mon_hg_commands'] = "Host Group Commands";
$lang['mon_h_commands'] = "Host Commands";
$lang['mon_sg_commands'] = "Service Group Commands";
$lang['mon_s_commands'] = "Service Commands";
$lang['mon_no_stat_for_host'] = "No stat for this Host.<br><br> Think has generate the configuration files.";
$lang['mon_no_stat_for_service'] = "No stat for this Service.<br><br> Think has generate the configuration files.";
$lang['mon_hg_cmd1'] = "Schedule downtime for all hosts in this hostgroup";
$lang['mon_hg_cmd2'] = "Schedule downtime for all services in this hostgroup";
$lang['mon_hg_cmd3'] = "Enable notifications for all hosts in this hostgroup";
$lang['mon_hg_cmd4'] = "Disable notifications for all hosts in this hostgroup";
$lang['mon_hg_cmd5'] = "Enable notifications for all services in this hostgroup";
$lang['mon_hg_cmd6'] = "Disable notifications for all services in this hostgroup";
$lang['mon_hg_cmd7'] = "Enable checks of all services in this hostgroup";
$lang['mon_hg_cmd8'] = "Disable checks of all services in this hostgroup";
$lang['mon_host_state_info'] = "Host State Information";
$lang['mon_hostgroup_state_info'] = "Hostgroup State Information";
$lang['mon_host_status'] = "Host Status";
$lang['mon_last_status_check'] = "Last Status Check";
$lang['mon_status_data_age'] = "Status Data Age";
$lang['mon_current_state_duration'] = "Current State Duration";
$lang['mon_last_host_notif'] = "Last Host Notification";
$lang['mon_current_notif_nbr'] = "Current Notification Number";
$lang['mon_is_host_flapping'] = "Is This Host Flapping ?";
$lang['mon_percent_state_change'] = "Percent State Change";
$lang['mon_is_sched_dt'] = "Is Scheduled Downtime ?";
$lang['mon_sch_imm_cfas'] = "Schedule an immediate check of all services";
$lang['mon_sch_dt'] = "Schedule downtime";
$lang['mon_dis_notif_fas'] = "Disable notifications for all services";
$lang['mon_enable_notif_fas'] = "Enable notifications for all services";
$lang['mon_dis_checks_fas'] = "Disable checks of all services";
$lang['mon_enable_checks_fas'] = "Enable checks of all services";
$lang['mon_service_state_info'] = "Service State Information";
$lang['mon_service_status'] = "Service State";
$lang['mon_current_attempt'] = "Current Attempt";
$lang['mon_state_type'] = "State Type";
$lang['mon_last_check_type'] = "Last Check Type";
$lang['mon_last_check_time'] = "Last Check Time";
$lang['mon_next_sch_active_check'] = "Next Scheduled Active Check";
$lang['mon_last_service_notif'] = "Last Service Notification";
$lang['mon_is_service_flapping'] = "Is This Service Flapping ?";
$lang['mon_checks_for_service'] = "checks of this service";
$lang['mon_accept_pass_check'] = "accepting passive checks for this service";
$lang['mon_notif_service'] = "notifications for this service";
$lang['mon_eh_service'] = "event handler for this service";
$lang['mon_fp_service'] = "flap detection for this service";
$lang['mon_submit_pass_check_service'] = "Submit passive check result for this service";
$lang['mon_sch_dt_service'] = "Schedule downtime for this service";
$lang['mon_service_check_executed'] = "Service Checks Being Executed";
$lang['mon_passive_service_check_executed'] = "Passive Service Checks Being Accepted";
$lang['mon_eh_enabled'] = "Event Handlers Enabled";
$lang['mon_obess_over_services'] = "Obsessing Over Services";
$lang['mon_fp_detection_enabled'] = "Flap Detection Enabled";
$lang['mon_perf_data_process'] = "Performance Data Being Processed";
$lang['mon_process_infos'] = "Process Informations";
$lang['mon_process_start_time'] = "Program Start Time";
$lang['mon_total_run_time'] = "Total Running Time";
$lang['mon_last_ext_command_check'] = "Last External Command Check";
$lang['mon_last_log_file_rotation'] = "Last Log File Rotation";
$lang['mon_nagios_pid'] = "Nagios PID";
$lang['mon_process_cmds'] = "Process Commands";
$lang['mon_stop_nagios_proc'] = "Stop the Nagios Process";
$lang['mon_start_nagios_proc'] = "Start the Nagios Process";
$lang['mon_restart_nagios_proc'] = "Restart the Nagios Process";
$lang['mon_proc_options'] = "Process Options";
$lang['mon_notif_enabled'] = "Notifications Enabled";
$lang['mon_notif_disabled'] = "Notifications Disabled";
$lang['mon_service_check_disabled'] = "Service Check Disabled";
$lang['mon_service_check_passice_only'] = "Passive Check Only";
$lang['mon_service_view_graph'] = "View graph";
$lang['mon_service_sch_check'] = "Schedule an immediate check of this service";

/* comments */

$lang['cmt_service_comment'] = "Service Comments";
$lang['cmt_host_comment'] = "Host Comments";
$lang['cmt_addH'] = "Add a Host comment";
$lang['cmt_addS'] = "Add a Service comment";
$lang['cmt_added'] = "Comment added with succes. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ";
$lang['cmt_del'] = "Comment deleted with succes. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ";
$lang['cmt_del_all'] = "All Comments deleted with succes. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ";
$lang['cmt_host_name'] = "Host Name";
$lang['cmt_service_descr'] = "Services";
$lang['cmt_entry_time'] = "Entry Time";
$lang['cmt_author'] = "Author";
$lang['cmt_comment'] = "Comment";
$lang['cmt_persistent'] = "Persistent";
$lang['cmt_actions'] = "Actions";

/* downtime */

$lang['dtm_addH'] = "Add a Host downtime";
$lang['dtm_addS'] = "Add a Service downtime";
$lang['dtm_addHG'] = "Add a Host Group downtime";
$lang['dtm_added'] = "Downtime added with succes. <br><br>Click <a href='./oreon.php?p=308' class='text11b'>here</a> to return to the Downtimes page. ";
$lang['dtm_del'] = "Downtime deleted with succes. <br><br>Click <a href='./oreon.php?p=308' class='text11b'>here</a> to return to the Downtimes page. ";
$lang['dtm_start_time'] = "Start Time";
$lang['dtm_end_time'] = "End Time";
$lang['dtm_fixed'] = "Fixed";
$lang['dtm_duration'] = "Duration";
$lang['dtm_sch_dt_fht'] = "Schedule Downtime For Hosts Too";
$lang['dtm_host_downtimes'] = "Host Downtimes";
$lang['dtm_service_downtimes'] = "Service Downtimes";
$lang['dtm_dt_no_file'] = "Downtime file not found";
$lang['dtm_host_delete'] = "Delete host downtime";

/* cmd externe */

#$lang['cmd_utils'] = 'Useful';
#$lang['cmd_send'] = "Your command has been send";
#$lang['cmd_ping'] = "Ping";
#$lang['cmd_traceroute'] = "Traceroute.";

/* actions & recurrent text */

$lang['home'] = "Home";
$lang['oreon'] = "Oreon";
$lang['add'] = "Add";
$lang['dup'] = "Duplicate";
$lang['save'] = "Save";
$lang['modify'] = "Modify";
$lang['mchange'] = "Massive Change";
$lang['delete'] = "Delete";
$lang['update'] = "Update";
$lang['ex'] = "Example ";
$lang['name'] = "Name ";
$lang['alias'] = "Alias ";
$lang['user'] = "User ";
$lang['here'] = "here";
$lang['this'] = "this";
$lang['confirm_removing'] = "Do you validate the deletion ?";
$lang['confirm_duplication'] = "Do you validate the duplication ?";
$lang['confirm_mchange'] = "Do you validate the massive change ?";
$lang['confirm_update'] = "Are you sure you want to update the traffic map ?";
$lang['file_exist'] = "Sorry the file already exist.";
$lang['uncomplete_form'] = "Uncomplete form or invalid";
$lang['none'] = "none";
$lang['already_logged'] = "You are already connected to OREON, close firstly the other session. <br> If this window is the only Oreon window, click <br><a href='?disconnect=1' class='text11b'>here</a>";
$lang['usage_stats'] = "Statistics usage";
$lang['check'] = "Check";
$lang['uncheck'] = "Uncheck";
$lang['options'] = "Options";
$lang['status'] = "Status";
$lang['status_options'] = "Status and options";
$lang['details'] = "D&eacute;tails";
$lang['back'] = "Retour";
$lang['view'] = "View";
$lang['choose'] = "Choose";
$lang['enable'] = "Enabled";
$lang['disable'] = "Disabled";
$lang['yes'] = "Yes";
$lang['no'] = "No";
$lang['description'] = "Description";
$lang['page'] = "Page";
$lang['required'] = "<font color='red'>*</font> required";
$lang['nbr_per_page'] = "Nbr per page";
$lang['reset'] = "Reset";
$lang['time_sec'] = " secondes ";
$lang['time_min'] = " minutes ";
$lang['time_hours'] = " Hours ";
$lang['time_days'] = " Days ";
$lang['size'] = "Size";
$lang['close'] = "Close";
$lang['forTheSelectedElements'] = "For the selection : ";
$lang['mc_mod'] = "Update options";
$lang['mc_mod_incremental'] = "Incremental";
$lang['mc_mod_replacement'] = "Replacement";

/* db */

$lang['db_extract'] = "Extract";
$lang['db_execute'] = "Execute";
$lang['db_save'] = "Save";
$lang['DB_status'] = "DataBase Statistics";
$lang['db_lenght'] = "Lenght";
$lang['db_nb_entry'] = "Entries Number";

/* Parseenevlog */

$lang['hours'] = "Hours";

/* User_online */

$lang['wi_user'] = "Users";
$lang['wi_where'] = "Where";
$lang['wi_last_req'] = "Last Requete";

/* About */

$lang['developped'] = "Developed by";

/* Date and Time Format */

$lang['date_format'] = "Y/m/d";
$lang['time_format'] = "H:i:s";
$lang['time_formatWOs'] = "H:i";
$lang['header_format'] = "Y/m/d G:i";
$lang['date_time_format'] = "Y/m/d - H:i:s";
$lang['date_time_format_status'] = "d/m/Y H:i:s";
$lang['date_time_format_g_comment'] = "Y/m/d H:i";

/* legend */

$lang['lgd_legend'] = " Legend";
$lang['lgd_force'] = " Force Check";
$lang['lgd_graph'] = " Graph";
$lang['lgd_notification'] = " Notification disabled";
$lang['lgd_passiv'] = " Passiv check enabled";
$lang['lgd_desactivated'] = " Check not active and not passive";
$lang['lgd_work'] = " Acknowledged";
$lang['lgd_delOne'] = " Delete";
$lang['lgd_delAll'] = " Delete";
$lang['lgd_duplicate'] = " Duplicate";
$lang['lgd_view'] = " View";
$lang['lgd_play'] = " Play";
$lang['lgd_pause']= " Pause";
$lang['lgd_refresh'] = " Refresh";
$lang['lgd_edit'] = " Modify";
$lang['lgd_signpost'] = " Detail";
$lang['lgd_next'] = " Next";
$lang['lgd_prev'] = " Previous";
$lang['lgd_on'] = " Enable";
$lang['lgd_off'] = " Disable";
$lang['advanced'] = "Advanced >>";
$lang['quickFormError'] = "impossible to validate, one or more field is incorrect";
$lang['lgd_more_actions'] = " More Actions...";

?>