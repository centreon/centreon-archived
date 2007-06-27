<?
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

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

# Options Error

$lang['requiredFields'] = "<font style='color: red;'>*</font> Require Fields";
$lang['ErrValidPath'] = "The directory isn't valid";
$lang['ErrReadPath'] = "Can't read directory";
$lang['ErrExeBin'] = "Can't execute binary";
$lang['ErrWrPath'] = "Can't write directory";
$lang['ErrWrFile'] = "Can't write file";

# LCA

$lang['lca_infos'] = "General Informations";
$lang['lca_add'] = "Add a ACL";
$lang['lca_change'] = "Modify a ACL";
$lang['lca_view'] = "View a ACL";
$lang['lca_name'] = "ACL definition";
$lang['lca_comment'] = "Comment";
$lang['lca_type'] = "Type";
$lang['lca_tpMenu'] = "Menu";
$lang['lca_tpRes'] = "Resources";
$lang['lca_tpBoth'] = "Both";
$lang['lca_appCG'] = "Contact Groups implied";
$lang['lca_cg'] = "Contact Groups";
$lang['lca_sortRes'] = "Resources";
$lang['lca_appRes'] = "Resources implied";
$lang['lca_hg'] = "Host Groups";
$lang['lca_hgChilds'] = "Include Host Groups -> Hosts";
$lang['lca_sg'] = "Service Groups";
$lang['lca_host'] = "Hosts";
$lang['lca_sortTopo'] = "Topology";
$lang['lca_appTopo'] = "Page implied";
$lang['lca_topo'] = "Visible Page";

# General Options

$lang['genOpt_change'] = "Modify General Options";
$lang['genOpt_nagios'] = "Nagios informations";
$lang['genOpt_oreon'] = "Centreon informations";
$lang['genOpt_snmp'] = "SNMP informations";
$lang['genOpt_various'] = "Various Informations";
$lang['genOpt_nagPath'] = "Directory";
$lang['genOpt_nagBin'] = "Directory + Binary";
$lang['genOpt_nagScript'] = "Init Script";
$lang['genOpt_nagImg'] = "Images Directory";
$lang['genOpt_nagPlug'] = "Plugins Directory";
$lang['genOpt_nagVersion'] = "Nagios Release";
$lang['genOpt_oPath'] = "Directory";
$lang['genOpt_webPath'] = "Centreon Web Directory";
$lang['genOpt_oRrdbPath'] = "rrd Directory";
$lang['genOpt_oRefresh'] = "Refresh Interval";
$lang['genOpt_oExpire'] = "Sessions Expiration Time";
$lang['genOpt_oHCUP'] = "Host UP Color";
$lang['genOpt_oHCDW'] = "Host DOWN Color";
$lang['genOpt_oHCUN'] = "Host UNREACHABLE Color";
$lang['genOpt_oSOK'] = "Service OK Color";
$lang['genOpt_oSWN'] = "Service WARNING Color";
$lang['genOpt_oSCT'] = "Service CRITICAL Color";
$lang['genOpt_oSPD'] = "Service PENDING Color";
$lang['genOpt_oSUK'] = "Service UNKNOWN Color";
$lang['genOpt_snmpCom'] = "Global Community";
$lang['genOpt_snmpVer'] = "Version";
$lang['genOpt_snmpttconvertmib_path_bin'] = "snmpttconvertmib Directory + Binary";
$lang['genOpt_perl_library_path'] = "Perl library directory";
$lang['genOpt_mailer'] = "Directory + Mailer Binary";
$lang['genOpt_rrdtool'] = "Directory + RRDTOOL Binary";
$lang['genOpt_rrdtoolV'] = "RRDTOOL Version";
$lang['genOpt_perfparse'] = "Using PerfParse";
$lang['genOpt_colorPicker'] = "Pick a color";
$lang['genOpt_maxViewMonitoring'] = "Limit per page for Monitoring";
$lang['genOpt_maxViewConfiguration'] = "Limit per page (default)";
$lang['genOpt_AjaxTimeReloadStatistic'] = "Refresh Interval for statistics";
$lang['genOpt_AjaxTimeReloadMonitoring'] = "Refresh Interval for monitoring";
$lang['genOpt_AjaxFirstTimeReloadStatistic'] = "First Refresh delay for statistics";
$lang['genOpt_AjaxFirstTimeReloadMonitoring'] = "First Refresh delay for monitoring";
$lang['genOpt_snmp_trapd_pathConf'] = "Directory of traps configuration files";
$lang['genOpt_template'] = "Template";
$lang['genOpt_ldap'] = "LDAP informations";
$lang['genOpt_ldap_host'] = "LDAP Server";
$lang['genOpt_ldap_port'] = "LDAP Port";
$lang['genOpt_ldap_base_dn'] = "LDAP Base DN";
$lang['genOpt_ldap_login_attrib'] = "LDAP Login Attribut";
$lang['genOpt_ldap_ssl'] = "Enable LDAP over SSL";
$lang['genOpt_ldap_auth_enable'] = "Enable LDAP authentification";
$lang['genOpt_searchldap'] = "LDAP Search Information";
$lang['genOpt_ldap_search_user'] = "User for search (anonymous if empty)";
$lang['genOpt_ldap_search_user_pwd'] = "Password";
$lang['genOpt_ldap_search_filter'] = "Default LDAP filter";
$lang['genOpt_ldap_search_timeout'] = "LDAP search timeout";
$lang['genOpt_ldap_search_limit'] = "LDAP Search Size Limit";
$lang['genOpt_graph_preferencies'] = "Favorite Graphs Engine";
$lang['genOpt_debug'] = "Debug";
$lang['genOpt_dPath'] = "Logs Directory";
$lang['genOpt_debug_auth'] = "Authentification Debug";
$lang['genOpt_debug_nagios_import'] = "Nagios Import Debug";
$lang['genOpt_debug_rrdtool'] = "RRDTool Debug";
$lang['genOpt_debug_ldap_import'] = "Ldap Import Users Debug";
$lang['genOpt_debug_inventory'] = "Inventory Debug";
$lang['genOpt_debug_clear'] = "&nbsp;Clear debug file";

