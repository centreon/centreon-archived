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

$lang["m_main_menu"] = "Main Menu";
$lang["m_connected_users"] = "Online Users";

# Monitoring menu

$lang["m_host_detail"] = "Host detail";
$lang["m_hosts_problems"] = "Hosts problems";
$lang["m_hostgroup_detail"] = "Host Group detail";

$lang["m_service_detail"] = "Service detail";
$lang["m_services_problems"] = "Services problems";
$lang["m_servicegroup_detail"] = "Service Group detail";
$lang["m_service_by_service_group"] = "Services by Svc Grp";

$lang["m_status_scheduling"] = "Status and scheduling";
$lang["m_status_summary"] = "Status Summary";
$lang["m_status_resume"] = "Status Resume";

$lang["m_status_grid"] = "Status Grid";
$lang["m_scheduling"] = "Scheduling Queue";

$lang['m_tools'] = "Tools";
$lang["m_process_info"] = "Process Info";
$lang["m_event_log"] = "Event Log";
$lang["m_downtime"] = "Downtime";
$lang["m_comments"] = "Comments";

$lang["m_alerts"] = "Alerts history";

# Log Menu

$lang["m_all_logs"] = "All Logs";
$lang["m_notify_logs"] = "Notifications";
$lang["m_alerts_log"] = "Alerts";
$lang["m_warning_log"] = "Errors/Warnings";

# Reporting menu

$lang["m_report"] = "Reports";
$lang["m_rtList"] = "Report List";
$lang["m_rtStat"] = "Stats";

$lang["m_rtNotif"] = "Diffusion";
$lang["m_rtMailList"] = "Diffusion List";
$lang["m_rtMail"] = "Mail DB";

$lang["m_message"] = "Message";
$lang["m_status_map"] = "Carte de Status des Hosts";
$lang["m_cartography"] = "Cartography";
$lang["m_dashboard"] = "Dashboard";
$lang["m_dashboardHost"] = "Host";
$lang["m_dashboardService"] = "Service";

# Graph menu

$lang['m_views_loc'] = "Localisation";
$lang['m_views_cty'] = "Countries & Cities";
$lang['m_views_map'] = "Maps";
$lang['m_views_graphs'] = "RRD Engine";
$lang['m_views_graphCustom'] = "Custom Graphs";
$lang['m_views_graphShow'] = "Simple Graphs Renderer";
$lang['m_views_graphPlu'] = "Graphs Plugins";
$lang['m_views_graphTmp'] = "Graphs Templates";
$lang['m_views_compoTmp'] = "Components Templates";
$lang['m_views_mine'] = "My Views";

# Options menu

$lang['m_opt_conf'] = "Oreon";
$lang['m_general'] = "General Options";
$lang['m_lang'] = "Language";
$lang['m_menu'] = "Menu";
$lang['m_plugins'] = "Plugins";
$lang['m_myAccount'] = "My Account";

$lang['m_acl'] = "ACL";
$lang["lca_list"] = "Access Control Lists";

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
$lang["m_commands"] = "Commands";


/* ID Menu */

$lang["m_idCards"] = "ID Cards";
$lang["m_id_serv"] = "Servers";
$lang["m_id_network"] = "Network Equipments";
$lang["m_idUpdate"] = "Manual Update";
$lang["m_id_manu"] = "Manufacturer";

/* Plugins */

$lang["plugins1"] = "Plugins deleted";
$lang["plugins2"] = "Are you sure you want to delete this plugin ? ";
$lang["plugins3"] = "Plugin sent";
$lang["plugins4"] = "A error occured during Plugin upload. May be a right problem";
$lang["plugins5"] = "A error occured during &#146;oreon.conf&#146; file creation. May be a right problem";
$lang["plugins6"] = "Generated file";
$lang["plugins_add"] = "Add plugins for Nagios";
$lang["plugins"] = "Plugins";
$lang["plugins_list"] = "List of plugins";
$lang["plugins_pm_conf"] = "Oreon.conf";
$lang["plugins_pm_conf_desc"] = "Generate configuration file for Oreon.pm with informations include in General menu";

/* index100 */

