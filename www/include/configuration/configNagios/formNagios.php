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

	#
	## Database retrieve information for Nagios
	#
	$nagios = array();
	if (($o == "c" || $o == "w") && $nagios_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM cfg_nagios WHERE nagios_id = '".$nagios_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$nagios = array_map("myDecode", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Check commands comes from DB -> Store in $checkCmds Array
	$checkCmds = array();
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command ORDER BY command_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$checkCmds = array(NULL=>NULL);
	while($DBRESULT->fetchInto($checkCmd))
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText		= array("size"=>"30");
	$attrsText2 	= array("size"=>"50");
	$attrsText3 	= array("size"=>"10");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["nagios_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["nagios_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["nagios_view"]);

	#
	## Nagios Configuration basic information
	#
	$form->addElement('header', 'information', $lang['nagios_infos']);
	$form->addElement('text', 'nagios_name', $lang["nagios_name"], $attrsText);
	$form->addElement('textarea', 'nagios_comment', $lang["nagios_comment"], $attrsTextarea);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'nagios_activate', null, $lang["enable"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'nagios_activate', null, $lang["disable"], '0');
	$form->addGroup($nagTab, 'nagios_activate', $lang["status"], '&nbsp;');	
	
	## Part 1
	$form->addElement('text', 'log_file', $lang["nag_logFile"], $attrsText2);
	$form->addElement('text', 'cfg_dir', $lang["nag_objConfDir"], $attrsText2);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'object_cache_file', $lang["nag_objCacheFile"], $attrsText2);
	$form->addElement('text', 'temp_file', $lang["nag_tmpFile"], $attrsText2);
	$form->addElement('text', 'p1_file', $lang["nag_p1File"], $attrsText2);
	
	## Part 2
	$form->addElement('text', 'status_file', $lang["nag_statusFile"], $attrsText2);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'aggregate_status_updates', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'aggregate_status_updates', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'aggregate_status_updates', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'aggregate_status_updates', $lang["nag_asuOpt"], '&nbsp;');
	$form->addElement('text', 'status_update_interval', $lang["nag_asuInt"], $attrsText3);
	
	## Part 3
	$form->addElement('text', 'nagios_user', $lang["nag_nagUser"], $attrsText);
	$form->addElement('text', 'nagios_group', $lang["nag_nagGroup"], $attrsText);
	
	## Part 4
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_notifications', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_notifications', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_notifications', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'enable_notifications', $lang["nag_notifOpt"], '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_service_checks', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_service_checks', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_service_checks', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'execute_service_checks', $lang["nag_svCheckExeOpt"], '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_service_checks', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_service_checks', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_service_checks', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'accept_passive_service_checks', $lang["nag_pasSvCheckAccOpt"], '&nbsp;');
	if($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_host_checks', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_host_checks', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_host_checks', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'execute_host_checks', $lang["nag_hostCheckExeOpt"], '&nbsp;');
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_host_checks', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_host_checks', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_host_checks', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'accept_passive_host_checks', $lang["nag_pasHostCheckAccOpt"], '&nbsp;');
	}
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_event_handlers', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_event_handlers', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_event_handlers', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'enable_event_handlers', $lang["nag_eventHandOpt"], '&nbsp;');
	
	## Part 5
	$nagTab = array();
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'n', 'n');
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'h', 'h');
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'd', 'd');
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'w', 'w');
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'm', 'm');
	$form->addGroup($nagTab, 'log_rotation_method', $lang["nag_logRotMethod"], '&nbsp;&nbsp;');
	$form->addElement('text', 'log_archive_path', $lang["nag_logArchPath"], $attrsText2);
	
	## Part 6	
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_external_commands', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_external_commands', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_external_commands', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'check_external_commands', $lang["nag_extCmdCheckOpt"], '&nbsp;');
	$form->addElement('text', 'command_check_interval', $lang["nag_extCmdCheckInt"], $attrsText3);
	$form->addElement('text', 'command_file', $lang["nag_extCmdFile"], $attrsText2);
	
	## Part 7
	$form->addElement('text', 'downtime_file', $lang["nag_dtFile"], $attrsText2);
	$form->addElement('text', 'comment_file', $lang["nag_cmtFile"], $attrsText2);
	$form->addElement('text', 'lock_file', $lang["nag_lockFile"], $attrsText2);
	
	## Part 8
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'retain_state_information', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'retain_state_information', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'retain_state_information', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'retain_state_information', $lang["nag_stateRetOpt"], '&nbsp;');
	$form->addElement('text', 'state_retention_file', $lang["nag_stateRetFile"], $attrsText2);
	$form->addElement('text', 'retention_update_interval', $lang["nag_autStateRetUpdInt"], $attrsText3);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_program_state', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_program_state', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_program_state', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'use_retained_program_state', $lang["nag_useRetPgmStateOpt"], '&nbsp;');
	if($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_scheduling_info', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_scheduling_info', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_scheduling_info', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'use_retained_scheduling_info', $lang["nag_useRetSchInfoOpt"], '&nbsp;');
	}
	
	## Part 9
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_syslog', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_syslog', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_syslog', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'use_syslog', $lang["nag_SysLogOpt"], '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_notifications', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_notifications', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_notifications', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'log_notifications', $lang["nag_notLogOpt"], '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_service_retries', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_service_retries', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_service_retries', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'log_service_retries', $lang["nag_svCheckRtrLogOpt"], '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_host_retries', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_host_retries', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_host_retries', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'log_host_retries', $lang["nag_hostRtrLogOpt"], '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_event_handlers', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_event_handlers', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_event_handlers', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'log_event_handlers', $lang["nag_eventHandLogOpt"], '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_initial_states', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_initial_states', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_initial_states', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'log_initial_states', $lang["nag_iniStateLogOpt"], '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_external_commands', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_external_commands', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_external_commands', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'log_external_commands', $lang["nag_extCmdLogOpt"], '&nbsp;');
	if ($oreon->user->get_version() == 1)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_service_checks', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_service_checks', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_service_checks', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'log_passive_service_checks', $lang["nag_passSvCheckLogOpt"], '&nbsp;');
	}
	else if ($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_checks', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_checks', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_checks', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'log_passive_checks', $lang["nag_passCheckLogOpt"], '&nbsp;');
	}
	
	## Part 10
	$form->addElement('select', 'global_host_event_handler', $lang["nag_glHostEventHand"], $checkCmds);
	$form->addElement('select', 'global_service_event_handler', $lang["nag_glSvEventHand"], $checkCmds);
	
	## Part 11
	$form->addElement('text', 'sleep_time', $lang["nag_intCheckSleepTm"], $attrsText3);
	if ($oreon->user->get_version() == 1)	{
		/*$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'inter_check_delay_method', null, 'n', 'n');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'inter_check_delay_method', null, 'd', 'd');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'inter_check_delay_method', null, 's', 's');	
		$form->addGroup($nagTab, 'inter_check_delay_method', $lang["nag_intCheckDelMth"], '&nbsp;');*/
		$form->addElement('text', 'inter_check_delay_method', $lang["nag_intCheckDelMth"], $attrsText3);
	}
	else if ($oreon->user->get_version() == 2)	{
		/*$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_inter_check_delay_method', null, 'n', 'n');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_inter_check_delay_method', null, 'd', 'd');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_inter_check_delay_method', null, 's', 's');	
		$form->addGroup($nagTab, 'service_inter_check_delay_method', $lang["nag_svIntCheckDelMth"], '&nbsp;');*/
		$form->addElement('text', 'service_inter_check_delay_method', $lang["nag_svIntCheckDelMth"], $attrsText3);
		$form->addElement('text', 'max_service_check_spread', $lang["nag_maxSvCheckSpread"], $attrsText3);
	}
	$form->addElement('text', 'service_interleave_factor', $lang["nag_svInterFac"], $attrsText3);
	$form->addElement('text', 'max_concurrent_checks', $lang["nag_maxConcSvChecks"], $attrsText3);
	$form->addElement('text', 'service_reaper_frequency', $lang["nag_svReapFreq"], $attrsText3);
	if ($oreon->user->get_version() == 2)	{
		/*$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_inter_check_delay_method', null, 'n', 'n');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_inter_check_delay_method', null, 'd', 'd');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_inter_check_delay_method', null, 's', 's');	
		$form->addGroup($nagTab, 'host_inter_check_delay_method', $lang["nag_hostIntCheckDelMth"], '&nbsp;');*/
		$form->addElement('text', 'host_inter_check_delay_method', $lang["nag_hostIntCheckDelMth"], $attrsText3);
		$form->addElement('text', 'max_host_check_spread', $lang["nag_maxHostCheckSpread"], $attrsText3);
	}
	$form->addElement('text', 'interval_length', $lang["nag_tmIntLen"], $attrsText3);
	if ($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'auto_reschedule_checks', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'auto_reschedule_checks', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'auto_reschedule_checks', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'auto_reschedule_checks', $lang["nag_autoRescheOpt"], '&nbsp;');
		$form->addElement('text', 'auto_rescheduling_interval', $lang["nag_autoRescheInt"], $attrsText3);
		$form->addElement('text', 'auto_rescheduling_window', $lang["nag_autoRescheWnd"], $attrsText3);
	}
	
	## Part 12
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_agressive_host_checking', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_agressive_host_checking', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_agressive_host_checking', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'use_agressive_host_checking', $lang["nag_aggHostCheckOpt"], '&nbsp;');
	
	## Part 13
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_flap_detection', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_flap_detection', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_flap_detection', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'enable_flap_detection', $lang["nag_flapDetOpt"], '&nbsp;');
	$form->addElement('text', 'low_service_flap_threshold', $lang["nag_lowSvFlapThres"], $attrsText3);
	$form->addElement('text', 'high_service_flap_threshold', $lang["nag_highSvFlapThres"], $attrsText3);
	$form->addElement('text', 'low_host_flap_threshold', $lang["nag_lowHostFlapThres"], $attrsText3);
	$form->addElement('text', 'high_host_flap_threshold', $lang["nag_highHostFlapThres"], $attrsText3);
	
	## Part 14
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'soft_state_dependencies', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'soft_state_dependencies', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'soft_state_dependencies', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'soft_state_dependencies', $lang["nag_softSvDepOpt"], '&nbsp;');
	
	## Part 15
	$form->addElement('text', 'service_check_timeout', $lang["nag_svCheckTmOut"], $attrsText3);
	$form->addElement('text', 'host_check_timeout', $lang["nag_hostCheckTmOut"], $attrsText3);
	$form->addElement('text', 'event_handler_timeout', $lang["nag_eventHandTmOut"], $attrsText3);
	$form->addElement('text', 'notification_timeout', $lang["nag_notifTmOut"], $attrsText3);
	$form->addElement('text', 'ocsp_timeout', $lang["nag_obComSvProcTmOut"], $attrsText3);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'ochp_timeout', $lang["nag_obComHostProcTmOut"], $attrsText3);
	$form->addElement('text', 'perfdata_timeout', $lang["nag_perfDataProcCmdTmOut"], $attrsText3);
	
	## Part 16
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_services', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_services', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_services', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'obsess_over_services', $lang["nag_obsOverSvOpt"], '&nbsp;');
	$form->addElement('select', 'ocsp_command', $lang["nag_obsComSvProcCmd"], $checkCmds);
	if ($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_hosts', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_hosts', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_hosts', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'obsess_over_hosts', $lang["nag_obsOverHostOpt"], '&nbsp;');
		$form->addElement('select', 'ochp_command', $lang["nag_obsComHostProcCmd"], $checkCmds);
	}
	
	## Part 17
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'process_performance_data', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'process_performance_data', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'process_performance_data', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'process_performance_data', $lang["nag_perfDataProcOpt"], '&nbsp;');
	$form->addElement('select', 'host_perfdata_command', $lang["nag_hostPerfDataProcCmd"], $checkCmds);
	$form->addElement('select', 'service_perfdata_command', $lang["nag_svPerfDataProcCmd"], $checkCmds);
	if ($oreon->user->get_version() == 2)	{
		$form->addElement('text', 'host_perfdata_file', $lang["nag_hostPerfDataFile"], $attrsText2);
		$form->addElement('text', 'service_perfdata_file', $lang["nag_svPerfDataFile"], $attrsText2);
		$form->addElement('textarea', 'host_perfdata_file_template', $lang["nag_hostPerfDataFileTmp"], $attrsTextarea);
		$form->addElement('textarea', 'service_perfdata_file_template', $lang["nag_svPerfDataFileTmp"], $attrsTextarea);
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_perfdata_file_mode', null, 'a', 'a');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_perfdata_file_mode', null, 'w', 'w');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_perfdata_file_mode', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'host_perfdata_file_mode', $lang["nag_hostPerfDataFileMode"], '&nbsp;');
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_perfdata_file_mode', null, 'a', 'a');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_perfdata_file_mode', null, 'w', 'w');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_perfdata_file_mode', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'service_perfdata_file_mode', $lang["nag_svPerfDataFileMode"], '&nbsp;');
		$form->addElement('text', 'host_perfdata_file_processing_interval', $lang["nag_hostPerfDataFileProcInt"], $attrsText3);
		$form->addElement('text', 'service_perfdata_file_processing_interval', $lang["nag_svPerfDataFileProcInt"], $attrsText3);
		$form->addElement('select', 'host_perfdata_file_processing_command', $lang["nag_hostPerfDataFileProcCmd"], $checkCmds);
		$form->addElement('select', 'service_perfdata_file_processing_command', $lang["nag_svPerfDataFileProcCmd"], $checkCmds);		
	}
	
	## Part 18
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_for_orphaned_services', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_for_orphaned_services', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_for_orphaned_services', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'check_for_orphaned_services', $lang["nag_OrpSvCheckOpt"], '&nbsp;');
	
	## Part 19
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_service_freshness', null, $lang["yes"], '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_service_freshness', null, $lang["no"], '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_service_freshness', null, $lang["nothing"], '2');
	$form->addGroup($nagTab, 'check_service_freshness', $lang["nag_svFreshCheckOpt"], '&nbsp;');
	if ($oreon->user->get_version() == 1)
		$form->addElement('text', 'freshness_check_interval', $lang["nag_freshCheckInt"], $attrsText);
	else if ($oreon->user->get_version() == 2)	{
		$form->addElement('text', 'service_freshness_check_interval', $lang["nag_svFreshCheckInt"], $attrsText);
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_host_freshness', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_host_freshness', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_host_freshness', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'check_host_freshness', $lang["nag_hostFreshCheckOpt"], '&nbsp;');
		$form->addElement('text', 'host_freshness_check_interval', $lang["nag_hostFreshCheckInt"], $attrsText);
	}
	
	## Part 20
	$form->addElement('text', 'date_format', $lang["nag_dateFormat"], $attrsText);
	$form->addElement('text', 'illegal_object_name_chars', $lang["nag_illObjNameChar"], $attrsText2);
	$form->addElement('text', 'illegal_macro_output_chars', $lang["nag_illMacOutChar"], $attrsText2);
	if ($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_regexp_matching', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_regexp_matching', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_regexp_matching', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'use_regexp_matching', $lang["nag_regExpMatchOpt"], '&nbsp;');
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_true_regexp_matching', null, $lang["yes"], '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_true_regexp_matching', null, $lang["no"], '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_true_regexp_matching', null, $lang["nothing"], '2');
		$form->addGroup($nagTab, 'use_true_regexp_matching', $lang["nag_trueRegExpMatchOpt"], '&nbsp;');
	}
	
	## Part 21
	$form->addElement('text', 'admin_email', $lang["nag_adminEmail"], $attrsText);
	$form->addElement('text', 'admin_pager', $lang["nag_adminPager"], $attrsText);
		
	## Part 22
	$form->addElement('text', 'broker_module', $lang["nag_broker_module"], $attrsText2);
	$form->addElement('text', 'event_broker_options', $lang["nag_broker_module_options"], $attrsText2);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	
	$form->setDefaults(array(
	"nagios_activate"=>'0',
	"aggregate_status_updates"=>'1',
	"nagios_user"=>"nagios_user",
	"nagios_group"=>"nagios",
	"enable_notifications"=>'1',
	"execute_service_checks"=>'1',
	"accept_passive_service_checks"=>'1',
	"execute_host_checks"=>'1',
	"accept_passive_host_checks"=>'1',
	"enable_event_handlers"=>'1',
	"log_rotation_method"=>'n',
	"check_external_commands"=>'0',
	"retain_state_information"=>'0',
	"use_retained_program_state"=>'1',
	"use_retained_scheduling_info"=>'1',
	"use_syslog"=>'2',
	"log_notifications"=>'2',
	"log_service_retries"=>'2',
	"log_host_retries"=>'2',
	"log_event_handlers"=>'2',
	"log_initial_states"=>'0',
	"log_external_commands"=>'1',
	"log_passive_checks"=>'1',
	"inter_check_delay_method"=>'s',
	"service_inter_check_delay_method"=>'s',
	"service_interleave_factor"=>'s',
	"host_inter_check_delay_method"=>'s',
	"auto_reschedule_checks"=>'2',
	"use_aggressive_host_checking"=>'0',
	"enable_flap_detection"=>'0',
	"soft_state_dependencies"=>'0',
	"obsess_over_services"=>'0',
	"obsess_over_hosts"=>'0',
	"process_performance_data"=>'0',
	"host_perfdata_file_mode"=>'a',
	"service_perfdata_file_mode"=>'a',
	"check_for_orphaned_services"=>'0',
	"check_service_freshness"=>'1',
	"check_host_freshness"=>'1',
	"illegal_object_name_chars"=>"~!$%^&*\"|'<>?,()=",
	"illegal_macro_output_chars"=>"`~$^&\"|'<>",
	"use_regexp_matching"=>'0',
	"use_true_regexp_matching"=>'0',
	'action'=>'1'
	));
		
	$form->addElement('hidden', 'nagios_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('cfg_dir', 'slash');
	$form->applyFilter('log_archive_path', 'slash');
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('nagios_name', $lang['ErrName'], 'required');
	$form->addRule('nagios_comment', $lang['ErrRequired'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('nagios_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a nagios information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&nagios_id=".$nagios_id."'"));
	    $form->setDefaults($nagios);
		$form->freeze();
	}
	# Modify a nagios information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($nagios);
	}
	# Add a nagios information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version()));
	
	$valid = false;
	if ($form->validate())	{
		$nagiosObj =& $form->getElement('nagios_id');
		if ($form->getSubmitValue("submitA"))
			$nagiosObj->setValue(insertNagiosInDB());
		else if ($form->getSubmitValue("submitC"))
			updateNagiosInDB($nagiosObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&nagios_id=".$nagiosObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listNagios.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formNagios.ihtml");
	}
?>