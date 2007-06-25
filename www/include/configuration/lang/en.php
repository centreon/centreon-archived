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

/* Configuration Error */

$lang['requiredFields'] = "<font style='color: red;'>*</font> Champs requis";
$lang['ErrName'] = "Compulsory Name";
$lang['ErrAlias'] = "Compulsory Alias";
$lang['ErrEmail'] = "Valid Email";
$lang['ErrOpt'] = "Compulsory Option";
$lang['ErrTp'] = "Compulsory Period";
$lang['ErrCmd'] = "Compulsory Command";
$lang['ErrCct'] = "Compulsory Contact";
$lang['ErrCg'] = "Compulsory Contact Group";
$lang['ErrCmdLine'] = "Compulsory Command Line";
$lang['ErrCmdType'] = "Compulsory  Command Type";
$lang['ErrAlreadyExist'] = "A same Name element already exist";
$lang['ErrAddress'] = "Compulsory Adress";
$lang['ErrRequired'] = "Require Field";
$lang['ErrSvLeast'] = "HostGroup or Host Require";
$lang['ErrCctPasswd'] = "Passwords are not the same";
$lang['ErrGenFileProb'] = "Can't access to file needed";
$lang['ErrCycleDef'] = "Circular Definition";
$lang['ErrSvConflict'] = "This description is in conflicts with another one already define in the relation(s) selected";
$lang['ErrNotEnoughtContact'] = "You have to keep at least one contact to access to Oreon";

/* Configuration Menu */

$lang['quicksearch'] = "Quick Search";
$lang['available'] = "Available";
$lang['selected'] = "Selected";
$lang['comment'] = "Comments";
$lang['nothing'] = "Default";
$lang['formObjMatch'] = "Impacted Elements : ";
$lang['action'] = "Post Validation";
$lang['actionList'] = "List";
$lang['actionForm'] = "Form";
$lang['legend1'] = "(*) This Service is used by many Host";
$lang['previous'] = "previous";
$lang['next'] = "next";
$lang['further_infos'] = "Additional Information";


/* host */

$lang['h'] = "Host";
$lang['h_conf'] = "Host Configuration";
$lang['h_add'] = "Add a Host";
$lang['h_change'] = "Modify a Host";
$lang['h_view'] = "View a Host";

$lang['h_extInf'] = "Host Extended Infos";
$lang['h_ExtInf_add'] = "Add an Host Extended Info";
$lang['h_ExtInf_change'] = "Modify an Host Extended Info";
$lang['h_ExtInf_view'] = "View an Host Extended Info";

$lang['h_childs'] = "Linked Services";
$lang['h_parent'] = "Template parent";

$lang['h_infos'] = "General Informations";
$lang['h_name'] = "Host Name";
$lang['h_alias'] = "Alias";
$lang['h_address'] = "Address";
$lang['h_snmpCom'] = "SNMP Community";
$lang['h_snmpVer'] = "Version";
$lang['h_template'] = "Host Template";
$lang['h_templateText'] = "Use a Template exempt you to fill require fields";
$lang['h_dupSvTplAssocText'] = "Create Services linked to the Template too";

$lang['h_head_links'] = "Relations";
$lang['h_Links_add'] = "Add relations";
$lang['h_Links_change'] = "Modify relations";
$lang['h_Links_view'] = "View relations";
$lang['h_HostGroupMembers'] = "HostGroups Parents";
$lang['h_HostParents'] = "Hosts Parents";
$lang['h_HostChilds'] = "Hosts Childs";

$lang['h_head_state'] = "Host Check Properties";
$lang['h_checkCmd'] = "Check Command";
$lang['h_checkMca'] = "Max Check Attempts";
$lang['h_checkInterval'] = "Normal Check Interval";
$lang['h_checksEnabled'] = "Checks Enabled";
$lang['h_checkPeriod'] = "Check Period";
$lang['h_activeCE'] = "Active Checks Enabled";
$lang['h_passiveCE'] = "Passive Checks Enabled";
$lang['h_eventHandlerE'] = "Event Handler Enabled";
$lang['h_eventHandler'] = "Event Handler";

$lang['h_head_treat'] = "Data Treatment";
$lang['h_add_treat'] = "Add Data Treatment";
$lang['h_modify_treat'] = "Modify Data Treatment";
$lang['h_view_treat'] = "View Data Treatment";

$lang['h_ObsessOH'] = "Obsess Over Host";
$lang['h_checkFreshness'] = "Check Freshness";
$lang['h_FreshnessThreshold'] = "Freshness Threshold";
$lang['h_flapDetect'] = "Flap Detection Enabled";
$lang['h_lowFT'] = "Low Flap threshold";
$lang['h_highFT'] = "High Flap Threshold";
$lang['h_processPD'] = "Process Perf Data";
$lang['h_retainSI'] = "Retain Satatus Information";
$lang['h_retainNI'] = "Retain Non Status Information";

$lang['h_head_notif'] = "Notification";
$lang['h_CgMembers'] = "ContactGroups Linked";
$lang['h_notifInt'] = "Notification Interval";
$lang['h_notifTp'] = "Notification Period";
$lang['h_notifOpts'] = "Notification Options";
$lang['h_notifEnabled'] = "Notification Enabled";
$lang['h_stalOpts'] = "Stalking Options";

