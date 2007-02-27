<?
/**
Oreon is developped with GPL Licence 2.0 :
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
$lang['genOpt_oreon'] = "Oreon informations";
$lang['genOpt_snmp'] = "SNMP informations";
$lang['genOpt_various'] = "Various Informations";
$lang['genOpt_nagPath'] = "Directory";
$lang['genOpt_nagBin'] = "Directory + Binary";
$lang['genOpt_nagScript'] = "Init Script";
$lang['genOpt_nagImg'] = "Images Directory";
$lang['genOpt_nagPlug'] = "Plugins Directory";
$lang['genOpt_nagVersion'] = "Nagios Release";
$lang['genOpt_oPath'] = "Directory";
$lang['genOpt_webPath'] = "Oreon Web Directory";
$lang['genOpt_oRrdbPath'] = "rrd Directory";
$lang['genOpt_oRefresh'] = "Refresh Interval (in seconds)";
$lang['genOpt_oExpire'] = "Sessions Expiration Time (in minutes)";
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
$lang['genOpt_mailer'] = "Directory + Mailer Binary";
$lang['genOpt_rrdtool'] = "Directory + RRDTOOL Binary";
$lang['genOpt_rrdtoolV'] = "RRDTOOL Version";
$lang['genOpt_perfparse'] = "Using PerfParse";
$lang['genOpt_colorPicker'] = "Pick a color";
$lang['genOpt_maxViewMonitoring'] = "Limit per page for Monitoring";
$lang['genOpt_maxViewConfiguration'] = "Limit per page (default)";
$lang['genOpt_AjaxTimeReloadStatistic'] = "Refresh Interval for statistics (in seconds)";
$lang['genOpt_AjaxTimeReloadMonitoring'] = "Refresh Interval for monitoring (in seconds)";
$lang['genOpt_AjaxFirstTimeReloadStatistic'] = "First Refresh delay for statistics (in seconds)";
$lang['genOpt_AjaxFirstTimeReloadMonitoring'] = "First Refresh delay for monitoring (in seconds)";
$lang['genOpt_snmp_trapd_pathConf'] = "Directory + Conf File SNMPTrapd";
$lang['genOpt_snmp_trapd_pathBin'] = "Directory + Daemon SNMPTrapd (/etc/init.d/...)";
$lang['genOpt_snmp_trapd_used'] = "Easy Traps manager used";
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


# Menu

$lang['menu_infos'] = "Menu Information";
$lang['menu_mod'] = "Modules Availables";
$lang['menu_modRules'] = "A module can contain information which will be quickly integrate";
$lang['menu_modRule1'] = "Files to generate -> /MODULES/generate_files/";
$lang['menu_modRule2'] = "Sql Files -> /MODULES/sql/";
$lang['menu_modRule3'] = "Lang Files -> /MODULES/lang/";
$lang['menu_modInfos'] = "Module Informations";
$lang['menu_modGen'] = "Generation";
$lang['menu_modSql'] = "Sql";
$lang['menu_modLang'] = "Lang Files Available";
$lang['menu_listName'] = "Name";
$lang['menu_listLongName'] = "Real Name";
$lang['menu_listDir'] = "Directory";
$lang['menu_listGen'] = "Generation";
$lang['menu_listLang'] = "Lang";
$lang['menu_listSql'] = "Sql";
$lang["menu_listAction"] = "Actions";
$lang["menu_listAction_del"] = "Uninstall";
$lang["menu_listAction_install"] = "Install";
$lang["menu_listAction_i"] = "Module has been installed succesfully";
$lang["menu_listAction_d"] = "Module has been uninstalled succesfully";
$lang["menu_Module_Title"] = "Module Informations";
$lang["menu_Module_Name"] = "Name";
$lang["menu_Module_Version"] = "Version";
$lang["menu_Module_Author"] = "Author";
$lang["menu_Module_additionnals_infos"] = "Additionnals Informations";
$lang['menu_ODS'] = "OreonDataStorage";
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

$lang['ods_rrd_path'] = "Path to RRDTool Database";
$lang['ods_len_storage_rrd'] = "RRDTool database size";
$lang['ods_autodelete_rrd_db'] = "RRDTool auto delete";
$lang['ods_sleep_time'] = "Sleep Time";
$lang['ods_purge_interval'] = "Purge check interval";
$lang['ods_storage_type'] = "Storage Type";

?>