$lang['genOpt_problem_sort_type'] = "Sort problems by  ";
$lang['genOpt_problem_duration'] = "Duration";
$lang['genOpt_problem_host'] = "Hosts";
$lang['genOpt_problem_service'] = "Services";
$lang['genOpt_problem_status'] = "Status";
$lang['genOpt_problem_last_check'] = "Last check";
$lang['genOpt_problem_output'] = "Output";
$lang['genOpt_problem_sort_order'] = "Order sort problems ";
$lang['genOpt_problem_order_asc'] = "Ascendant";
$lang['genOpt_problem_order_desc'] = "Descendant";

$lang['genOpt_gmt'] = "GMT";

$lang["genOpt_max_page_size"] = "Maximum page size";
$lang["genOpt_refresh_properties"] = "Refresh Properties";
$lang["genOpt_template"] = "Display Template";
$lang["genOpt_display_options"] = "Display Options";
$lang["genOpt_time_zone"] = "Time Zone";
$lang["genOpt_expiration_properties"] = "Sessions Properties";

$lang["genOpt_nagios_properties"] = "Nagios Properties";
$lang["genOpt_nagios_version"] = "Nagios version";
$lang["genOpt_nagios_init_script"] = "Initialisation Script ";
$lang["genOpt_nagios_direstory"] = "Nagios Directories";
$lang["genOpt_mailer_path"] = "Mailer path";

$lang["genOpt_colors_properties"] = "Status Properties Colors";

$lang["genOpt_rrdtool_properties"] = "RRDTool Properties";
$lang["genOpt_rrdtool_configurations"] = "RRDTool Configuration";

$lang["optGen_ldap_properties"] = "LDAP Properties";

$lang["optGen_CSS_properties"] = "CSS configuration";

$lang["genOpt_debug_options"] = "Debug Properties";

$lang["genOpt_ODS_config"] = "ODS Configuration";

$lang['genOpt_css'] = "CSS";
$lang['genOpt_menu_name'] = "Menu";
$lang['genOpt_file_name'] = "CSS file";

# Menu

$lang['mod_menu'] = "Modules Availables";
$lang['mod_menu_modInfos'] = "Module Informations";
$lang['mod_menu_upgradeInfos'] = "Upgrade Informations";
$lang["mod_menu_module_name"] = "Name";
$lang["mod_menu_module_rname"] = "Real name";
$lang["mod_menu_module_release"] = "Release";
$lang["mod_menu_module_release_from"] = "Base release";
$lang["mod_menu_module_release_to"] = "Final release";
$lang["mod_menu_module_author"] = "Author";
$lang["mod_menu_module_additionnals_infos"] = "Additionnals Informations";
$lang["mod_menu_module_is_installed"] = "Installed";
$lang["mod_menu_module_is_validUp"] = "Valid for an upgrade";
$lang["mod_menu_module_is_notvalidIn"] = "Module already install";
$lang["mod_menu_module_invalid"] = "NA";
$lang["mod_menu_module_impossible"] = "Impossible";
$lang["mod_menu_listAction"] = "Actions";
$lang["mod_menu_listAction_del"] = "Uninstall Module";
$lang["mod_menu_listAction_install"] = "Install Module";
$lang["mod_menu_listAction_upgrade"] = "Upgrade";
$lang["mod_menu_output1"] = "Module installed and recorded";
$lang["mod_menu_output2"] = "SQL file included";
$lang["mod_menu_output3"] = "PHP file included";
$lang["mod_menu_output4"] = "Unable to install module";