$lang['h_nagios'] = "Nagios";
$lang['h_notes'] = "Note";
$lang['h_notesUrl'] = "URL";
$lang['h_actionUrl'] = "Action URL";
$lang['h_iconImg'] = "Icon";
$lang['h_iconImgAlt'] = "Alt icon";
$lang['h_vrmlImg'] = "VRML Image";
$lang['h_nagStatImg'] = "Nagios Statuts Map Image";
$lang['h_nag2dCoords'] = "Nagios 2d Coords";
$lang['h_nag3dCoords'] = "Nagios 3d Coords";

$lang['h_oreon'] = "Oreon";
$lang['h_country'] = "Country";
$lang['h_city'] = "City";
$lang['h_popCityTitle'] = "Choose a country";
$lang['h_popCityAlpha'] = "Alphabetic Sort";

/* host group */

$lang['hg'] = "HostGroup";
$lang['hg_name'] = "HostGroup Name";
$lang['hg_alias'] = "Alias";
$lang['hg_add'] = "Add a HostGroup";
$lang['hg_change'] = "Modify a HostGroup";
$lang['hg_view'] = "View a HostGroup";
$lang['hg_CgMembers'] = "ContactGroups linked";
$lang['hg_HostMembers'] = "Hosts linked";
$lang['hg_infos'] = "General Informations";
$lang['hg_links'] = "Relations";
$lang['hg_notif'] = "Notification";
$lang['hg_HostAct'] = "Hosts activate";
$lang['hg_HostDeact'] = "Hosts deactivate";

/* Escalation  */

$lang['esc'] = "Escalation";
$lang['esc_name'] = "Escalation Name";
$lang['esc_add'] = "Add an Escalation";
$lang['esc_change'] = "Modify an Escalation";
$lang['esc_view'] = "View an Escalation";
$lang['esc_infos'] = "Informations";
$lang['esc_sort2'] = "Hosts Escalation";
$lang['esc_sort3'] = "Services Escalation";
$lang['esc_sort4'] = "Hostgroups Escalation";
$lang['esc_sort5'] = "Meta Services Escalation";
$lang['esc_firstNotif'] = "First Notification";
$lang['esc_lastNotif'] = "Last Notification";
$lang['esc_notifInt'] = "Notification Interval";
$lang['esc_escPeriod'] = "Escalation Period";
$lang['esc_hOpt'] = "Hosts Escalation Options";
$lang['esc_sOpt'] = "Services Escalation Options";
$lang['esc_comment'] = "Comment";
$lang['esc_appCG'] = "implied Contact Groups";
$lang['esc_sortHosts'] = "Implied Hosts";
$lang['esc_sortSv'] = "Implied Services";
$lang['esc_sortHg'] = "Implied HostGroups";
$lang['esc_sortMs'] = "Implied Meta Services";
$lang['esc_hostServiceMembers'] = "Services by Hosts";

/* Dependencies */

$lang['dep'] = "Dependencies";
$lang['dep_add'] = "Add a Dependency";
$lang['dep_change'] = "Modify a Dependency";
$lang['dep_view'] = "View a Dependency";
$lang['dep_infos'] = "Informations";
$lang['dep_inheritsP'] = "Parent relationship";
$lang['dep_exeFC'] = "Execution Failure Criteria";
$lang['dep_notifFC'] = "Notification Failure Criteria";
$lang['dep_comment'] = "Comment";
$lang['dep_hPar'] = "Hosts Name";
$lang['dep_hChi'] = "Dependent Hosts Name";
$lang['dep_hgPar'] = "HostGroups Name";
$lang['dep_hgChi'] = "Dependent HostGroups Name";
$lang['dep_hSvPar'] = "Hosts Services Description";
$lang['dep_hSvChi'] = "Dependent Hosts Services Description";
$lang['dep_sgPar'] = "ServiceGroups Name";
$lang['dep_sgChi'] = "Dependent ServiceGroups Name";
$lang['dep_msPar'] = "Meta Services Name";
$lang['dep_msChi'] = "Dependent Meta Services Name";
$lang['dep_name'] = "Name";
$lang['dep_description'] = "Description";
$lang['dep_sort2'] = "Host Service Description";

/* host template model */

$lang['htm'] = "Host Template Model";
$lang['htm_childs'] = "Services Template linked";
$lang['htm_parent'] = "Template parent";
$lang['htm_add'] = "Add a Host Template Model";
$lang['htm_change'] = "Modify a Host Template Model";
$lang['htm_view'] = "View a Host Template Model";
$lang['htm_template'] = "Host Model Template";
$lang['htm_templateText'] = "Use a Template Model allow you to have multi level Template relationship";

/* service */

$lang['sv'] = "Service";
$lang['sv_conf'] = "Service Configuration";
$lang['sv_add'] = "Add a Service";
$lang['sv_change'] = "Modify a Service";
$lang['sv_view'] = "View a Service";
$lang['sv_parent'] = "Template parent";

$lang['sv_extInf'] = "Service Extended Infos";
$lang['sv_ExtInf_add'] = "Add an Extended Info";
$lang['sv_ExtInf_change'] = "Modify an Extended Info";
$lang['sv_ExtInf_view'] = "View an Extended Info";