$lang['ind_infos'] = "In this section, you can configure all Nagios items";
$lang['ind_detail'] = "Ressources are linked together, be careful when you modify or delete one item, related items will be removed too.";

/* index */

$lang['ind_first'] = "You are already connected to OREON, firstly, close the other session<br>If this window is only the first windo you&acute;ve got, click";

/* alt main */

$lang['am_intro'] = "monitoring now :";
$lang['host_health'] = "Host health";
$lang['service_health'] = "Service health";
$lang['network_health'] = "Network health";
$lang['am_hg_vdetail'] = 'View Hostgroup details';
$lang['am_sg_vdetail'] = 'View Servicegroup details';
$lang['am_hg_detail'] = 'Hostgroup details';
$lang['am_sg_detail'] = 'Servicegroup details';

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
$lang['mon_status_info'] = "Status Information";
$lang['mon_last_status_check'] = "Last Status Check";
$lang['mon_status_data_age'] = "Status Data Age";
$lang['mon_current_state_duration'] = "Current State Duration";
$lang['mon_last_host_notif'] = "Last Host Notification";
$lang['mon_current_notif_nbr'] = "Current Notification Number";
$lang['mon_is_host_flapping'] = "Is This Host Flapping ?";
$lang['mon_percent_state_change'] = "Percent State Change";
$lang['mon_is_sched_dt'] = "Is Scheduled Downtime ?";
$lang['mon_last_update'] = "Last Update";
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
$lang['mon_percent_state_change'] = "Percent State Change";
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
$lang['mon_request_submit_host'] = "Your request has been recorded<br><br>You&#146;re gonna be redirected to the host page.";
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
$lang["cmt_added"] = "Comment added with succes. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ";
$lang["cmt_del"] = "Comment deleted with succes. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ";
$lang["cmt_del_all"] = "All Comments deleted with succes. <br><br>Click <a href='./oreon.php?p=307' class='text11b'>here</a> to return to the comments page. ";
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

$lang['cmd_utils'] = 'Useful';
$lang["cmd_send"] = "Your command has been send";
$lang["cmd_ping"] = "Ping";
$lang["cmd_traceroute"] = "Traceroute.";

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

/* host */

$lang['h_available'] = "Available Host";
$lang['h'] = "Host ";
$lang['h_services'] = "Associated Service(s)";
$lang['h_hostgroup'] = "Associated Host Group(s)";
$lang['h_dependancy'] = "Belonging Host Groups ";
$lang['h_nbr_dup'] = "Quantity to duplicate";

/* extended host infos */

$lang['ehi'] = "Extended Host Informations";
$lang['ehi_available'] = "Available extended informations for Hosts ";
$lang['ehi_notes'] = "Note";
$lang['ehi_notes_url'] = "Note address";
$lang['ehi_action_url'] = "Action url";
$lang['ehi_icon_image'] = "Icon image";
$lang['ehi_icon_image_alt'] = "Icon image alt";

/* host template model*/

$lang['htm_available'] = "Host(s) Template(s) Model(s) available(s)";
$lang['htm'] = "Host Template Model ";
$lang['htm_use'] = "Use a Host Template Model";
$lang['htm_stats1'] = "This Template Model is used by ";

/* host group */

$lang['hg_title'] = "Host Groups ";
$lang['hg_available'] = "Available Host Group(s)";
$lang['hg'] = "Host Groups";
$lang['hg_belong'] = "Host Groups belonged";

/* host group escalation */

$lang['hge'] = "Host Group Escalations";
$lang['hge_available'] = "Available(s) Host Group escalation";

/* host escalation */

$lang['he'] = "Host Escalations";
$lang['he_available'] = "Available(s) Host Escalation";

/* host dependencies */

$lang['hd'] = "Host Dependencies";
$lang['hd_available'] = "available Host Dependencies";
$lang['hd_dependent'] = "Host Dependent";

/* host template model */

$lang['htm'] = "Host Template Model";
$lang['htm_u'] = "Use as a Host Template Model";
$lang['htm_v'] = "Use Template in Host monitoring";

/* service escalation */

$lang['se'] = "Service Escalation";
$lang['se_available'] = "Available(s) Service Escalation";

