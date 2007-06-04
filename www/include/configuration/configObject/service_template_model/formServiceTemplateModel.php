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
	
	function myDecodeSvTP($arg)	{
		$arg = str_replace('#BR#', "\\n", $arg);
		$arg = str_replace('#T#', "\\t", $arg);
		$arg = str_replace('#R#', "\\r", $arg);
		$arg = str_replace('#S#', "/", $arg);
		$arg = str_replace('#BS#', "\\", $arg);
		return html_entity_decode($arg, ENT_QUOTES);
	}
	
	$service = array();
	if (($o == "c" || $o == "w") && $service_id)	{		
		$DBRESULT =& $pearDB->query("SELECT * FROM service, extended_service_information esi WHERE service_id = '".$service_id."' AND esi.service_service_id = service_id LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$service = array_map("myDecodeSvTP", $DBRESULT->fetchRow());
		# Grab hostgroup || host
		$DBRESULT =& $pearDB->query("SELECT * FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		while ($DBRESULT->fetchInto($parent))	{
			if ($parent["host_host_id"])
				$service["service_hPars"][$parent["host_host_id"]] = $parent["host_host_id"];
			else if ($parent["hostgroup_hg_id"])
				$service["service_hgPars"][$parent["hostgroup_hg_id"]] = $parent["hostgroup_hg_id"];
		}
		# Set Service Notification Options
		$tmp = explode(',', $service["service_notification_options"]);
		foreach ($tmp as $key => $value)
			$service["service_notifOpts"][trim($value)] = 1;
		# Set Stalking Options
		$tmp = explode(',', $service["service_stalking_options"]);
		foreach ($tmp as $key => $value)
			$service["service_stalOpts"][trim($value)] = 1;
		$DBRESULT->free();
		# Set Contact Group
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_service_relation WHERE service_service_id = '".$service_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($notifCg); $i++)
			$service["service_cgs"][$i] = $notifCg["contactgroup_cg_id"];
		$DBRESULT->free();
		# Set Service Group Parents
		$DBRESULT =& $pearDB->query("SELECT DISTINCT servicegroup_sg_id FROM servicegroup_relation WHERE service_service_id = '".$service_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($sg); $i++)
			$service["service_sgs"][$i] = $sg["servicegroup_sg_id"];
		$DBRESULT->free();
		# Set Traps
		$DBRESULT =& $pearDB->query("SELECT DISTINCT traps_id FROM traps_service_relation WHERE service_id = '".$service_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($trap); $i++)
			$service["service_traps"][$i] = $trap["traps_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Host Templates comes from DB -> Store in $hosts Array
	$hosts = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '0' ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($host))
		$hosts[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();
	# Service Templates comes from DB -> Store in $svTpls Array
	$svTpls = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service WHERE service_register = '0' AND service_id != '".$service_id."' ORDER BY service_description");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($svTpl))	{
		if (!$svTpl["service_description"])
			$svTpl["service_description"] = getMyServiceName($svTpl["service_template_model_stm_id"])."'";
		$svTpls[$svTpl["service_id"]] = $svTpl["service_description"];
	}
	$DBRESULT->free();
	# Timeperiods comes from DB -> Store in $tps Array
	$tps = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($tp))
		$tps[$tp["tp_id"]] = $tp["tp_name"];
	$DBRESULT->free();
	# Check commands comes from DB -> Store in $checkCmds Array
	$checkCmds = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' ORDER BY command_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($checkCmd))
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();
	# Contact Groups comes from DB -> Store in $notifCcts Array
	$notifCgs = array();
	$DBRESULT =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($notifCg))
		$notifCgs[$notifCg["cg_id"]] = $notifCg["cg_name"];
	$DBRESULT->free();
	# Service Groups comes from DB -> Store in $hgs Array
	$sgs = array();
	$DBRESULT =& $pearDB->query("SELECT sg_id, sg_name FROM servicegroup ORDER BY sg_name");
	while($DBRESULT->fetchInto($sg))
		$sgs[$sg["sg_id"]] = $sg["sg_name"];
	$DBRESULT->free();
	# Graphs Template comes from DB -> Store in $graphTpls Array
	$graphTpls = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($graphTpl))
		$graphTpls[$graphTpl["graph_id"]] = $graphTpl["name"];
	$DBRESULT->free();
	# Traps definition comes from DB -> Store in $traps Array
	$traps = array();
	$DBRESULT =& $pearDB->query("SELECT traps_id, traps_name FROM traps ORDER BY traps_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($trap))
		$traps[$trap["traps_id"]] = $trap["traps_name"];
	$DBRESULT->free();
	# Deletion Policy definition comes from DB -> Store in $ppols Array
	$ppols = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT purge_policy_id, purge_policy_name FROM purge_policy ORDER BY purge_policy_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($ppol))
		$ppols[$ppol["purge_policy_id"]] = $ppol["purge_policy_name"];
	$DBRESULT->free();
	# IMG comes from DB -> Store in $extImg Array
	$extImg = array();
	$extImg = return_image_list(1);
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsText2		= array("size"=>"6");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["stm_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["stm_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["stm_view"]);
	else if ($o == "mc")
		$form->addElement('header', 'title', $lang["mchange"]);

	#
	## Service basic information
	#
	$form->addElement('header', 'information', $lang['sv_infos']);

	if ($o != "mc")
		$form->addElement('text', 'service_description', $lang["sv_description"], $attrsText);
	$form->addElement('text', 'service_alias', $lang["sv_alias"], $attrsText);
	$form->addElement('header', 'service_alias_interest', $lang["sv_alias_interest"], $attrsText);

	$form->addElement('select', 'service_template_model_stm_id', $lang['stm_template'], $svTpls);
	$form->addElement('static', 'tplText', $lang['stm_templateText']);

    $ams3 =& $form->addElement('advmultiselect', 'service_hPars', $lang['sv_hPars'], $hosts, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	#
	## Check information
	#
	$form->addElement('header', 'check', $lang['sv_head_state']);

	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, $lang["yes"], '1');
	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, $lang["no"], '0');
	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, $lang["nothing"], '2');
	$form->addGroup($serviceIV, 'service_is_volatile', $lang['sv_isVolatile'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_is_volatile' => '2'));

	$form->addElement('select', 'command_command_id', $lang['sv_checkCmd'], $checkCmds, 'onchange=setArgument(this.form,"command_command_id","example1")');
	$form->addElement('text', 'command_command_id_arg', $lang['sv_args'], $attrsText);
	$form->addElement('text', 'service_max_check_attempts', $lang['sv_checkMca'], $attrsText2);
	$form->addElement('text', 'service_normal_check_interval', $lang['sv_normalCheckInterval'], $attrsText2);
	$form->addElement('text', 'service_retry_check_interval', $lang['sv_retryCheckInterval'], $attrsText2);

	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, $lang["yes"], '1');
	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, $lang["no"], '0');
	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, $lang["nothing"], '2');
	$form->addGroup($serviceEHE, 'service_event_handler_enabled', $lang['sv_eventHandlerE'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_event_handler_enabled' => '2'));
	$form->addElement('select', 'command_command_id2', $lang['sv_eventHandler'], $checkCmds, 'onchange=setArgument(this.form,"command_command_id2","example2")');
	$form->addElement('text', 'command_command_id_arg2', $lang['sv_args'], $attrsText);

	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, $lang["yes"], '1');
	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, $lang["no"], '0');
	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, $lang["nothing"], '2');
	$form->addGroup($serviceACE, 'service_active_checks_enabled', $lang['sv_activeCE'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_active_checks_enabled' => '2'));

	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, $lang["yes"], '1');
	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, $lang["no"], '0');
	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, $lang["nothing"], '2');
	$form->addGroup($servicePCE, 'service_passive_checks_enabled', $lang['sv_passiveCE'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_passive_checks_enabled' => '2'));

	$form->addElement('select', 'timeperiod_tp_id', $lang['sv_checkPeriod'], $tps);

	##
	## Notification informations
	##
	$form->addElement('header', 'notification', $lang['sv_head_notif']);
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, $lang["yes"], '1');
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, $lang["no"], '0');
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, $lang["nothing"], '2');
	$form->addGroup($serviceNE, 'service_notifications_enabled', $lang['sv_notifEnabled'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_notifications_enabled' => '2'));
	
	if ($o == "mc")	{
		$mc_mod_cgs = array();
		$mc_mod_cgs[] = &HTML_QuickForm::createElement('radio', 'mc_mod_cgs', null, $lang['mc_mod_incremental'], '0');
		$mc_mod_cgs[] = &HTML_QuickForm::createElement('radio', 'mc_mod_cgs', null, $lang['mc_mod_replacement'], '1');
		$form->addGroup($mc_mod_cgs, 'mc_mod_cgs', $lang["mc_mod"], '&nbsp;');
		$form->setDefaults(array('mc_mod_cgs'=>'0'));
	}
    $ams3 =& $form->addElement('advmultiselect', 'service_cgs', $lang['sv_CgMembers'], $notifCgs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	$form->addElement('text', 'service_notification_interval', $lang['sv_notifInt'], $attrsText2);
	$form->addElement('select', 'timeperiod_tp_id2', $lang['sv_notifTp'], $tps);

 	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unknown');
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'Recovery');
	if ($oreon->user->get_version() == 2)
		$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', 'Flapping');
	$form->addGroup($serviceNotifOpt, 'service_notifOpts', $lang['sv_notifOpts'], '&nbsp;&nbsp;');

 	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', 'Ok');
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'unknown');
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	$form->addGroup($serviceStalOpt, 'service_stalOpts', $lang['sv_stalOpts'], '&nbsp;&nbsp;');

	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$serviceActivation[] = &HTML_QuickForm::createElement('radio', 'service_activate', null, $lang["enable"], '1');
	$serviceActivation[] = &HTML_QuickForm::createElement('radio', 'service_activate', null, $lang["disable"], '0');
	$form->addGroup($serviceActivation, 'service_activate', $lang["status"], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_activate' => '1'));
	$form->addElement('textarea', 'service_comment', $lang["cmt_comment"], $attrsTextarea);

	#
	## Sort 2 - Service relations
	#
	if ($o == "a")
		$form->addElement('header', 'title2', $lang["sv_Links_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title2', $lang["sv_Links_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title2', $lang["sv_Links_view"]);
	else if ($o == "mc")
		$form->addElement('header', 'title2', $lang["mchange"]);
		
	$form->addElement('header', 'links', $lang['sv_head_links']);

 	if ($o == "mc")	{
		$mc_mod_traps = array();
		$mc_mod_traps[] = &HTML_QuickForm::createElement('radio', 'mc_mod_traps', null, $lang['mc_mod_incremental'], '0');
		$mc_mod_traps[] = &HTML_QuickForm::createElement('radio', 'mc_mod_traps', null, $lang['mc_mod_replacement'], '1');
		$form->addGroup($mc_mod_traps, 'mc_mod_traps', $lang["mc_mod"], '&nbsp;');
		$form->setDefaults(array('mc_mod_traps'=>'0'));
	}
	$form->addElement('header', 'traps', $lang['gen_trapd']);
    $ams3 =& $form->addElement('advmultiselect', 'service_traps', $lang['sv_traps'], $traps, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	if ($o == "mc")	{
		$mc_mod_Pars = array();
		$mc_mod_Pars[] = &HTML_QuickForm::createElement('radio', 'mc_mod_Pars', null, $lang['mc_mod_incremental'], '0');
		$mc_mod_Pars[] = &HTML_QuickForm::createElement('radio', 'mc_mod_Pars', null, $lang['mc_mod_replacement'], '1');
		$form->addGroup($mc_mod_Pars, 'mc_mod_Pars', $lang["mc_mod"], '&nbsp;');
		$form->setDefaults(array('mc_mod_Pars'=>'0'));
	} 
	$ams3 =& $form->addElement('advmultiselect', 'service_hPars', $lang['sv_hPars'], $hosts, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	##
	## Sort 3 - Data treatment
	##
	
	if ($o == "a")
		$form->addElement('header', 'title3', $lang["h_add_treat"]);
	else if ($o == "c")
		$form->addElement('header', 'title3', $lang["h_modify_treat"]);
	else if ($o == "w")
		$form->addElement('header', 'title3', $lang["h_view_treat"]);
	else if ($o == "mc")
		$form->addElement('header', 'title2', $lang["mchange"]);
	
	$form->addElement('header', 'treatment', $lang['sv_head_treat']);

	$servicePC[] = &HTML_QuickForm::createElement('radio', 'service_parallelize_check', null, $lang["yes"], '1');
	$servicePC[] = &HTML_QuickForm::createElement('radio', 'service_parallelize_check', null, $lang["no"], '0');
	$servicePC[] = &HTML_QuickForm::createElement('radio', 'service_parallelize_check', null, $lang["nothing"], '2');
	$form->addGroup($servicePC, 'service_parallelize_check', $lang['sv_paraCheck'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_parallelize_check' => '2'));

	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, $lang["yes"], '1');
	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, $lang["no"], '0');
	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, $lang["nothing"], '2');
	$form->addGroup($serviceOOS, 'service_obsess_over_service', $lang['sv_ObsessOS'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_obsess_over_service' => '2'));

	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, $lang["yes"], '1');
	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, $lang["no"], '0');
	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, $lang["nothing"], '2');
	$form->addGroup($serviceCF, 'service_check_freshness', $lang['sv_checkFreshness'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_check_freshness' => '2'));

	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, $lang["yes"], '1');
	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, $lang["no"], '0');
	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, $lang["nothing"], '2');
	$form->addGroup($serviceFDE, 'service_flap_detection_enabled', $lang['sv_flapDetect'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_flap_detection_enabled' => '2'));

	$form->addElement('text', 'service_freshness_threshold', $lang['sv_FreshnessThreshold'], $attrsText2);
	$form->addElement('text', 'service_low_flap_threshold', $lang['sv_lowFT'], $attrsText2);
	$form->addElement('text', 'service_high_flap_threshold', $lang['sv_highFT'], $attrsText2);

	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, $lang["yes"], '1');
	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, $lang["no"], '0');
	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, $lang["nothing"], '2');
	$form->addGroup($servicePPD, 'service_process_perf_data', $lang['sv_processPD'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_process_perf_data' => '2'));

	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, $lang["yes"], '1');
	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, $lang["no"], '0');
	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, $lang["nothing"], '2');
	$form->addGroup($serviceRSI, 'service_retain_status_information', $lang['sv_retainSI'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_retain_status_information' => '2'));

	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, $lang["yes"], '1');
	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, $lang["no"], '0');
	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, $lang["nothing"], '2');
	$form->addGroup($serviceRNI, 'service_retain_nonstatus_information', $lang['sv_retainNI'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_retain_nonstatus_information' => '2'));

	#
	## Sort 4 - Extended Infos
	#
	if ($o == "a")
		$form->addElement('header', 'title4', $lang["sv_ExtInf_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title4', $lang["sv_ExtInf_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title4', $lang["sv_ExtInf_view"]);
	else if ($o == "mc")
		$form->addElement('header', 'title2', $lang["mchange"]);

	$form->addElement('header', 'nagios', $lang['h_nagios']);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'esi_notes', $lang['h_notes'], $attrsText);
	$form->addElement('text', 'esi_notes_url', $lang['h_notesUrl'], $attrsText);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'esi_action_url', $lang['h_actionUrl'], $attrsText);
	$form->addElement('select', 'esi_icon_image', $lang['h_iconImg'], $extImg, array("onChange"=>"showLogo('esi_icon_image',this.form.elements['esi_icon_image'].value)"));
	$form->addElement('text', 'esi_icon_image_alt', $lang['h_iconImgAlt'], $attrsText);

	$form->addElement('header', 'oreon', $lang['h_oreon']);
	$form->addElement('select', 'graph_id', $lang['sv_graphTpl'], $graphTpls);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'service_id');
	$reg =& $form->addElement('hidden', 'service_register');
	$reg->setValue("0");
	$service_register = 0;
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	if (is_array($select))	{
		$select_str = NULL;
		foreach ($select as $key => $value)
			$select_str .= $key.",";
		$select_pear =& $form->addElement('hidden', 'select');
		$select_pear->setValue($select_str);
	}

	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("service_description")));
	}
	function myReplaceAlias()	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("service_alias")));
	}
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('service_description', 'myReplace');
	$form->applyFilter('service_alias', 'myReplaceAlias');
	if ($o != "mc")	{
		$form->addRule('service_description', $lang['ErrName'], 'required');
		$form->addRule('service_alias', $lang['ErrName'], 'required');
		$form->registerRule('exist', 'callback', 'testServiceTemplateExistence');
		$form->addRule('service_description', $lang['ErrAlreadyExist'], 'exist');
	}
	$form->setRequiredNote($lang['requiredFields']);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path2, $tpl);

	# Just watch a host information
	if ($o == "w")	{
		if (!$min)
			$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&service_id=".$service_id."'"));
	    $form->setDefaults($service);
		$form->freeze();
	}
	# Modify a service information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($service);
	}
	# Add a service information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	# Massive Change
	else if ($o == "mc")	{
		$subMC =& $form->addElement('submit', 'submitMC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version(), "tpl"=>1));
	$tpl->assign("sort1", $lang['sv_conf']);
	$tpl->assign("sort2", $lang['sv_head_links']);
	$tpl->assign("sort3", $lang['sv_head_treat']);
	$tpl->assign("sort4", $lang['sv_extInf']);
	$tpl->assign('javascript', "<script type='text/javascript'>function showLogo(_img_dst, _value) {".
	"var _img = document.getElementById(_img_dst + '_img');".
	"_img.src = 'include/common/getHiddenImage.php?path=' + _value + '&logo=1' ; }</script>" );	
	$tpl->assign('time_unit', " * ".$oreon->Nagioscfg["interval_length"]." ".$lang["time_sec"]);

	$valid = false;
	if ($form->validate())	{
		$serviceObj =& $form->getElement('service_id');
		if ($form->getSubmitValue("submitA"))
			$serviceObj->setValue(insertServiceInDB());
		else if ($form->getSubmitValue("submitC"))
			updateServiceInDB($serviceObj->getValue());
		else if ($form->getSubmitValue("submitMC"))	{
			$select = explode(",", $select);
			foreach ($select as $key=>$value)
				if ($value)
					updateServiceInDB($value, true);
		}
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&service_id=".$serviceObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listServiceTemplateModel.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('is_not_template', $service_register);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->assign('v', $oreon->user->get_version());
		$tpl->display("formService.ihtml");
	}
?>