$lang['sv_infos'] = "General Informations";
$lang['sv_hPars'] = "Linked with Hosts";
$lang['sv_hgPars'] = "Linked with HostGroups";
$lang['sv_description'] = "Description";
$lang['sv_alias'] = "Alias";
$lang['sv_alias_interest'] = "Used for Service duplication";
$lang['sv_template'] = "Service Template";
$lang['sv_templateText'] = "Use a Template exempt you to fill require fields";
$lang['sv_traps'] = "Service Trap Relation";

$lang['sv_head_links'] = "Relations";
$lang['sv_Links_add'] = "Add relations";
$lang['sv_Links_change'] = "Modify relations";
$lang['sv_Links_view'] = "View relations";
$lang['sv_ServiceGroupMembers'] = "ServiceGroups parents";

$lang['sv_head_state'] = "Service State";
$lang['sv_isVolatile'] = "Is Volatile";
$lang['sv_checkCmd'] = "Check Command";
$lang['sv_checkMca'] = "Max Check Attempts";
$lang['sv_normalCheckInterval'] = "Normal Check Interval";
$lang['sv_retryCheckInterval'] = "Retry Check Interval";
$lang['sv_checkPeriod'] = "Check Period";
$lang['sv_activeCE'] = "Active Checks Enabled";
$lang['sv_passiveCE'] = "Passive Checks Enabled";
$lang['sv_eventHandlerE'] = "Event Handler Enabled";
$lang['sv_eventHandler'] = "Event Handler";
$lang['sv_args'] = "Args";

$lang['sv_head_treat'] = "Data Treatment";
$lang['sv_paraCheck'] = "Parallelize Check";
$lang['sv_ObsessOS'] = "Obsess Over Service";
$lang['sv_checkFreshness'] = "Check Freshness";
$lang['sv_FreshnessThreshold'] = "Freshness Threshold";
$lang['sv_flapDetect'] = "Flap Detection Enabled";
$lang['sv_lowFT'] = "Low Flap Threshold";
$lang['sv_highFT'] = "High Flap Threshold";
$lang['sv_processPD'] = "Process Perf Data";
$lang['sv_retainSI'] = "Retain Status Information";
$lang['sv_retainNI'] = "Retain Non Status Information";

$lang['sv_head_notif'] = "Notification";
$lang['sv_CgMembers'] = "ContactGroups implied";
$lang['sv_notifInt'] = "Notification Interval";
$lang['sv_notifTp'] = "Notification Period";
$lang['sv_notifOpts'] = "Notification Type";
$lang['sv_notifEnabled'] = "Notification Enabled";
$lang['sv_stalOpts'] = "Stalking Options";

$lang['sv_oreon'] = "Oreon";
$lang['sv_graphTpl'] = "Graph Template";

/* Meta Service */

$lang['ms'] = "Meta Service";
$lang['ms_conf'] = "Configuration";
$lang['ms_infos'] = "General Informations";
$lang['ms_add'] = "Add a Meta Service";
$lang['ms_change'] = "Modify a Meta Service";
$lang['ms_view'] = "View a Meta Service";
$lang['ms_name'] = "Meta Service Name";
$lang['ms_display'] = "Display format";
$lang['ms_comment'] = "Comment";
$lang['ms_levelw'] = "Warning Level";
$lang['ms_levelc'] = "Critical Level";
$lang['ms_calType'] = "Calcul Type";
$lang['ms_selSum'] = "Sum";
$lang['ms_selAvr'] = "Average";
$lang['ms_selMin'] = "Min";
$lang['ms_selMax'] = "Max";
$lang['ms_selMod'] = "Selection Mode";
$lang['ms_selList'] = "Services List";
$lang['ms_regexp'] = "Regular Expression";
$lang['ms_exp'] = "Expression";
$lang['ms_sqlMatch'] = "SQL matching";
$lang['ms_metric'] = "Metric";

$lang['ms_head_state'] = "Meta Service State";
$lang['ms_checkMca'] = "Max Check Attempts";
$lang['ms_normalCheckInterval'] = "Normal Check Interval";
$lang['ms_retryCheckInterval'] = "Retry Check Interval";
$lang['ms_checkPeriod'] = "Check Period";

$lang['ms_head_notif'] = "Notification";
$lang['ms_CgMembers'] = "ContactGroups linked";
$lang['ms_notifInt'] = "Notification Interval";
$lang['ms_notifTp'] = "Notification Period";
$lang['ms_notifOpts'] = "Notification Type";
$lang['ms_notifEnabled'] = "Notification Enabled";

$lang['mss_add'] = "Add a Service";
$lang['mss_change'] = "Modify a Service";
$lang['mss_view'] = "View a Service";

/* extended service infos */

$lang['esi'] = "Extended Service Information";
$lang['esi_available'] = "Extended Service Information Available ";
$lang['esi_notes'] = "Notes";
$lang['esi_notes_url'] = "Notes url";
$lang['esi_action_url'] = "Action url";
$lang['esi_icon_image'] = "Icon";
$lang['esi_icon_image_alt'] = "Icon alt";

/* service template model*/