/* service */

$lang['s_ping_response'] = "ping response";
$lang['s_logged_users'] = "logged users";
$lang['s_free_disk_space'] = "free disk space";
$lang['s_available'] = "Available Services";
$lang['s_contact_groups'] = "Contact Groups :";
$lang['s'] = "Service";

/* extended service infos */

$lang['esi'] = "Extended Service Informations";
$lang['esi_available'] = "Available Extended Informations for Services ";
$lang['esi_notes'] = "Note";
$lang['esi_notes_url'] = "Note address";
$lang['esi_action_url'] = "Action url";
$lang['esi_icon_image'] = "Icon image";
$lang['esi_icon_image_alt'] = "Icon image alt";

/* service template model*/

$lang['stm_available'] = "Service(s) Template(s) Model(s) available(s)";
$lang['stm'] = "Service Template Model ";
$lang['stm_use'] = "Use a Service Template Model";
$lang['stm_stats1'] = "This Template Model is used by ";

/* service dependencies */

$lang['sd'] = "Service Dependencie";
$lang['sd_available'] = "available Service Dependencies";
$lang['sd_dependent'] = "Service Dependent";

/* service group*/

$lang['sg_available'] = "Available Service Group";
$lang['sg'] = "Service Group";

/* contact */

$lang['c_available'] = "Available Contact(s)";
$lang['c'] = "Contact";
$lang['c_use'] = "This Contact is use in the Contact Groups :";

/* contact group */

$lang['cg_title'] = "Contact Group";
$lang['cg_available'] = "Availables Contact Groups";
$lang['cg'] = "Contact Group";
$lang['cg_related'] = " is used with ";

/* time period */

$lang['tp_title'] = "Time Period";
$lang['tp_notifications'] = "notifications ";
$lang['tp_service_check'] = "service check ";
$lang['tp_name'] = "Time Period name";
$lang['tp_alias'] = "Alias ";
$lang['tp_sunday'] = "Sunday ";
$lang['tp_monday'] = "Monday ";
$lang['tp_tuesday'] = "Tuesday ";
$lang['tp_wednesday'] = "Wednesday ";
$lang['tp_thursday'] = "Thursday ";
$lang['tp_friday'] = "Friday ";
$lang['tp_saturday'] = "Saturday ";
$lang['tp_available'] = "Available Time Period";
$lang['tp'] = "Time Period(s) ";
$lang['tp_more_ex'] = " is used like a check Command on following Hosts :";
$lang['tp_more_ex2'] ="is like an event handler on following Hosts :";

/* command */

$lang['cmd_title'] = "Command";
$lang['cmd_notifications'] = "Service notifications ";
$lang['cmd_service_check'] = "Service check ";
$lang['cmd_event'] = "Service event handler ";
$lang['cmd_host_check'] = "Host check ";
$lang['cmd_host_notifications'] = "Host notifications ";
$lang['cmd_host_event_handler'] = "Host event handler ";
$lang['cmd_comment'] = "Command definitions can contain macros, but you must make sure that you include only those macros that are &quot;valid&quot; for the circumstances when the command will be used.";
$lang['cmd_macro_infos'] = "More informations about macro can be found here :";
$lang['ckcmd_available'] = "Available Check-Command(s)";
$lang['ntcmd_available'] = "Available Notification-Command(s)";
$lang['cmd_name'] = "Command name";
$lang['cmd_line'] = "Command line ";
$lang['cmd'] = "Command(s) ";
$lang['cmd_more_ex'] = " is used like a check command on following hosts :";
$lang['cmd_more_ex2'] =" is used like an event_Handler on following hosts :";
$lang['cmd_type'] = "Command type";

/* Load Nagios CFG */

