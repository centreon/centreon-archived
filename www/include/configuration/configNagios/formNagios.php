<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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
	
	# Get all nagios servers
	$nagios_server = array(NULL => "");
	$DBRESULT =& $pearDB->query("SELECT `name`, `id` FROM `nagios_server`");
	for ($i = 0; $ns = $DBRESULT->fetchRow(); $i++)
		$nagios_server[$ns["id"]] = $ns["name"];
	$DBRESULT->free();
	unset($ns);
	
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
		$form->addElement('header', 'title', _("Add a Nagios Configuration File"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Nagios Configuration File"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Nagios Configuration File"));

	#
	## Nagios Configuration basic information
	#
	$form->addElement('header', 'information', _("Information"));
	$form->addElement('text', 'nagios_name', _("Configuration Name"), $attrsText);
	$form->addElement('textarea', 'nagios_comment', _("Comments"), $attrsTextarea);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'nagios_activate', null, _("Enabled"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'nagios_activate', null, _("Disabled"), '0');
	$form->addGroup($nagTab, 'nagios_activate', _("Status"), '&nbsp;');	
	
	$form->addElement('select', 'nagios_server_id', _("Server Nagios configured"), $nagios_server);
	
	## Part 1
	$form->addElement('text', 'log_file', _("Log file"), $attrsText2);
	$form->addElement('text', 'cfg_dir', _("Object Configuration Directory"), $attrsText2);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'object_cache_file', _("Object Cache File"), $attrsText2);
	$form->addElement('text', 'temp_file', _("Temp File"), $attrsText2);
	$form->addElement('text', 'p1_file', _("P1 File"), $attrsText2);
	
	## Part 2
	$form->addElement('text', 'status_file', _("Status File"), $attrsText2);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'aggregate_status_updates', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'aggregate_status_updates', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'aggregate_status_updates', null, _("Default"), '2');
	$form->addGroup($nagTab, 'aggregate_status_updates', _("Aggregated Status Updates Option"), '&nbsp;');
	$form->addElement('text', 'status_update_interval', _("Aggregated Status Data Update Interval"), $attrsText3);
	
	## Part 3
	$form->addElement('text', 'nagios_user', _("Nagios User"), $attrsText);
	$form->addElement('text', 'nagios_group', _("Nagios Group"), $attrsText);
	
	## Part 4
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_notifications', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_notifications', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_notifications', null, _("Default"), '2');
	$form->addGroup($nagTab, 'enable_notifications', _("Notification Option"), '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_service_checks', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_service_checks', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_service_checks', null, _("Default"), '2');
	$form->addGroup($nagTab, 'execute_service_checks', _("Service Check Execution Option"), '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_service_checks', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_service_checks', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_service_checks', null, _("Default"), '2');
	$form->addGroup($nagTab, 'accept_passive_service_checks', _("Passive Service Check Acceptance Option"), '&nbsp;');
	if($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_host_checks', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_host_checks', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'execute_host_checks', null, _("Default"), '2');
		$form->addGroup($nagTab, 'execute_host_checks', _("Host Check Execution Option"), '&nbsp;');
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_host_checks', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_host_checks', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'accept_passive_host_checks', null, _("Default"), '2');
		$form->addGroup($nagTab, 'accept_passive_host_checks', _("Passive Host Check Acceptance Option"), '&nbsp;');
	}
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_event_handlers', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_event_handlers', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_event_handlers', null, _("Default"), '2');
	$form->addGroup($nagTab, 'enable_event_handlers', _("Event Handler Option"), '&nbsp;');
	
	## Part 5
	$nagTab = array();
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'n', 'n');
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'h', 'h');
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'd', 'd');
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'w', 'w');
 	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_rotation_method', null, 'm', 'm');
	$form->addGroup($nagTab, 'log_rotation_method', _("Log Rotation Method"), '&nbsp;&nbsp;');
	$form->addElement('text', 'log_archive_path', _("Log Archive Path"), $attrsText2);
	
	## Part 6	
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_external_commands', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_external_commands', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_external_commands', null, _("Default"), '2');
	$form->addGroup($nagTab, 'check_external_commands', _("External Command Check Option"), '&nbsp;');
	$form->addElement('text', 'command_check_interval', _("External Command Check Interval"), $attrsText3);
	$form->addElement('text', 'command_file', _("External Command File"), $attrsText2);
	
	## Part 7
	$form->addElement('text', 'downtime_file', _("Downtime File"), $attrsText2);
	$form->addElement('text', 'comment_file', _("Comment File"), $attrsText2);
	$form->addElement('text', 'lock_file', _("Lock File"), $attrsText2);
	
	## Part 8
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'retain_state_information', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'retain_state_information', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'retain_state_information', null, _("Default"), '2');
	$form->addGroup($nagTab, 'retain_state_information', _("State Retention Option"), '&nbsp;');
	$form->addElement('text', 'state_retention_file', _("State Retention File"), $attrsText2);
	$form->addElement('text', 'retention_update_interval', _("Automatic State Retention Update Interval"), $attrsText3);
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_program_state', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_program_state', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_program_state', null, _("Default"), '2');
	$form->addGroup($nagTab, 'use_retained_program_state', _("Use Retained Program State Option"), '&nbsp;');
	if($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_scheduling_info', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_scheduling_info', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_retained_scheduling_info', null, _("Default"), '2');
		$form->addGroup($nagTab, 'use_retained_scheduling_info', _("Use Retained Scheduling Info Option"), '&nbsp;');
	}
	
	## Part 9
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_syslog', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_syslog', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_syslog', null, _("Default"), '2');
	$form->addGroup($nagTab, 'use_syslog', _("Syslog Logging Option"), '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_notifications', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_notifications', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_notifications', null, _("Default"), '2');
	$form->addGroup($nagTab, 'log_notifications', _("Notification Logging Option"), '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_service_retries', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_service_retries', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_service_retries', null, _("Default"), '2');
	$form->addGroup($nagTab, 'log_service_retries', _("Service Check Retry Logging Option"), '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_host_retries', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_host_retries', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_host_retries', null, _("Default"), '2');
	$form->addGroup($nagTab, 'log_host_retries', _("Host Retry Logging Option"), '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_event_handlers', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_event_handlers', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_event_handlers', null, _("Default"), '2');
	$form->addGroup($nagTab, 'log_event_handlers', _("Event Handler Logging Option"), '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_initial_states', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_initial_states', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_initial_states', null, _("Default"), '2');
	$form->addGroup($nagTab, 'log_initial_states', _("Initial State Logging Option"), '&nbsp;');
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_external_commands', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_external_commands', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_external_commands', null, _("Default"), '2');
	$form->addGroup($nagTab, 'log_external_commands', _("External Command Logging Option"), '&nbsp;');
	if ($oreon->user->get_version() == 1)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_service_checks', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_service_checks', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_service_checks', null, _("Default"), '2');
		$form->addGroup($nagTab, 'log_passive_service_checks', _("Passive Service Check Logging Option"), '&nbsp;');
	}
	else if ($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_checks', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_checks', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'log_passive_checks', null, _("Default"), '2');
		$form->addGroup($nagTab, 'log_passive_checks', _("Passive Check Logging Option"), '&nbsp;');
	}
	
	## Part 10
	$form->addElement('select', 'global_host_event_handler', _("Global Host Event Handler"), $checkCmds);
	$form->addElement('select', 'global_service_event_handler', _("Global Service Event Handler"), $checkCmds);
	
	## Part 11
	$form->addElement('text', 'sleep_time', _("Inter-Check Sleep Time"), $attrsText3);
	if ($oreon->user->get_version() == 1)	{
		/*$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'inter_check_delay_method', null, 'n', 'n');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'inter_check_delay_method', null, 'd', 'd');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'inter_check_delay_method', null, 's', 's');	
		$form->addGroup($nagTab, 'inter_check_delay_method', $lang["nag_intCheckDelMth"], '&nbsp;');*/
		$form->addElement('text', 'inter_check_delay_method', _("Inter-Check Delay Method"), $attrsText3);
	}
	else if ($oreon->user->get_version() == 2)	{
		/*$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_inter_check_delay_method', null, 'n', 'n');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_inter_check_delay_method', null, 'd', 'd');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_inter_check_delay_method', null, 's', 's');	
		$form->addGroup($nagTab, 'service_inter_check_delay_method', $lang["nag_svIntCheckDelMth"], '&nbsp;');*/
		$form->addElement('text', 'service_inter_check_delay_method', _("Service Inter-Check Delay Method"), $attrsText3);
		$form->addElement('text', 'max_service_check_spread', _("Maximum Service Check Spread"), $attrsText3);
	}
	$form->addElement('text', 'service_interleave_factor', _("Service Interleave Factor"), $attrsText3);
	$form->addElement('text', 'max_concurrent_checks', _("Maximum Concurrent Service Checks"), $attrsText3);
	$form->addElement('text', 'service_reaper_frequency', _("Service Repear Frequency"), $attrsText3);
	if ($oreon->user->get_version() == 2)	{
		/*$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_inter_check_delay_method', null, 'n', 'n');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_inter_check_delay_method', null, 'd', 'd');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_inter_check_delay_method', null, 's', 's');	
		$form->addGroup($nagTab, 'host_inter_check_delay_method', $lang["nag_hostIntCheckDelMth"], '&nbsp;');*/
		$form->addElement('text', 'host_inter_check_delay_method', _("Host Inter-Check Delay Method"), $attrsText3);
		$form->addElement('text', 'max_host_check_spread', _("Maximum Host Check Spread"), $attrsText3);
	}
	$form->addElement('text', 'interval_length', _("Timing Interval Length"), $attrsText3);
	if ($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'auto_reschedule_checks', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'auto_reschedule_checks', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'auto_reschedule_checks', null, _("Default"), '2');
		$form->addGroup($nagTab, 'auto_reschedule_checks', _("Auto-Rescheduling Option"), '&nbsp;');
		$form->addElement('text', 'auto_rescheduling_interval', _("Auto-Rescheduling Interval"), $attrsText3);
		$form->addElement('text', 'auto_rescheduling_window', _("Auto-Rescheduling Window"), $attrsText3);
	}
	
	## Part 12
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_agressive_host_checking', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_agressive_host_checking', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_agressive_host_checking', null, _("Default"), '2');
	$form->addGroup($nagTab, 'use_agressive_host_checking', _("Aggressive Host Checking Option"), '&nbsp;');
	
	## Part 13
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_flap_detection', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_flap_detection', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'enable_flap_detection', null, _("Default"), '2');
	$form->addGroup($nagTab, 'enable_flap_detection', _("Flap Detection Option"), '&nbsp;');
	$form->addElement('text', 'low_service_flap_threshold', _("Low Service Flap Threshold"), $attrsText3);
	$form->addElement('text', 'high_service_flap_threshold', _("High Service Flap Threshold"), $attrsText3);
	$form->addElement('text', 'low_host_flap_threshold', _("Low Host Flap Threshold"), $attrsText3);
	$form->addElement('text', 'high_host_flap_threshold', _("High Host Flap Threshold"), $attrsText3);
	
	## Part 14
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'soft_state_dependencies', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'soft_state_dependencies', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'soft_state_dependencies', null, _("Default"), '2');
	$form->addGroup($nagTab, 'soft_state_dependencies', _("Soft Service Dependencies Option"), '&nbsp;');
	
	## Part 15
	$form->addElement('text', 'service_check_timeout', _("Service Check Timeout"), $attrsText3);
	$form->addElement('text', 'host_check_timeout', _("Host Check Timeout"), $attrsText3);
	$form->addElement('text', 'event_handler_timeout', _("Event Handler Timeout"), $attrsText3);
	$form->addElement('text', 'notification_timeout', _("Notification Timeout"), $attrsText3);
	$form->addElement('text', 'ocsp_timeout', _("Obsessive Compulsive Service Processor Timeout"), $attrsText3);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'ochp_timeout', _("Obsessive Compulsive Host Processor Timeout"), $attrsText3);
	$form->addElement('text', 'perfdata_timeout', _("Performance Data Processor Command Timeout"), $attrsText3);
	
	## Part 16
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_services', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_services', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_services', null, _("Default"), '2');
	$form->addGroup($nagTab, 'obsess_over_services', _("Obsess Over Services Option"), '&nbsp;');
	$form->addElement('select', 'ocsp_command', _("Obsessive Compulsive Service Processor Command"), $checkCmds);
	if ($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_hosts', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_hosts', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'obsess_over_hosts', null, _("Default"), '2');
		$form->addGroup($nagTab, 'obsess_over_hosts', _("Obsess Over Hosts Option"), '&nbsp;');
		$form->addElement('select', 'ochp_command', _("Obsessive Compulsive Host Processor Command"), $checkCmds);
	}
	
	## Part 17
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'process_performance_data', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'process_performance_data', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'process_performance_data', null, _("Default"), '2');
	$form->addGroup($nagTab, 'process_performance_data', _("Performance Data Processing Option"), '&nbsp;');
	$form->addElement('select', 'host_perfdata_command', _("Host Performance Data Processing Command"), $checkCmds);
	$form->addElement('select', 'service_perfdata_command', _("Service Performance Data Processing Command"), $checkCmds);
	if ($oreon->user->get_version() == 2)	{
		$form->addElement('text', 'host_perfdata_file', _("Host Performance Data File"), $attrsText2);
		$form->addElement('text', 'service_perfdata_file', _("Service Performance Data File"), $attrsText2);
		$form->addElement('textarea', 'host_perfdata_file_template', _("Host Performance Data File Template"), $attrsTextarea);
		$form->addElement('textarea', 'service_perfdata_file_template', _("Service Performance Data File Template"), $attrsTextarea);
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_perfdata_file_mode', null, 'a', 'a');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_perfdata_file_mode', null, 'w', 'w');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'host_perfdata_file_mode', null, _("Default"), '2');
		$form->addGroup($nagTab, 'host_perfdata_file_mode', _("Host Performance Data File Mode"), '&nbsp;');
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_perfdata_file_mode', null, 'a', 'a');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_perfdata_file_mode', null, 'w', 'w');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'service_perfdata_file_mode', null, _("Default"), '2');
		$form->addGroup($nagTab, 'service_perfdata_file_mode', _("Service Performance Data File Mode"), '&nbsp;');
		$form->addElement('text', 'host_perfdata_file_processing_interval', _("Host Performance Data File Processing Interval"), $attrsText3);
		$form->addElement('text', 'service_perfdata_file_processing_interval', _("Service Performance Data File Processing Interval"), $attrsText3);
		$form->addElement('select', 'host_perfdata_file_processing_command', _("Host Performance Data File Processing Command"), $checkCmds);
		$form->addElement('select', 'service_perfdata_file_processing_command', _("Service Performance Data File Processing Command"), $checkCmds);		
	}
	
	## Part 18
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_for_orphaned_services', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_for_orphaned_services', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_for_orphaned_services', null, _("Default"), '2');
	$form->addGroup($nagTab, 'check_for_orphaned_services', _("Orphaned Service Check Option"), '&nbsp;');
	
	## Part 19
	$nagTab = array();
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_service_freshness', null, _("Yes"), '1');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_service_freshness', null, _("No"), '0');
	$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_service_freshness', null, _("Default"), '2');
	$form->addGroup($nagTab, 'check_service_freshness', _("Service Freshness Checking Option"), '&nbsp;');
	if ($oreon->user->get_version() == 1)
		$form->addElement('text', 'freshness_check_interval', _("Freshness Check Interval"), $attrsText);
	else if ($oreon->user->get_version() == 2)	{
		$form->addElement('text', 'service_freshness_check_interval', _("Service Freshness Check Interval"), $attrsText);
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_host_freshness', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_host_freshness', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'check_host_freshness', null, _("Default"), '2');
		$form->addGroup($nagTab, 'check_host_freshness', _("Host Freshness Checking Option"), '&nbsp;');
		$form->addElement('text', 'host_freshness_check_interval', _("Host Freshness Check Interval"), $attrsText);
	}
	
	## Part 20
	$form->addElement('text', 'date_format', _("Date Format"), $attrsText);
	$form->addElement('text', 'illegal_object_name_chars', _("Illegal Object Name Characters"), $attrsText2);
	$form->addElement('text', 'illegal_macro_output_chars', _("Illegal Macro Output Characters"), $attrsText2);
	if ($oreon->user->get_version() == 2)	{
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_regexp_matching', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_regexp_matching', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_regexp_matching', null, _("Default"), '2');
		$form->addGroup($nagTab, 'use_regexp_matching', _("Regular Expression Matching Option"), '&nbsp;');
		$nagTab = array();
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_true_regexp_matching', null, _("Yes"), '1');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_true_regexp_matching', null, _("No"), '0');
		$nagTab[] = &HTML_QuickForm::createElement('radio', 'use_true_regexp_matching', null, _("Default"), '2');
		$form->addGroup($nagTab, 'use_true_regexp_matching', _("True Regular Expression Matching Option"), '&nbsp;');
	}
	
	## Part 21
	$form->addElement('text', 'admin_email', _("Administrator Email Address"), $attrsText);
	$form->addElement('text', 'admin_pager', _("Administrator Pager"), $attrsText);
		
	## Part 22
	$form->addElement('text', 'broker_module', _("Broker Module"), $attrsText2);
	$form->addElement('text', 'event_broker_options', _("Broker Module Options"), $attrsText2);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	
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
	$form->addRule('nagios_name', _("Compulsory Name"), 'required');
	$form->addRule('nagios_comment', _("Required Field"), 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('nagios_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>"._(" Required fields"));
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a nagios information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&nagios_id=".$nagios_id."'"));
	    $form->setDefaults($nagios);
		$form->freeze();
	}
	# Modify a nagios information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($nagios);
	}
	# Add a nagios information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
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
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&nagios_id=".$nagiosObj->getValue()."'"));
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
		$tpl->assign('sort1', "Files");		
		$tpl->assign('sort2', "Checks Options");		
		$tpl->assign('sort3', "Logs Options");
		$tpl->assign('sort4', "Data");		
		$tpl->assign('sort5', "Tunning");		
		$tpl->assign('sort6', "Admin");
		$tpl->assign('lang', $lang);
		$tpl->display("formNagios.ihtml");
	}
?>