$lang['stm'] = "Service Template Model";
$lang['stm_parent'] = "Template parent";
$lang['stm_add'] = "Add a Service Template Model";
$lang['stm_change'] = "Modify a Service Template Model";
$lang['stm_view'] = "View a Service Template Model";
$lang['stm_template'] = "Template Service Model";
$lang['stm_templateText'] = "Use a Template Model allow you to have multi level Template relationship";

/* service group*/

$lang['sg'] = "ServiceGroup";
$lang['sg_name'] = "ServiceGroup Name";
$lang['sg_alias'] = "Alias";
$lang['sg_add'] = "Add a ServiceGroup";
$lang['sg_change'] = "Modify a ServiceGroup";
$lang['sg_view'] = "View a ServiceGroup";
$lang['sg_hostServiceMembers'] = "Host Services linked";
$lang['sg_hostGroupServiceMembers'] = "Host Group Services linked";
$lang['sg_infos'] = "General Informations";
$lang['sg_links'] = "Relations";
$lang['sg_notif'] = "Notification";

/* contact */

$lang['cct_add'] = "Add an User";
$lang['cct_change'] = "Modify an User";
$lang['cct_view'] = "View an User";
$lang['cct_infos'] = "General Informations";
$lang['cct_notif'] = "Notifications Type";
$lang['cct_name'] = "Full Name";
$lang['cct_mail'] = "Email";
$lang['cct_mailType'] = "Mail Type";
$lang['cct_pager'] = "Pager";
$lang['cct_hostNotifOpt'] = "Hosts Notification Options";
$lang['cct_hostNotifTp'] = "Host Notification Period";
$lang['cct_hostNotifCmd'] = "Host Notification Commands";
$lang['cct_svNotifOpt'] = "Services Notification Options";
$lang['cct_svNotifTp'] = "Services Notification Period";
$lang['cct_svNotifCmd'] = "Services Notification Commands";
$lang['cct_cgNotif'] = "Contact Groups parents";
$lang['cct_passwd'] = "Password";
$lang['cct_passwd2'] = "Confirmation";
$lang['cct_lang'] = "Default Lang";
$lang['cct_oreon'] = "Oreon";
$lang['cct_oreon_text'] = "Reach Oreon Frontend";
$lang['cct_admin'] = "Admin";
$lang['cct_contact_auth_type'] = "Authentification Type";
$lang['cct_ldap_dn'] = "Ldap DN (Distinguished Name)";
$lang['cct_ldap_import'] ="LDAP Import";
$lang['cct_ldap_search_param'] ="Search Options";
$lang['cct_ldap_search_options'] ="Search Options";
$lang['cct_ldap_search_result'] ="Search Result";
$lang['cct_ldap_search_filter'] ="Search Filter";
$lang['cct_ldap_search_result_output'] ="Result";
$lang['cct_ldap_search'] = "Search";
$lang['cct_ldap_import_users'] = "Import";
$lang['cct_ldap_search_filter_help_title'] ="Filter Examples";
$lang['cct_ldap_search_filter_help'] = "Active Directory : (&(objectClass=user)(samaccounttype=805306368)(objectCategory=person)(cn=*))<br>Lotus Domino : (&(objectClass=person)(cn=*))<br>OpenLDAP : (&(objectClass=person)(cn=*))";

/* contact group */

$lang['cg_infos'] = "General Informations";
$lang['cg_name'] = "Contact Group Name";
$lang['cg_alias'] = "Alias";
$lang['cg_members'] = "Contacts linked";
$lang['cg_notif'] = "Notification";
$lang['cg_add'] = "Add a Contact Group";
$lang['cg_change'] = "Modify a Contact Group";
$lang['cg_view'] = "View a Contact Group";
$lang['cg_cctNbr'] = "Contacts";

/* time period */

$lang['tp_name'] = "Time Period Name";
$lang['tp_alias'] = "Alias";
$lang['tp_sunday'] = "Sunday";
$lang['tp_monday'] = "Monday";
$lang['tp_tuesday'] = "Tuesday";
$lang['tp_wednesday'] = "Wednesday";
$lang['tp_thursday'] = "Thursday";
$lang['tp_friday'] = "Friday";
$lang['tp_saturday'] = "Saturday";
$lang['tp_infos'] = "General Informations";
$lang['tp_notif'] = "Notification Slice Time";
$lang['tp_add'] = "Add a Time Period";
$lang['tp_change'] = "Modify a Time Period";
$lang['tp_view'] = "View a Time Period";

/* command */

$lang['cmd_type'] = "Command Type";
$lang['cmd_infos'] = "Informations";
$lang['cmd_check'] = "Check Command";
$lang['cmd_notif'] = "Notification Command";
$lang['cmd_various'] = "Various Command";
$lang['cmd_checkShort'] = "Check";
$lang['cmd_notifShort'] = "Notification";
$lang['cmd_add'] = "Add a Command";
$lang['cmd_change'] = "Modify a Command";
$lang['cmd_view'] = "View a Command";
$lang['cmd_name'] = "Command Name";
$lang['cmd_line'] = "Command Line";
$lang['cmd_comment'] = "Commands definitions can contain Macros but you have to be sure that they are well validate for the case they'll be used";
$lang['cmd_help'] = "Plugin Help";
$lang['cmd_help_output'] = "Help";
$lang['cmd_output'] = "Output";
$lang['cmd_example'] = "Argument Example";
$lang['cmd_plugins'] = "Plugins";