$lang['nfc_generated_by_oreon'] = 'Are the files generated with Oreon ?';
$lang['nfc_targz'] = 'You have to upload a tar.gz file';
$lang['nfc_limit'] = 'To load a Nagios configuration, you have to :<ul><li>Specify at least the misccommands.cfg and checkcommands.cfg files</li><li>Other definition can be in any file you want</li><li>Oreon doesn\'t manage the Nagios time-saving tricks</li></ul>';
$lang['nfc_enum'] = "Hosts, services, contacts, commands, escalations, templates....";
$lang['nfc_ncfg'] = "Nagios.cfg";
$lang['nfc_rcfg'] = "Resource.cfg";
$lang['nfc_ncfgFile'] = "Nagios.cfg file";
$lang['nfc_rcfgFile'] = "Resource.cfg file";
$lang['nfc_fileUploaded'] = "Files uploaded correctly";
$lang['nfc_extractComplete'] = "Extract Complete";
$lang['nfc_unzipComplete'] = "Unzip Complete";
$lang['nfc_unzipUncomplete'] = "Unzip Uncomplete";
$lang['nfc_uploadComplete'] = "Upload Complete";

/* profile */

$lang['profile_h_name'] = "Name";
$lang['profile_h_contact'] = "Contact";
$lang['profile_h_location'] = "Location";
$lang['profile_h_uptime'] = "Uptime";
$lang['profile_h_os'] = "Operating system";
$lang['profile_h_interface'] = "Interface";
$lang['profile_h_ram'] = "Memory";
$lang['profile_h_disk'] = "Disk";
$lang['profile_h_software'] = "Software";
$lang['profile_h_update'] = "Windows update";
$lang['profile_s_network'] = "By network";
$lang['profile_s_os'] = "By operating system";
$lang['profile_s_software'] = "By software";
$lang['profile_s_update'] = "By Windows update";
$lang['profile_s_submit'] = "search";
$lang['profile_o_system'] = "System";
$lang['profile_o_network'] = "Network";
$lang['profile_o_storage'] = "Storage";
$lang['profile_o_software'] = "Software";
$lang['profile_o_live_update'] = "Live update";
$lang['profile_h_ip'] = "IP";
$lang['profile_h_speed'] = "Speed";
$lang['profile_h_mac'] = "Mac";
$lang['profile_h_status'] = "Status";
$lang['profile_h_used_space'] = "Used space";
$lang['profile_h_size'] = "Size";
$lang['profile_h_partition'] = "Partition";
$lang['profile_h_list_host'] = "Select your server";
$lang['profile_menu_list'] = "Hosts";
$lang['profile_menu_search'] = "Search";
$lang['profile_menu_options'] = "Inventory";
$lang['profile_search_results'] = "Search results for :";
$lang['profile_title_partition'] = "Partition";
$lang['profile_title_size'] = "Size";
$lang['profile_title_used_space'] = "Used space";
$lang['profile_title_free_space'] = "Free space";
$lang['profile_error_snmp'] = "The SNMP deamon does not seem to running on host target";

/* db */

$lang['db_cannot_open'] = "File cannot be open :";
$lang['db_cannot_write'] = "Unable to write into file :";
$lang['db_genesis'] = "Generate configuration files";
$lang['db_file_state'] = "Generated files's current state :";
$lang['db_create_backup'] = "You should backup before creating new configuration file";
$lang['db_create'] = "Create Database";
$lang['db_generate'] = "Generate";
$lang['db_nagiosconf_backup'] = "Nagios configuration backup ";
$lang['db_backup'] = "All Oreon's database backup";
$lang['db_nagiosconf_backup_on_server'] = "Backup Nagios configuration on server.";
$lang['db_backup_spec_users'] = "backup user's configuration ";
$lang['db_insert_new_database'] = "Insert a new database";
$lang['db_reset_old_conf'] = "Load an old registered configuration";
$lang['db_extract'] = "Extract";
$lang['db_execute'] = "Execute";
$lang['db_save'] = "Save";
$lang["DB_status"] = "DataBase Statistics";
$lang["db_lenght"] = "Lenght";
$lang["db_nb_entry"] = "Entries Number";

/* user */

