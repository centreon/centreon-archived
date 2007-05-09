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
	## Database retrieve information for Host
	#
	$host = array();
	if (($o == "c" || $o == "w") && $host_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM host, extended_host_information ehi WHERE host_id = '".$host_id."' AND ehi.host_host_id = host.host_id LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		if ($DBRESULT->numRows())	{
			$host = array_map("myDecode", $DBRESULT->fetchRow());
			# Set Host Notification Options
			$tmp = explode(',', $host["host_notification_options"]);
			foreach ($tmp as $key => $value)
				$host["host_notifOpts"][trim($value)] = 1;
			# Set Stalking Options
			$tmp = explode(',', $host["host_stalking_options"]);
			foreach ($tmp as $key => $value)
				$host["host_stalOpts"][trim($value)] = 1;
			$DBRESULT->free();
			# Set Contact Group
			$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_host_relation WHERE host_host_id = '".$host_id."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			for($i = 0; $DBRESULT->fetchInto($notifCg); $i++)
				$host["host_cgs"][$i] = $notifCg["contactgroup_cg_id"];
			$DBRESULT->free();
			# Set Host Parents
			$DBRESULT =& $pearDB->query("SELECT DISTINCT host_parent_hp_id FROM host_hostparent_relation WHERE host_host_id = '".$host_id."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			for($i = 0; $DBRESULT->fetchInto($parent); $i++)
				$host["host_parents"][$i] = $parent["host_parent_hp_id"];
			$DBRESULT->free();
			# Set Service Templates Childs
			$DBRESULT =& $pearDB->query("SELECT DISTINCT service_service_id FROM host_service_relation WHERE host_host_id = '".$host_id."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			for($i = 0; $DBRESULT->fetchInto($svTpl); $i++)
				$host["host_svTpls"][$i] = $svTpl["service_service_id"];
			$DBRESULT->free();
			# Set City name
			$DBRESULT =& $pearDB->query("SELECT DISTINCT cny.country_id, cty.city_name FROM view_city cty, view_country cny WHERE cty.city_id = '".$host["city_id"]."' AND cny.country_id = '".$host["country_id"]."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$city = $DBRESULT->fetchRow();
			$host["city_name"] = $city["city_name"];
			$host["country_id"] = $city["country_id"];
			$DBRESULT->free();
		}
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Host Templates comes from DB -> Store in $hTpls Array
	$hTpls = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_template_model_htm_id FROM host WHERE host_register = '0' AND host_id != '".$host_id."' ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($hTpl))	{
		if (!$hTpl["host_name"])
			$hTpl["host_name"] = getMyHostName($hTpl["host_template_model_htm_id"])."'";
		$hTpls[$hTpl["host_id"]] = $hTpl["host_name"];
	}
	$DBRESULT->free();
	# Service Templates comes from DB -> Store in $svTpls Array
	$svTpls = array();
	$DBRESULT =& $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service WHERE service_register = '0' ORDER BY service_description");
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
	# Countries comes from DB -> Store in $countries Array
	$countries = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT country_id, country_name FROM view_country ORDER BY country_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($country))
		$countries[$country["country_id"]] = $country["country_name"];
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
	$extImg = return_image_list();
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
		$form->addElement('header', 'title', $lang["htm_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["htm_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["htm_view"]);
	else if ($o == "mc")
		$form->addElement('header', 'title', $lang["mchange"]);

	## Sort 1 - Host Template Configuration
	#
	## Host basic information
	#
	$form->addElement('header', 'information', $lang['h_infos']);
	# No possibility to change name and alias, because there's no interest
	if ($o != "mc")	{
		$form->addElement('text', 'host_name', $lang["h_name"], $attrsText);
		$form->addElement('text', 'host_alias', $lang["h_alias"], $attrsText);
	}
	$form->addElement('text', 'host_address', $lang["h_address"], $attrsText);
	$form->addElement('select', 'host_snmp_version', $lang['h_snmpVer'], array(0=>null, 1=>"1", 2=>"2c", 3=>"3"));
	$form->addElement('text', 'host_snmp_community', $lang['h_snmpCom'], $attrsText);

	$form->addElement('select', 'host_template_model_htm_id', $lang['htm_template'], $hTpls);
	$form->addElement('static', 'tplText', $lang['htm_templateText']);

	#
	## Check information
	#
	$form->addElement('header', 'check', $lang['h_head_state']);
	#Nagios 1
	if ($oreon->user->get_version() == 1)	{
		$hostCE[] = &HTML_QuickForm::createElement('radio', 'host_checks_enabled', null, $lang["yes"], '1');
		$hostCE[] = &HTML_QuickForm::createElement('radio', 'host_checks_enabled', null, $lang["no"], '0');
		$hostCE[] = &HTML_QuickForm::createElement('radio', 'host_checks_enabled', null, $lang["nothing"], '2');
		$form->addGroup($hostCE, 'host_checks_enabled', $lang['h_checksEnabled'], '&nbsp;');
		if ($o != "mc")
			$form->setDefaults(array('host_checks_enabled' => '2'));
	}
	$form->addElement('select', 'command_command_id', $lang['h_checkCmd'], $checkCmds, 'onchange=setArgument(this.form,"command_command_id","example1")');
	$form->addElement('text', 'command_command_id_arg1', $lang['sv_args'], $attrsText);

	$form->addElement('text', 'host_max_check_attempts', $lang['h_checkMca'], $attrsText2);

	$hostEHE[] = &HTML_QuickForm::createElement('radio', 'host_event_handler_enabled', null, $lang["yes"], '1');
	$hostEHE[] = &HTML_QuickForm::createElement('radio', 'host_event_handler_enabled', null, $lang["no"], '0');
	$hostEHE[] = &HTML_QuickForm::createElement('radio', 'host_event_handler_enabled', null, $lang["nothing"], '2');
	$form->addGroup($hostEHE, 'host_event_handler_enabled', $lang['h_eventHandlerE'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('host_event_handler_enabled' => '2'));
	$form->addElement('select', 'command_command_id2', $lang['h_eventHandler'], $checkCmds, 'onchange=setArgument(this.form,"command_command_id2","example2")');
	$form->addElement('text', 'command_command_id_arg2', $lang['sv_args'], $attrsText);

	# Nagios 2
	if ($oreon->user->get_version() == 2)	{
		$form->addElement('text', 'host_check_interval', $lang['h_checkInterval'], $attrsText2);
	
		$hostACE[] = &HTML_QuickForm::createElement('radio', 'host_active_checks_enabled', null, $lang["yes"], '1');
		$hostACE[] = &HTML_QuickForm::createElement('radio', 'host_active_checks_enabled', null, $lang["no"], '0');
		$hostACE[] = &HTML_QuickForm::createElement('radio', 'host_active_checks_enabled', null, $lang["nothing"], '2');
		$form->addGroup($hostACE, 'host_active_checks_enabled', $lang['h_activeCE'], '&nbsp;');
		if ($o != "mc")
			$form->setDefaults(array('host_active_checks_enabled' => '2'));
	
		$hostPCE[] = &HTML_QuickForm::createElement('radio', 'host_passive_checks_enabled', null, $lang["yes"], '1');
		$hostPCE[] = &HTML_QuickForm::createElement('radio', 'host_passive_checks_enabled', null, $lang["no"], '0');
		$hostPCE[] = &HTML_QuickForm::createElement('radio', 'host_passive_checks_enabled', null, $lang["nothing"], '2');
		$form->addGroup($hostPCE, 'host_passive_checks_enabled', $lang['h_passiveCE'], '&nbsp;');
		if ($o != "mc")
			$form->setDefaults(array('host_passive_checks_enabled' => '2'));
	
		$form->addElement('select', 'timeperiod_tp_id', $lang['h_checkPeriod'], $tps);
	}

	##
	## Notification informations
	##
	$form->addElement('header', 'notification', $lang['h_head_notif']);
	$hostNE[] = &HTML_QuickForm::createElement('radio', 'host_notifications_enabled', null, $lang["yes"], '1');
	$hostNE[] = &HTML_QuickForm::createElement('radio', 'host_notifications_enabled', null, $lang["no"], '0');
	$hostNE[] = &HTML_QuickForm::createElement('radio', 'host_notifications_enabled', null, $lang["nothing"], '2');
	$form->addGroup($hostNE, 'host_notifications_enabled', $lang['h_notifEnabled'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('host_notifications_enabled' => '2'));
	#Nagios 2
	if ($oreon->user->get_version() == 2)	{
		if ($o == "mc")	{
			$mc_mod_hcg = array();
			$mc_mod_hcg[] = &HTML_QuickForm::createElement('radio', 'mc_mod_hcg', null, $lang['mc_mod_incremental'], '0');
			$mc_mod_hcg[] = &HTML_QuickForm::createElement('radio', 'mc_mod_hcg', null, $lang['mc_mod_replacement'], '1');
			$form->addGroup($mc_mod_hcg, 'mc_mod_hcg', $lang["mc_mod"], '&nbsp;');
			$form->setDefaults(array('mc_mod_hcg'=>'0'));
		}
	    $ams3 =& $form->addElement('advmultiselect', 'host_cgs', $lang['h_CgMembers'], $notifCgs, $attrsAdvSelect);
		$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
		$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
		$ams3->setElementTemplate($template);
		echo $ams3->getElementJs(false);
	}

	$form->addElement('text', 'host_notification_interval', $lang['h_notifInt'], $attrsText2);
	$form->addElement('select', 'timeperiod_tp_id2', $lang['h_notifTp'], $tps);

 	$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', 'Down');
	$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unreachable');
	$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'Recovery');
	if ($oreon->user->get_version() == 2)
		$hostNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', 'Flapping');
	$form->addGroup($hostNotifOpt, 'host_notifOpts', $lang['h_notifOpts'], '&nbsp;&nbsp;');

 	$hostStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', 'Ok/Up');
	$hostStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', 'Down');
	$hostStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unreachable');
	$form->addGroup($hostStalOpt, 'host_stalOpts', $lang['h_stalOpts'], '&nbsp;&nbsp;');

	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);	
	$hostActivation[] = &HTML_QuickForm::createElement('radio', 'host_activate', null, $lang["enable"], '1');
	$hostActivation[] = &HTML_QuickForm::createElement('radio', 'host_activate', null, $lang["disable"], '0');
	$form->addGroup($hostActivation, 'host_activate', $lang["status"], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('host_activate' => '1'));
	
	$form->addElement('textarea', 'host_comment', $lang["cmt_comment"], $attrsTextarea);

	#
	## Sort 2 - Host Relations
	#
	if ($o == "a")
		$form->addElement('header', 'title2', $lang["h_Links_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title2', $lang["h_Links_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title2', $lang["h_Links_view"]);
	else if ($o == "mc")
		$form->addElement('header', 'title2', $lang["mchange"]);

	$form->addElement('header', 'links', $lang['h_head_links']);
	if ($o == "mc")	{
		$mc_mod_htpl = array();
		$mc_mod_htpl[] = &HTML_QuickForm::createElement('radio', 'mc_mod_htpl', null, $lang['mc_mod_incremental'], '0');
		$mc_mod_htpl[] = &HTML_QuickForm::createElement('radio', 'mc_mod_htpl', null, $lang['mc_mod_replacement'], '1');
		$form->addGroup($mc_mod_htpl, 'mc_mod_htpl', $lang["mc_mod"], '&nbsp;');
		$form->setDefaults(array('mc_mod_htpl'=>'0'));
	}
    $ams3 =& $form->addElement('advmultiselect', 'host_svTpls', $lang['htm_childs'], $svTpls, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	#
	## Sort 3 - Data treatment
	#
	if ($o == "a")
		$form->addElement('header', 'title3', $lang["h_add_treat"]);
	else if ($o == "c")
		$form->addElement('header', 'title3', $lang["h_modify_treat"]);
	else if ($o == "w")
		$form->addElement('header', 'title3', $lang["h_view_treat"]);
	else if ($o == "mc")
		$form->addElement('header', 'title2', $lang["mchange"]);

	$form->addElement('header', 'treatment', $lang['h_head_treat']);
	# Nagios 2
	if ($oreon->user->get_version() == 2)	{
		$hostOOH[] = &HTML_QuickForm::createElement('radio', 'host_obsess_over_host', null, $lang["yes"], '1');
		$hostOOH[] = &HTML_QuickForm::createElement('radio', 'host_obsess_over_host', null, $lang["no"], '0');
		$hostOOH[] = &HTML_QuickForm::createElement('radio', 'host_obsess_over_host', null, $lang["nothing"], '2');
		$form->addGroup($hostOOH, 'host_obsess_over_host', $lang['h_ObsessOH'], '&nbsp;');
		if ($o != "mc")
			$form->setDefaults(array('host_obsess_over_host' => '2'));
	
		$hostCF[] = &HTML_QuickForm::createElement('radio', 'host_check_freshness', null, $lang["yes"], '1');
		$hostCF[] = &HTML_QuickForm::createElement('radio', 'host_check_freshness', null, $lang["no"], '0');
		$hostCF[] = &HTML_QuickForm::createElement('radio', 'host_check_freshness', null, $lang["nothing"], '2');
		$form->addGroup($hostCF, 'host_check_freshness', $lang['h_checkFreshness'], '&nbsp;');
		if ($o != "mc")
			$form->setDefaults(array('host_check_freshness' => '2'));
	}
	$hostFDE[] = &HTML_QuickForm::createElement('radio', 'host_flap_detection_enabled', null, $lang["yes"], '1');
	$hostFDE[] = &HTML_QuickForm::createElement('radio', 'host_flap_detection_enabled', null, $lang["no"], '0');
	$hostFDE[] = &HTML_QuickForm::createElement('radio', 'host_flap_detection_enabled', null, $lang["nothing"], '2');
	$form->addGroup($hostFDE, 'host_flap_detection_enabled', $lang['h_flapDetect'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('host_flap_detection_enabled' => '2'));
	# Nagios 2
	if ($oreon->user->get_version() == 2)	{
		$form->addElement('text', 'host_freshness_threshold', $lang['h_FreshnessThreshold'], $attrsText2);
	}
	$form->addElement('text', 'host_low_flap_threshold', $lang['h_lowFT'], $attrsText2);
	$form->addElement('text', 'host_high_flap_threshold', $lang['h_highFT'], $attrsText2);

	$hostPPD[] = &HTML_QuickForm::createElement('radio', 'host_process_perf_data', null, $lang["yes"], '1');
	$hostPPD[] = &HTML_QuickForm::createElement('radio', 'host_process_perf_data', null, $lang["no"], '0');
	$hostPPD[] = &HTML_QuickForm::createElement('radio', 'host_process_perf_data', null, $lang["nothing"], '2');
	$form->addGroup($hostPPD, 'host_process_perf_data', $lang['h_processPD'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('host_process_perf_data' => '2'));

	$hostRSI[] = &HTML_QuickForm::createElement('radio', 'host_retain_status_information', null, $lang["yes"], '1');
	$hostRSI[] = &HTML_QuickForm::createElement('radio', 'host_retain_status_information', null, $lang["no"], '0');
	$hostRSI[] = &HTML_QuickForm::createElement('radio', 'host_retain_status_information', null, $lang["nothing"], '2');
	$form->addGroup($hostRSI, 'host_retain_status_information', $lang['h_retainSI'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('host_retain_status_information' => '2'));

	$hostRNI[] = &HTML_QuickForm::createElement('radio', 'host_retain_nonstatus_information', null, $lang["yes"], '1');
	$hostRNI[] = &HTML_QuickForm::createElement('radio', 'host_retain_nonstatus_information', null, $lang["no"], '0');
	$hostRNI[] = &HTML_QuickForm::createElement('radio', 'host_retain_nonstatus_information', null, $lang["nothing"], '2');
	$form->addGroup($hostRNI, 'host_retain_nonstatus_information', $lang['h_retainNI'], '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('host_retain_nonstatus_information' => '2'));

	if ($oreon->optGen["perfparse_installed"])	{
		$form->addElement('header', 'purge_policy', $lang["mod_purgePolicy"]);
		$form->addElement('select', 'purge_policy_id', $lang["mod_purgePolicy_name"], $ppols);
	}
	
	#
	## Sort 4 - Extended Infos
	#
	if ($o == "a")
		$form->addElement('header', 'title4', $lang["h_ExtInf_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title4', $lang["h_ExtInf_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title4', $lang["h_ExtInf_view"]);

	$form->addElement('header', 'nagios', $lang['h_nagios']);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'ehi_notes', $lang['h_notes'], $attrsText);
	$form->addElement('text', 'ehi_notes_url', $lang['h_notesUrl'], $attrsText);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'ehi_action_url', $lang['h_actionUrl'], $attrsText);
	$form->addElement('select', 'ehi_icon_image', $lang['h_iconImg'], $extImg, array("onChange"=>"showLogo('ehi_icon_image',this.form.elements['ehi_icon_image'].value)"));
	$form->addElement('text', 'ehi_icon_image_alt', $lang['h_iconImgAlt'], $attrsText);
	$form->addElement('text', 'ehi_vrml_image', $lang['h_vrmlImg'], $attrsText);
	$form->addElement('select', 'ehi_statusmap_image', $lang['h_nagStatImg'],$extImg, array("onChange"=>"showLogo('ehi_statusmap_image',this.form.elements['ehi_statusmap_image'].value)"));	
	$form->addElement('text', 'ehi_2d_coords', $lang['h_nag2dCoords'], $attrsText2);
	$form->addElement('text', 'ehi_3d_coords', $lang['h_nag3dCoords'], $attrsText2);

	$form->addElement('header', 'oreon', $lang['h_oreon']);
	$form->addElement('select', 'country_id', $lang['h_country'], $countries);
	$form->addElement('text', 'city_name', $lang['h_city'], array("id"=>"city_name", "size"=>"35", "autocomplete"=>"off"));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'host_id');
	$reg =& $form->addElement('hidden', 'host_register');
	$reg->setValue("0");
	$host_register = 0;
	$assoc =& $form->addElement('hidden', 'dupSvTplAssoc');
	$assoc->setValue("0");
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
		return (str_replace(" ", "_", $form->getSubmitValue("host_name")));
	}
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('host_name', 'myReplace');
	$form->registerRule('exist', 'callback', 'testHostExistence');
	$form->addRule('host_name', $lang['ErrAlreadyExist'], 'exist');
	$form->addRule('host_name', $lang['ErrName'], 'required');
	$form->addRule('host_alias', $lang['ErrAlias'], 'required');

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path2, $tpl);

	# Just watch a host information
	if ($o == "w")	{
		if (!$min)
			$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&host_id=".$host_id."'"));
	    $form->setDefaults($host);
		$form->freeze();
	}
	# Modify a host information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($host);
	}
	# Add a host information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	# Massive Change
	else if ($o == "mc")	{
		$subMC =& $form->addElement('submit', 'submitMC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version(), "tpl"=>1, "min"=>$min, "perfparse"=>$oreon->optGen["perfparse_installed"]));
	$tpl->assign('min', $min);
	$tpl->assign("sort1", $lang['h_conf']);
	$tpl->assign("sort2", $lang['h_head_links']);
	$tpl->assign("sort3", $lang['h_head_treat']);
	$tpl->assign("sort4", $lang['h_extInf']);	
	$tpl->assign("initJS", "<script type='text/javascript'>
							window.onload = function () {
							init();
							initAutoComplete('Form','city_name','sub');
							};</script>");

	$tpl->assign('javascript', "<script type='text/javascript'>function showLogo(_img_dst, _value) {".
	"var _img = document.getElementById(_img_dst + '_img');".
	"_img.src = 'include/common/getHiddenImage.php?path=' + _value + '&logo=1' ; }</script>" );
	$tpl->assign('time_unit', " * ".$oreon->Nagioscfg["interval_length"]." ".$lang["time_sec"]);

	$valid = false;
	if ($form->validate())	{
		$hostObj =& $form->getElement('host_id');
		if ($form->getSubmitValue("submitA"))
			$hostObj->setValue(insertHostInDB());
		else if ($form->getSubmitValue("submitC"))
			updateHostInDB($hostObj->getValue());
		else if ($form->getSubmitValue("submitMC"))	{
			$select = explode(",", $select);
			foreach ($select as $key=>$value)
				if ($value)
					updateHostInDB($value, true);
		}
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&host_id=".$hostObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listHostTemplateModel.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formHost.ihtml");
	}
?>