/* Plugins */

$lang['plg_path'] = "Chemin d'acc&egrave;";
$lang['plg_size'] = "Size";

/* Nagios CFG */

$lang['nagios_add'] = "Add a Nagios Configuration File";
$lang['nagios_change'] = "Modify a Nagios Configuration File";
$lang['nagios_view'] = "View a Nagios Configuration File";
$lang['nagios_infos'] = "Informations";
$lang['nagios_name'] = "Configuration Name";
$lang['nagios_comment'] = "Comments";

$lang['nag_logFile'] = "Log file";
$lang['nag_objConfFile'] = "Object Configuration File";
$lang['nag_objConfDir'] = "Object Configuration Directory";
$lang['nag_objCacheFile'] = "Object Cache File";
$lang['nag_resFile'] = "Resource File";
$lang['nag_tmpFile'] = "Temp File";
$lang['nag_p1File'] = "P1 File";

$lang['nag_statusFile'] = "Status File";
$lang['nag_asuOpt'] = "Aggregated Status Updates Option";
$lang['nag_asuInt'] = "Aggregated Status Data Update Interval";

$lang['nag_nagUser'] = "Nagios User";
$lang['nag_nagGroup'] = "Nagios Group";

$lang['nag_notifOpt'] = "Notification Option";
$lang['nag_svCheckExeOpt'] = "Service Check Execution Option";
$lang['nag_pasSvCheckAccOpt'] = "Passive Service Check Acceptance Option";
$lang['nag_hostCheckExeOpt'] = "Host Check Execution Option";
$lang['nag_pasHostCheckAccOpt'] = "Passive Host Check Acceptance Option";
$lang['nag_eventHandOpt'] = "Event Handler Option";

$lang['nag_logRotMethod'] = "Log Rotation Method";
$lang['nag_logArchPath'] = "Log Archive Path";

$lang['nag_extCmdCheckOpt'] = "External Command Check Option";
$lang['nag_extCmdCheckInt'] = "External Command Check Interval";
$lang['nag_extCmdFile'] = "External Command File";

$lang['nag_cmtFile'] = "Comment File";
$lang['nag_dtFile'] = "Downtime File";
$lang['nag_lockFile'] = "Lock File";

$lang['nag_stateRetOpt'] = "State Retention Option";
$lang['nag_stateRetFile'] = "State Retention File";
$lang['nag_autStateRetUpdInt'] = "Automatic State Retention Update Interval";
$lang['nag_useRetPgmStateOpt'] = "Use Retained Program State Option";
$lang['nag_useRetSchInfoOpt'] = "Use Retained Scheduling Info Option";

$lang['nag_SysLogOpt'] = "Syslog Logging Option";
$lang['nag_notLogOpt'] = "Notification Logging Option";
$lang['nag_svCheckRtrLogOpt'] = "Service Check Retry Logging Option";
$lang['nag_hostRtrLogOpt'] = "Host Retry Logging Option";
$lang['nag_eventHandLogOpt'] = "Event Handler Logging Option";
$lang['nag_iniStateLogOpt'] = "Initial State Logging Option";
$lang['nag_extCmdLogOpt'] = "External Command Logging Option";
$lang['nag_passSvCheckLogOpt'] = "Passive Service Check Logging Option";
$lang['nag_passCheckLogOpt'] = "Passive Check Logging Option";

$lang['nag_glHostEventHand'] = "Global Host Event Handler";
$lang['nag_glSvEventHand'] = "Global Service Event Handler";

$lang['nag_intCheckSleepTm'] = "Inter-Check Sleep Time";
$lang['nag_intCheckDelMth'] = "Inter-Check Delay Method";
$lang['nag_svIntCheckDelMth'] = "Service Inter-Check Delay Method";
$lang['nag_maxSvCheckSpread'] = "Maximum Service Check Spread";
$lang['nag_svInterFac'] = "Service Interleave Factor";
$lang['nag_maxConcSvChecks'] = "Maximum Concurrent Service Checks";
$lang['nag_svReapFreq'] = "Service Repear Frequency";
$lang['nag_hostIntCheckDelMth'] = "Host Inter-Check Delay Method";
$lang['nag_maxHostCheckSpread'] = "Maximum Host Check Spread";
$lang['nag_tmIntLen'] = "Timing Interval Length";
$lang['nag_autoRescheOpt'] = "Auto-Rescheduling Option";
$lang['nag_autoRescheInt'] = "Auto-Rescheduling Interval";
$lang['nag_autoRescheWnd'] = "Auto-Rescheduling Window";

$lang['nag_aggHostCheckOpt'] = "Aggressive Host Checking Option";

$lang['nag_flapDetOpt'] = "Flap Detection Option";
$lang['nag_lowSvFlapThres'] = "Low Service Flap Threshold";
$lang['nag_highSvFlapThres'] = "High Service Flap Threshold";
$lang['nag_lowHostFlapThres'] = "Low Host Flap Threshold";
$lang['nag_highHostFlapThres'] = "High Host Flap Threshold";