$lang['u_list'] = "Users&acute;s list";
$lang['u_admin_list'] = "Administrator list";
$lang['u_sadmin_list'] = "Super administrator list";
$lang['u_user'] = "User";
$lang['u_administrator'] = "Administrator";
$lang['u_sadministrator'] = "Super administrator";
$lang['u_profile'] = "Your profile";
$lang['u_new_profile'] = "New profile";
$lang['u_some_profile'] = "Profile for ";
$lang['u_name'] = "Name ";
$lang['u_lastname'] = "Lastname ";
$lang['u_login'] = "Login ";
$lang['u_passwd'] = "Password ";
$lang['u_cpasswd'] = "Change password";
$lang['u_ppasswd'] = "Confirm password ";
$lang['u_email'] = "Email ";
$lang['u_lang'] = "Choosen language ";
$lang['u_status'] = "Status ";
$lang['u_delete_profile'] = "delete this profile";

/* lang */

$lang['lang_infos'] = "There is already ";
$lang['lang_infos2'] = "different languages reday to use.";
$lang['lang_infos3'] = "If you want to add a new one. You have to upload a file in the following form";
$lang['lang_detail'] = "This file should have same fields like ";
$lang['lang_detail2'] = "in the choosen language";

/* bug resolver */

$lang['bug_infos'] = "On this page, you can erase all relations beetween ressources and database content which can contents errors if there is a bug.";
$lang['bug_action'] = "Click here if you want to reset database if you get bugs while tests step, thanks to report us which step failed.";
$lang['bug_kick'] = "Reset it";

/* Parseenevlog */

$lang['hours'] = "Hours";

/* Log report */

$lang['add_report'] = "The report has been added";
$lang['change_report'] = "The report has been changed";
$lang['add_reportHost'] = "A new Host has been added";
$lang['add_reportService'] = "Service(s) has been added";
$lang['daily_report'] = "Daily report (choose format)";
$lang['report_select_host'] = "select host";
$lang['report_select_service'] = "one of his services (not required)";
$lang['report_select_period'] = "select a period";
$lang['report_sp'] = "start period";
$lang['report_ep'] = "end period";
$lang['report_generate_pdf'] = "Generate PDF report";
$lang['custom_start_date'] = "custom start date";
$lang['custom_end_date'] = "custom end date";
$lang['report_change_host'] = "change host";
$lang['custom_report'] = "Custom Report";
$lang['report_color_up'] = "Color UP";
$lang['report_color_down'] = "Color DOWN";
$lang['report_color_unreachable'] = "Color UNREACHABLE";
$lang['report_color_ok'] = "Color OK";
$lang['report_color_warning'] = "Color WARNING";
$lang['report_color_critical'] = "Color CRITICAL";
$lang['report_color_unknown'] = "Color UNKNOWN";
$lang['report_kindof_report'] = "There is 3 kind of report";
$lang['report_daily_report'] = "The Actual Nagios status Report";
$lang['report_daily_report_explain'] = "It interprets this file :";
$lang['report_daily_report_availability'] = "Available in those formats :";
$lang['report_spec_info'] = "The specific information report";
$lang['report_spec_info_explain'] = "You can easily check immediately an host or/and his associated service(s) like :";
$lang['report_spec_info_ex1'] = "Host status during a specific period";
$lang['report_spec_info_ex2'] = "Service status during a specific period";
$lang['report_spec_info_ex3'] = "All services status associated to a host during a specific period";
$lang['available'] = "Available in those formas :";
$lang['report_cont_info'] = "The continous information report";
$lang['report_cont_info_explain'] = "Used if you want to get information on each interval you have selected, it works like :";
$lang['report_cont_info_ex1'] = "notification by mail every day of status from the day before of a host(s)/services(s) selection";
$lang['report_cont_info_ex2'] = "notification by mail every week of status from the week before of a host(s)/services(s) selection";
$lang['report_cont_info_ex3'] = "notification by mail everymonth of status from the month before of a host(s)/services(s) selection";
$lang['report_logs_explain'] = "Those logs restart every time Nagios is shutting down";

/* Traffic Map */