$lang['menu_ODS'] = "CentreonDataStorage";
$lang['menu_nagios'] = "Nagios";
$lang['menu_ldap'] = "LDAP";
$lang['menu_snmp'] = "SNMP";
$lang['menu_rrdtool'] = "RRDTool";
$lang['menu_debug'] = "Debug";
$lang['menu_colors'] = "Colors";
$lang['menu_general'] = "G&ecute;n&ecute;rate";
$lang['m_modules'] = "Modules";

# Session

$lang['kick_user'] = "Kick User";
$lang['distant_location'] = "IP Address";
$lang['wi_user'] = "Users";
$lang['wi_where'] = "Localisation";
$lang['wi_last_req'] = "Last request";
$lang['kicked_user'] = "User Kicked";

# Lang

$lang['lang_title'] = "Lang Files management";
$lang['lang_user'] = "Default User Lang :";
$lang['lang_gen'] = "Main Lang Files Availables :";
$lang['lang_genUse'] = "Main Lang File Used :";

$lang['lang_mod'] = "Module";
$lang['lang_av'] = "Lang Availables";
$lang['lang_use'] = "Lang Used";
$lang['lang_none'] = "None";

# My Account

$lang['myAcc_change'] = "Change my settings";

# Tasks

$lang['m_task'] = "Tasks";

# ODS 

$lang['m_log_advanced'] = "Avanced Logs";
$lang['m_log_lite'] = "Event Logs";
$lang['ods_rrd_path'] = "Path to RRDTool Database";
$lang['ods_len_storage_rrd'] = "RRDTool database size";
$lang['ods_autodelete_rrd_db'] = "RRDTool auto delete";
$lang['ods_sleep_time'] = "Sleep Time";
$lang['ods_purge_interval'] = "Purge check interval";
$lang['ods_storage_type'] = "Storage Type";
$lang['ods_sleep_time_expl'] = "in seconds - Must be higher than 10";
$lang['ods_purge_interval_expl'] = "in seconds - Must be higher than 2";
$lang['ods_auto_drop'] = "Drop Data in another file";
$lang['ods_drop_file'] = "Drop file";
$lang['ods_perfdata_file'] = "Perfdata";
$lang['ods_archive_log'] = "Archive Nagios Logs";
$lang['ods_log_retention'] = "Logs retetion duration";
$lang['ods_log_retention_unit'] = "days";
$lang['ods_fast_parsing'] = "Fast status log parsing";
$lang['ods_nagios_log_file'] = "Nagios current log file to parse.";

$lang['m_patch'] = "Update";
$lang['m_checkVersion'] = "Check";
$lang['m_patchOptions'] = "Options";
$lang['patchOption_change'] = "Change update options";
$lang['patchOption_check_stable'] = "Checks stable versions";
$lang['patchOption_check_security'] = "Check secu-patchs";
$lang['patchOption_check_patch'] = "Check patchs";
$lang['patchOption_check_rc'] = "Check Release candidate";
$lang['patchOption_check_beta'] = "Check Betas";
$lang['patchOption_path_download'] = "Patchs Download path";
$lang['checkVersion_msgErr01'] = "Can't get last version.";
$lang['updateSecu'] = "Security patch available";
$lang['update'] = "Update patch available";
$lang['uptodate'] = "Centreon is updated.";
$lang['preUpdate_msgErr01'] = "Can't get list files.";
$lang['preUpdate_msgErr02'] = "Can't get file.";
$lang['preUpdate_msgErr03'] = "No version defined.";
$lang['preUpdate_msgErr04'] = "Can't open configuration file : /etc/oreon.conf";
$lang['preUpdate_msgErr05'] = "Probl&egrave;me la derni&egrave; version disponible.";
$lang['preUpdate_msgErr06'] = "Can't open patch";
$lang['preUpdate_fileDownloaded'] = "%s is downloaded.<br/>";
$lang['preUpdate_installArchive'] = "For completing your upgrade (%s), unzip downloaded file, and follow README instructions\n";
$lang['preUpdate_shellPatch'] = "launch %s with root permissions.\n";
$lang['batchPatch_begin'] = "Execution start";
$lang['batchPatch_end'] = "Execution end";
$lang['batchPatch_ok01'] = "%s patch is installed.";
$lang['batchPatch_err01'] = "Error when installing patch : %s.";

?>