$lang['nag_softSvDepOpt'] = "Soft Service Dependencies Option";

$lang['nag_svCheckTmOut'] = "Service Check Timeout";
$lang['nag_hostCheckTmOut'] = "Host Check Timeout";
$lang['nag_eventHandTmOut'] = "Event Handler Timeout";
$lang['nag_notifTmOut'] = "Notification Timeout";
$lang['nag_obComSvProcTmOut'] = "Obsessive Compulsive Service Processor Timeout";
$lang['nag_obComHostProcTmOut'] = "Obsessive Compulsive Host Processor Timeout";
$lang['nag_perfDataProcCmdTmOut'] = "Performance Data Processor Command Timeout";

$lang['nag_obsOverSvOpt'] = "Obsess Over Services Option";
$lang['nag_obsComSvProcCmd'] = "Obsessive Compulsive Service Processor Command";
$lang['nag_obsOverHostOpt'] = "Obsess Over Hosts Option";
$lang['nag_obsComHostProcCmd'] = "Obsessive Compulsive Host Processor Command";

$lang['nag_perfDataProcOpt'] = "Performance Data Processing Option";
$lang['nag_hostPerfDataProcCmd'] = "Host Performance Data Processing Command";
$lang['nag_svPerfDataProcCmd'] = "Service Performance Data Processing Command";
$lang['nag_hostPerfDataFile'] = "Host Performance Data File";
$lang['nag_svPerfDataFile'] = "Service Performance Data File";
$lang['nag_hostPerfDataFileTmp'] = "Host Performance Data File Template";
$lang['nag_svPerfDataFileTmp'] = "Service Performance Data File Template";
$lang['nag_hostPerfDataFileMode'] = "Host Performance Data File Mode";
$lang['nag_svPerfDataFileMode'] = "Service Performance Data File Mode";
$lang['nag_hostPerfDataFileProcInt'] = "Host Performance Data File Processing Interval";
$lang['nag_svPerfDataFileProcInt'] = "Service Performance Data File Processing Interval";
$lang['nag_hostPerfDataFileProcCmd'] = "Host Performance Data File Processing Command";
$lang['nag_svPerfDataFileProcCmd'] = "Service Performance Data File Processing Command";

$lang['nag_OrpSvCheckOpt'] = "Orphaned Service Check Option";

$lang['nag_svFreshCheckOpt'] = "Service Freshness Checking Option";
$lang['nag_svFreshCheckInt'] = "Service Freshness Check Interval";
$lang['nag_freshCheckInt'] = "Freshness Check Interval";
$lang['nag_hostFreshCheckOpt'] = "Host Freshness Checking Option";
$lang['nag_hostFreshCheckInt'] = "Host Freshness Check Interval";

$lang['nag_dateFormat'] = "Date Format";

$lang['nag_illObjNameChar'] = "Illegal Object Name Characters";
$lang['nag_illMacOutChar'] = "Illegal Macro Output Characters";

$lang['nag_regExpMatchOpt'] = "Regular Expression Matching Option";
$lang['nag_trueRegExpMatchOpt'] = "True Regular Expression Matching Option";

$lang['nag_adminEmail'] = "Administrator Email Address";
$lang['nag_adminPager'] = "Administrator Pager";

$lang['nag_broker_module'] = "Broker Module";

/* Resource CFG */

$lang['rs_add'] = "Add a Resource";
$lang['rs_change'] = "Modify a Resource";
$lang['rs_view'] = "View Resource";
$lang['rs_infos'] = "General Informations";
$lang['rs_name'] = "Resource Name";
$lang['rs_line'] = "MACRO Expression";

/* Perfparse CFG */

$lang['pp_add'] = "Add a Perfparse Configuration File";
$lang['pp_change'] = "Modify a Perfparse Configuration File";
$lang['pp_view'] = "View a Perfparse Configuration File";
$lang['pp_infos'] = "General Informations";
$lang['pp_name'] = "Perfparse File Name";
$lang['pp_comment'] = "Comments";
$lang['pp_sMan'] = "Server Management";
$lang['pp_serPort'] = "Server Port";
$lang['pp_pMan'] = "Parser Management";
$lang['pp_perfDLF'] = "Performance Data Log Files ('-' for stdin)";
$lang['pp_serLog'] = "Service Log";
$lang['pp_svLPMP'] = "Service Log Position Mark Path";
$lang['pp_errHandling'] = "Error handling";
$lang['pp_errLog'] = "Error Log File";
$lang['pp_errLogRot'] = "Error Log Rotate";
$lang['pp_errLKND'] = "Error Log Keep N Days";
$lang['pp_dropFile'] = "Drop File";
$lang['pp_dropFileRot'] = "Drop File Rotate";
$lang['pp_dropFKND'] = "Drop File Keep N Days";
$lang['pp_lockFileTxt'] = "Lock file for only one perfparse running at the same time";
$lang['pp_lockFile'] = "Lock File";
$lang['pp_reportOpt'] = "Reporting Options";
$lang['pp_showSB'] = "Show Status Bar";
$lang['pp_doReport'] = "Do_Report";
$lang['pp_cgiMan'] = "CGI Management";
$lang['pp_defUPP'] = "Default user permissions Policy";
$lang['pp_defUPHG'] = "Default user permissions Hostgroups";
$lang['pp_defUPS'] = "Default user permissions Summary";
$lang['pp_outLog'] = "Output Logger";
$lang['pp_outLogFile'] = "Output Log File";
$lang['pp_outLogFileName'] = "Output Log Filename";
$lang['pp_outLogRot'] = "Output Log Rotate";
$lang['pp_outLKND'] = "Output Log Keep N Days";
$lang['pp_SockOutMan'] = "Socket_output managment";
$lang['pp_useStoSockOut'] = "Use Storage Socket Output";
$lang['pp_stoSockOutHName'] = "Storage Socket Output Host Name";
$lang['pp_stoSockOutPort'] = "Storage Socket Output Port";
$lang['pp_dbMan'] = "Database managment";
$lang['pp_useStorMySQL'] = "Use Storage Mysql";
$lang['pp_noRawData'] = "No Raw Data";
$lang['pp_noBinData'] = "No Bin Data";
$lang['pp_dbUser'] = "DB User";
$lang['pp_dbName'] = "DB Name";
$lang['pp_dbPass'] = "DP Pass";
$lang['pp_dbHost'] = "DB_Host";
$lang['pp_dumHN'] = "Dummy Hostname";
$lang['pp_stoModLoad'] = "Storage Modules Load";