$lang['tm_update'] = "The Traffic Map has been updated";
$lang['tm_available'] = "Traffic Map available";
$lang['tm_add'] = "Traffic Map add";
$lang['tm_modify'] = "Traffic Map modify";
$lang['tm_delete'] = "Traffic Map removed";
$lang['tm_addHost'] = "A new Host had been added to the traffic map";
$lang['tm_changeHost'] = "The Host has been changed";
$lang['tm_deleteHost'] = "The Host has been removed";
$lang['tm_addRelation'] = "A new relation had been added";
$lang['tm_changeRelation'] = "The relation has been changed";
$lang['tm_deleteRelation'] = "The relation has been removed";
$lang['tm_hostServiceAssociated'] = "Hosts with a service check_traffic associated";
$lang['tm_checkTrafficAssociated'] = "Check_traffic associated";
$lang['tm_other'] = "Other ressources (without check_traffic)";
$lang['tm_networkEquipment'] = "Network equipment";
$lang['tm_selected'] = "selected";
$lang['tm_maxBWIn'] = "Max bandwith possible In (Kbps)";
$lang['tm_maxBWOut'] = "Max bandwith possible Out (Kbps)";
$lang['tm_background'] = "Background image";
$lang['tm_relations'] = "Relation(s)";
$lang['tm_hostsAvailables'] = "Host(s) Available";
$lang['tm_labelsWarning'] = "Veuillez saisir un label sans accents";

/* Graphs */

$lang['graph'] = "Graph";
$lang['graphs'] = "Graphs";
$lang['g_title'] = "Graphs";
$lang['g_available'] = "Graphs available";
$lang['g_path'] = "Path of the RRDtool data-base";
$lang['g_imgformat'] = "Picture format";
$lang['g_verticallabel'] = "Vertical label";
$lang['g_width'] = "Picture size - width";
$lang['g_height'] = "Picture size - height";
$lang['g_lowerlimit'] = "Lower limit";
$lang['g_Couleurs'] = "Colors : ";
$lang['g_ColGrilFond'] = "Central graph background color";
$lang['g_ColFond'] = "Background color";
$lang['g_ColPolice'] = "Font color";
$lang['g_ColGrGril'] = "Main grid color";
$lang['g_ColPtGril'] = "Second grid color";
$lang['g_ColContCub'] = "Cube color";
$lang['g_ColArrow'] = "Arrow option color";
$lang['g_ColImHau'] = "Up picture - color";
$lang['g_ColImBa'] = "Down picture - color";
$lang['g_dsname'] = "Name of the data source";
$lang['g_ColDs'] = "Data source color";
$lang['g_flamming'] = "Flamming color";
$lang['g_Area'] = "Area";
$lang['g_tickness'] = "Tickness";
$lang['g_gprintlastds'] = "Show the last calculated value";
$lang['g_gprintminds'] = "Show the min calculated value";
$lang['g_gprintaverageds'] = "Show the average calculated value";
$lang['g_gprintmaxds'] = "Show the max calculated value";
$lang['g_graphorama'] = "GraphsVision";
$lang['g_graphoramaerror'] = "The date of beginning must be lower than the completion date";
$lang['g_date_begin'] = "Start time";
$lang['g_date_end'] = "End time";
$lang['g_hours'] = "Hours";
$lang['g_number_per_line'] = "Number per line";
$lang['g_height'] = "Weight";
$lang['g_width'] = "Width";
$lang['g_basic_conf'] = "Basic configuration :";
$lang['g_ds'] = "Data source";
$lang['g_lcurrent'] = "Current";
$lang['g_lday'] = "Last day";
$lang['g_lweek'] = "last week";
$lang['g_lyear'] = "Last year";
$lang['g_see'] = "See graph associated";
$lang['g_from'] = "From ";
$lang['g_to'] = " To ";
$lang['g_current'] = "Current:";
$lang['g_average'] = "Average:";
$lang['g_no_graphs'] = "No graph available";
$lang['g_no_access_file'] = "File %s is not accessible";

/* Graph Models */

$lang['gmod'] =  'Basic properties';
$lang['gmod_ds'] =  'Data source';
$lang['gmod_available'] = 'Graph properties models available';
$lang['gmod_ds_available'] = 'Graph DS models available';
$lang['gmod_use_model'] = 'Use a model';

/* Colors */
$lang['colors'] =  "Colors";
$lang['hexa'] =  "Color in hexadecimal";

/* Nagios.cfg */

$lang['nagios_save'] = 'La configuration a &eacute;t&eacute; sauvegard&eacute;.<br> Vous devez maintenant d&eacute;placer les fichiers et relancer Nagios pour que les changements soient pris en compte.';

/* Ressources.cfg */

$lang['resources_example'] = 'Resource file example';
$lang['resources_add'] = 'Add a new resource';
$lang['resources_new'] = 'A new resource has been added';

/* lca */

$lang['lca_user'] = 'User :';
$lang['lca_user_access'] = 'has acces to ';
$lang['lca_profile'] = 'profile';
$lang['lca_user_restriction'] = 'Users with access restrictions';
$lang['lca_access_comment'] = 'Enable acces to Comment :';
$lang['lca_access_downtime'] = 'Enable acces to Downtime :';
$lang['lca_access_watchlog'] = 'Enable to watch log :';
$lang['lca_access_trafficMap'] = 'Enable to watch traffic map :';
$lang['lca_access_processInfo'] = 'Enable to acces to process info :';
$lang['lca_add_user_access'] = 'Add attributs to an User';
$lang['lca_apply_restrictions'] = 'Apply restrictions';
$lang['lca_action_on_profile'] = 'Actions';

/* History */

$lang['log_detail'] = "Logs Detail for ";

/* General Options */

$lang["opt_gen"] = "General Options";
$lang["nagios_version"] = "Nagios version : ";
$lang["oreon_path"] = "Oreon installation folder";
$lang["oreon_path_tooltip"] = "Where Oreon is installed ?";
$lang["nagios_path"] = "Nagios installation folder";
$lang["nagios_path_tooltip"] = "Where is Nagios folder ?";
$lang["refresh_interface"] = "Interface refresh";
$lang["refresh_interface_tooltip"] = "Frontend reload frequency";
$lang["snmp_com"] = "SNMP community";
$lang["snmp_com_tooltip"] = "Default SNMP community";
$lang["snmp_version"] = "SNMP version";
$lang["snmp_path"] = "SNMP installation folder";
$lang["snmp_path_tooltip"] = "Where are snmpwalk and snmpget binary ?";
$lang["cam_color"] = "Pie Colors";
$lang["for_hosts"] = "For hosts";
$lang["for_services"] = "For services";
$lang["rrd_path"] = "RRDToolsPath/rrdtool";
$lang["rrd_path_tooltip"] = "Where rrdtool is installed ?";
$lang["rrd_base_path"] = "RRDTool base location";
$lang["rrd_base_path_tooltip"] = "Where the rrd databases are generated ?";
$lang["mailer"] = "Mailer";
$lang["mailer_tooltip"] = "Where mail binary is installed ?";
$lang["opt_gen_save"] = "General Options saved.<br>You don't have to generate the files.";
$lang["session_expire"] = "Session expiration time";
$lang["session_expire_unlimited"] = "unlimited";
$lang["binary_path"] = "Nagios Binary path";
$lang["binary_path_tooltip"] = "Where is Nagios binary ?";
$lang["images_logo_path"] = "Nagios picture path";
$lang["images_logo_path_tooltip"] = "Where are Nagios pictures ?";
$lang["plugins_path"] = "Nagios Plugins Path";
$lang["plugins_path_tooltip"] = "Where are Nagios plugins installed ?";
$lang["path_error_legend"] = "Color of the errors";
$lang["invalid_path"] = "The directory or file do not exist";
$lang["executable_binary"] = "The file is not executable";
$lang["writable_path"] = "The directory or file is not writable";
$lang["readable_path"] = "The directory is not readable";
$lang["rrdtool_version"] = "RRDTool version";
$lang["nmap_path"] = "Nmap binary path";
$lang["nmap_path_tooltip"] = "Where is nmap binary ?";

/* Auto Detect */