/* CGI cfg */

$lang['cgi_name'] = "CGI File Name";
$lang['cgi_comment'] = "Comments";
$lang['cgi_add'] = "Add a CGI Configuration File";
$lang['cgi_change'] = "Modify a CGI Configuration File";
$lang['cgi_view'] = "View a CGI Configuration File";
$lang['cgi_infos'] = "General Informations";
$lang['cgi_mainConfFile'] = "Main Configuration File Location";
$lang['cgi_phyHtmlPath'] = "Physical HTML Path";
$lang['cgi_urlHtmlPath'] = "URL HTML Path";
$lang['cgi_nagCheckCmd'] = "Nagios Process Check Command";
$lang['cgi_authUsage'] = "Authentication Usage";
$lang['cgi_defUserName'] = "Default User Name";
$lang['cgi_authFSysInfo'] = "System/Process Information Access";
$lang['cgi_authFSysCmd'] = "System/Process Command Access";
$lang['cgi_authFConfInf'] = "Configuration Information Access";
$lang['cgi_authFAllHosts'] = "Global Host Information Access";
$lang['cgi_authFAllHostCmds'] = "Global Host Command Access";
$lang['cgi_authFAllSv'] = "Global Service Information Access";
$lang['cgi_authFAllSvCmds'] = "Global Service Command Access";
$lang['cgi_smBckImg'] = "Statusmap CGI Background Image";
$lang['cgi_defSMLayMet'] = "Default Statusmap Layout Method";
$lang['cgi_statCGIIncWld'] = "Statuswrl CGI Include World";
$lang['cgi_defStatWRLLay'] = "Default Statuswrl Layout Method";
$lang['cgi_cgIRefRate'] = "CGI Refresh Rate";
$lang['cgi_hus'] = "Host Unreachable Sound";
$lang['cgi_hdu'] = "Host Down Sound";
$lang['cgi_scs'] = "Service Critical Sound";
$lang['cgi_sws'] = "Service Warning Sound";
$lang['cgi_sus'] = "Service Unknown Sound";
$lang['cgi_pingSyntax'] = "Ping Syntax";

/* Generate File */

$lang['gen_name'] = "Nagios Configuration Files Export";
$lang['gen_infos'] = "Serveur implied";
$lang['gen_host'] = "Nagios/Oreon Server";
$lang['gen_opt'] = "Export Options";
$lang['gen_ok'] = "Generate Files";
$lang['gen_level'] = "Relations between Elements";
$lang['gen_level1'] = "Dependencies Management";
$lang['gen_level2'] = "Current Activation";
$lang['gen_level3'] = "None";
$lang['gen_comment'] = "Include Comments";
$lang['gen_xml'] = "Export in XML too";
$lang['gen_result'] = "Result";
$lang['gen_debug'] = "Run Nagios debug (-v)";
$lang['gen_optimize'] = "Run Optimisation test (-s)";
$lang['gen_move'] = "Move Export Files";
$lang['gen_restart'] = "Restart Nagios";
$lang['gen_restart_load'] = "Reload";
$lang['gen_restart_start'] = "Restart";
$lang['gen_restart_extcmd'] = "External Command";
$lang['gen_butOK'] = "Export";
$lang['gen_status'] = "State";
$lang['gen_mvOk'] = " - movement OK";
$lang['gen_mvKo'] = " - movement KO";
$lang['gen_trapd'] = "Traps SNMP";
$lang['gen_genTrap'] = "Export configuration files";


/* Upload File */

$lang['upl_name'] = "Nagios Configuration Upload";
$lang['upl_infos'] = "Serveur implied";
$lang['upl_host'] = "Nagios/Oreon Server";
$lang['upl_opt'] = "Upload Options";
$lang['upl_del'] = "Delete all configuration for kind of files choose";
$lang['upl_over'] = "Update definition in same dual definition";
$lang['upl_comment'] = "Include comments";
$lang['upl_type'] = "File Type";
$lang['upl_mis1'] = "For archive upload, be sure that the first line of each file have no importance because it's doesn't manage.<br>Avoid to begin with a definition.";
$lang['upl_typeNag'] = "nagios.cfg";
$lang['upl_typeCgi'] = "cgi.cfg";
$lang['upl_typePerfparse'] = "perfparse.cfg";
$lang['upl_typeRes'] = "resource.cfg";
$lang['upl_typeCfg'] = "Template based method file";
$lang['upl_typeManual'] = "Manual Filling";
$lang['upl_format'] = "File Type";
$lang['upl_typeName'] = "Type";
$lang['upl_typeCmdType'] = "Command Type";
$lang['upl_typeCmdCheck'] = "Check Command";
$lang['upl_typeCmdNotif'] = "Notification Command";
$lang['upl_typeCmdCmt1'] = "You should upload before all the Commands definitions while specifying their type.";
$lang['upl_typeCmdCmt2'] = "Indeed, it's the only way to make difference between Check or Notification Commands.";
$lang['upl_file'] = "File (zip, tar or cfg)";
$lang['upl_manualDef'] = "Manual Filling";
$lang['upl_result'] = "Result";
$lang['upl_debug'] = "Run Nagios debug (-v)";
$lang['upl_butOK'] = "Load";
$lang['upl_uplOk'] = "File loading OK";
$lang['upl_uplKo'] = "File loading KO";
$lang['upl_carrOk'] = "Data recovery OK";
$lang['upl_carrKo'] = "Data recovery KO";
$lang['upl_manualDefOk'] = "Manual filling OK";
$lang['upl_uplBadType'] = "Not supported extension";
$lang['upl_newEntries'] = "recorded entrie(s)";


/* Purge Policy Template */

$lang['mod_purgePolicy'] = "Template Deletion Policy";
$lang['mod_purgePolicy_add'] = "Add a Template Deletion Policy";
$lang['mod_purgePolicy_change'] = "Modify a Template Deletion Policy";
$lang['mod_purgePolicy_view'] = "View a Template Deletion Policy";

$lang['mod_purgePolicy_infos'] = "General informations";
$lang['mod_purgePolicy_name'] = "Policy Name";
$lang['mod_purgePolicy_alias'] = "Alias";
$lang['mod_purgePolicy_retain'] = "Retention Period";
$lang['mod_purgePolicy_raw'] = "Raw Deletion";
$lang['mod_purgePolicy_raw2'] = "Only raw rows according to the retention period";
$lang['mod_purgePolicy_bin'] = "Bin Deletion";
$lang['mod_purgePolicy_bin2'] = "Only bin rows according to the retention period";
$lang['mod_purgePolicy_metric'] = "Metric Definition Deletion";
$lang['mod_purgePolicy_metric2'] = "Not link with period, ALL metric + bin";
$lang['mod_purgePolicy_service'] = "Service Definition Deletion";
$lang['mod_purgePolicy_service2'] = "Not link with period, ALL Service + Metric + bin + raw";
$lang['mod_purgePolicy_host'] = "Host Definition Deletion";
$lang['mod_purgePolicy_host2'] = "Not link with period, ALL Host + Service + Metric + bin + raw";
$lang['mod_purgePolicy_comment'] = "Comments";

$lang['mod_purgePolicy_listRaw'] = "Raw";
$lang['mod_purgePolicy_listBin'] = "Bin";
$lang['mod_purgePolicy_listMetric'] = "Metric";
$lang['mod_purgePolicy_listService'] = "Service";
$lang['mod_purgePolicy_listHost'] = "Host";

/* Traps SNMP */

$lang['m_traps_command'] = "SNMP Traps";
$lang['m_traps_name'] = "Trap name";
$lang['m_traps_oid'] = "OID";
$lang['m_traps_handler'] = "Handler";
$lang['m_traps_args'] = "Arguments";
$lang['m_traps_status'] = "Status";
$lang['m_traps_comments'] = "Comments";
$lang['m_traps_manufacturer'] = "Mibs Vendor";
$lang['m_traps_desc'] = "Description";
$lang['m_traps_alias'] = "Alias";

$lang['m_traps_add'] = "Add a Trap definition";
$lang['m_traps_change'] = "Modify a Trap definition";
$lang['m_traps_view'] = "View a Trap definition";

/* Manufacturer */
	
$lang['m_mnftr_name'] = "Mibs Vendor Name";
$lang['m_mnftr_alias'] = "Alias";
$lang['m_mnftr_desc'] = "Description";

$lang['m_mnftr_add'] = "Add Mibs Vendor";
$lang['m_mnftr_change'] = "Modify Mibs Vendor";
$lang['m_mnftr_view'] = "View Mibs Vendor";

/* Mibs */
	
$lang['m_mibs_mnftr'] = "Mibs Vendor Name";
$lang['m_mibs_file'] = "File (.mib)";
$lang['m_mibs_title'] = "Load a MIB";
$lang['load'] = "Load";
$lang['mibs_status'] = "Status";

/* GANTT Escalation */

$lang['m_gantt'] = "Escalations Viewer";
$lang['m_header_gantt'] = "Escalations View";

?>