$lang['ad_title'] = "Automatic Host research";
$lang['ad_title2'] = "Automatic research";
$lang['ad_ser_result'] = "Automatic research discovered the following Services : ";
$lang['ad_ser_result2'] = "This list is not an exhaustive list and includes only<br> the services networks having opened a port network on the host.";
$lang['ad_infos1'] = "To make automatic research,<br>please fill the fields following with :";
$lang['ad_infos2'] = 'Maybe with an address IP (ex : 192.168.1.45),';
$lang['ad_infos3'] = 'Maybe with IP fields (ex : 192.168.1.1-254),';
$lang['ad_infos4'] = 'Maybe with an IP list :';
$lang['ad_infos5'] = '192.168.1.1,24,38';
$lang['ad_infos6'] = '192.168.*.*';
$lang['ad_infos7'] = '192.168.10-34.23-25,29-32';
$lang['ad_ip'] = 'IP';
$lang['ad_res_result'] = 'Research result';
$lang['ad_found'] = "found(s)";
$lang['ad_number'] = "Number";
$lang['ad_dns'] = "DNS";
$lang['ad_actions'] = "Actions";
$lang['ad_port'] = "Port";
$lang['ad_name'] = "Name";

/* Export DB */

$lang['edb_file_already_exist'] = "This file already exist, please enter a new name for your backup";
$lang['edb_file_move'] = "Moved files";
$lang['edb_file_ok'] = "Generated and moved files";
$lang['edb_file_nok'] = "Error during the generation or the displacement of the files";
$lang['edb_restart'] = "Host restart";
$lang['edb_save'] = "Make a backup";
$lang['edb_nagios_restart'] = "Restart Nagios";
$lang['edb_nagios_restart_ok'] = "Nagios restart";
$lang['edb_restart'] = "Restart";

/* User_online */

$lang["wi_user"] = "Users";
$lang["wi_where"] = "Where";
$lang["wi_last_req"] = "Last Requete";

/* Reporting */

$lang["pie_unavailable"] = "No Pie available for the moment";

/* Configuration Stats */

$lang['conf_stats_category'] = "Category";

/* Pictures */

$lang["pict_title"] = "Oreon Extended Infos Pictures";
$lang["pict_new_image"] = "New image (only .png)";

/* About */

$lang["developped"] = "Developed by";

/* Live Report */

$lang["lr_available"] = "Available  Hosts";
$lang["live_report"] = "Live Report";
$lang["bbreporting"] = "Reporting";
$lang["lr_host"] = "Host :";
$lang["lr_alias"] = "Alias :";
$lang["lr_ip"] = "IP Address :";
$lang["lr_view_services"] = "View Services Details for this host";
$lang["lr_configure_host"] = "Configure this host";
$lang["lr_details_host"] = "View host Informations";

/* Date and Time Format */

$lang["date_format"] = "Y/m/d";
$lang["time_format"] = "H:i:s";
$lang["header_format"] = "Y/m/d G:i";
$lang["date_time_format"] = "Y/m/d - H:i:s";
$lang["date_time_format_status"] = "d/m/Y H:i:s";
$lang["date_time_format_g_comment"] = "Y/m/d H:i";

/* */

$lang["top"] = "Top";
$lang["event"] = "Events";
$lang["date"] = "Date";
$lang["pel_l_details"] = "Logs Details for ";
$lang["pel_sort"] = "Filters";
$lang["pel_alerts_title"] = "Alerts for ";
$lang["pel_notify_title"] = "Notifications for ";

/* perfparse */

$lang["perfparse_installed"] = "Is Perfparse installed ?";
$lang["service_logged"] = "Logged services";

/* legend */

$lang["lgd_legend"] = " Legend";
$lang["lgd_force"] = " Force Check";
$lang["lgd_graph"] = " Graph";
$lang["lgd_notification"] = " Notification desactivated";
$lang["lgd_passiv"] = " Passiv check activated";
$lang["lgd_work"] = " Acknowledge Activated";
$lang["lgd_delOne"] = " Delete";
$lang["lgd_delAll"] = " Delete";
$lang["lgd_duplicate"] = " Duplicate";
$lang["lgd_view"] = " View";
$lang["lgd_edit"] = " Modify";
$lang["lgd_signpost"] = " Detail";
$lang["lgd_next"] = " Next";
$lang["lgd_prev"] = " Previous";
$lang["lgd_on"] = " Enable";
$lang["lgd_off"] = " Disable";

$lang["advanced"] = "Advanced >>";


$lang["quickFormError"] = "impossible to validate, one or more field is incorrect";

$lang["lgd_more_actions"] = " More Actions...";

?>