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
	/*
	 * Database retrieve information for Host
	 */

	$host = array();
	if (($o == "c" || $o == "w") && $host_id)	{
		if ($oreon->user->admin || !HadUserLca($pearDB))
			$rq = "SELECT * FROM host, extended_host_information ehi WHERE host_id = '".$host_id."' AND ehi.host_host_id = host.host_id LIMIT 1";
		else
			$rq = "SELECT * FROM host, extended_host_information ehi WHERE host_id = '".$host_id."' AND ehi.host_host_id = host.host_id AND host_id IN (".$lcaHoststr.") LIMIT 1";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";

		/*
		 * Set base value
		 */
		$host = array_map("myDecode", $DBRESULT->fetchRow());
		
		/*
		 * Set Host Notification Options
		 */
		$tmp = explode(',', $host["host_notification_options"]);
		foreach ($tmp as $key => $value)
			$host["host_notifOpts"][trim($value)] = 1;
		
		/*
		 * Set Stalking Options
		 */
		$tmp = explode(',', $host["host_stalking_options"]);
		foreach ($tmp as $key => $value)
			$host["host_stalOpts"][trim($value)] = 1;
		$DBRESULT->free();
		
		/*
		 * Set Contact Group
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_host_relation WHERE host_host_id = '".$host_id."'");
		for($i = 0; $DBRESULT->fetchInto($notifCg); $i++)
			$host["host_cgs"][$i] = $notifCg["contactgroup_cg_id"];
		$DBRESULT->free();
		
		/*
		 * Set Host Parents
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_parent_hp_id FROM host_hostparent_relation WHERE host_host_id = '".$host_id."'");
		for($i = 0; $DBRESULT->fetchInto($parent); $i++)
			$host["host_parents"][$i] = $parent["host_parent_hp_id"];
		$DBRESULT->free();
		
		/*
		 * Set Host Childs
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id FROM host_hostparent_relation WHERE host_parent_hp_id = '".$host_id."'");
		for($i = 0; $DBRESULT->fetchInto($child); $i++)
			$host["host_childs"][$i] = $child["host_host_id"];
		$DBRESULT->free();
		
		/*
		 * Set Host Group Parents
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '".$host_id."'");
		for($i = 0; $DBRESULT->fetchInto($hg); $i++)
			$host["host_hgs"][$i] = $hg["hostgroup_hg_id"];
		$DBRESULT->free();
		
		/*
		 * Set Host and Nagios Server Relation
		 */
		$DBRESULT =& $pearDB->query("SELECT `nagios_server_id` FROM `ns_host_relation` WHERE `host_host_id` = '".$host_id."'");
		for ($i = 0; $ns = $DBRESULT->fetchRow(); $i++)
			$host["nagios_server_id"][$i] = $ns["nagios_server_id"];
		$DBRESULT->free();
		unset($ns);
	}

	/*
	 * Database retrieve information for differents elements list we need on the page
	 * Host Templates comes from DB -> Store in $hTpls Array
	 */

	$hTpls = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_template_model_htm_id FROM host WHERE host_register = '0' AND host_id != '".$host_id."' ORDER BY host_name");
	while($DBRESULT->fetchInto($hTpl))	{
		if (!$hTpl["host_name"])
			$hTpl["host_name"] = getMyHostName($hTpl["host_template_model_htm_id"])."'";
		$hTpls[$hTpl["host_id"]] = $hTpl["host_name"];
	}
	$DBRESULT->free();
	
	/*
	 * Timeperiods comes from DB -> Store in $tps Array
	 */
	$tps = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
	while($DBRESULT->fetchInto($tp))
		$tps[$tp["tp_id"]] = $tp["tp_name"];
	$DBRESULT->free();
	
	/*
	 * Check commands comes from DB -> Store in $checkCmds Array
	 */
	$checkCmds = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' ORDER BY command_name");
	while($DBRESULT->fetchInto($checkCmd))
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();
	
	/*
	 * Check commands comes from DB -> Store in $checkCmds Array
	 */
	$checkCmdEvent = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' OR command_type = '3' ORDER BY command_name");
	while($DBRESULT->fetchInto($checkCmd))
		$checkCmdEvent[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();
	
	/*
	 * Contact Groups comes from DB -> Store in $notifCcts Array
	 */
	$notifCgs = array();
	$DBRESULT =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
	while($DBRESULT->fetchInto($notifCg))
		$notifCgs[$notifCg["cg_id"]] = $notifCg["cg_name"];
	$DBRESULT->free();
	
	/*
	 * Contact Nagios Server comes from DB -> Store in $nsServer Array
	 */
	$nsServers = array();
	$DBRESULT =& $pearDB->query("SELECT id, name FROM nagios_server ORDER BY name");
	while ($nsServer = $DBRESULT->fetchRow())
		$nsServers[$nsServer["id"]] = $nsServer["name"];
	$DBRESULT->free();
	
	/*
	 * Host Groups comes from DB -> Store in $hgs Array
	 */
	$hgs = array();
	if ($oreon->user->admin || !HadUserLca($pearDB))		
		$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name");
	else
		$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup WHERE hg_id IN (".$lcaHostGroupstr.") ORDER BY hg_name");

	while($DBRESULT->fetchInto($hg))
		$hgs[$hg["hg_id"]] = $hg["hg_name"];
	$DBRESULT->free();
	
	/*
	 * Host Parents comes from DB -> Store in $hostPs Array
	 */
	$hostPs = array();
	if ($oreon->user->admin || !HadUserLca($pearDB))
		$DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_template_model_htm_id FROM host WHERE host_id != '".$host_id."' AND host_register = '1' ORDER BY host_name");
	else
		$DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_template_model_htm_id FROM host WHERE host_id != '".$host_id."' AND host_id IN (".$lcaHoststr.") AND host_register = '1' ORDER BY host_name");
	while($DBRESULT->fetchInto($hostP))	{
		if (!$hostP["host_name"])
			$hostP["host_name"] = getMyHostName($hostP["host_template_model_htm_id"])."'";
		$hostPs[$hostP["host_id"]] = $hostP["host_name"];
	}
	$DBRESULT->free();
	
	/*
	 * Deletion Policy definition comes from DB -> Store in $ppols Array
	 */
	$ppols = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT purge_policy_id, purge_policy_name FROM purge_policy ORDER BY purge_policy_name");
	while($DBRESULT->fetchInto($ppol))
		$ppols[$ppol["purge_policy_id"]] = $ppol["purge_policy_name"];
	$DBRESULT->free();	
	
	/*
	 * IMG comes from DB -> Store in $extImg Array
	 */
	$extImg = array();
	$extImg = return_image_list(1);
	$extImgStatusmap = array();
	$extImgStatusmap = return_image_list(2);
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsText2		= array("size"=>"6");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"4", "cols"=>"80");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	
	$TemplateValues = array();
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["h_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["h_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["h_view"]);
	else if ($o == "mc")
		$form->addElement('header', 'title', $lang["mchange"]);

	## Sort 1 - Host Configuration
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
	$form->addElement('static', 'tplText', $lang['h_templateText']);
	$dupSvTpl[] = &HTML_QuickForm::createElement('radio', 'dupSvTplAssoc', null, $lang["yes"], '1');
	$dupSvTpl[] = &HTML_QuickForm::createElement('radio', 'dupSvTplAssoc', null, $lang["no"], '0');
	$form->addGroup($dupSvTpl, 'dupSvTplAssoc', $lang['h_checksEnabled'], '&nbsp;');
	if ($o == "c")
		$form->setDefaults(array('dupSvTplAssoc' => '0'));
	else if ($o == "w")
		;
	else if ($o != "mc")
		$form->setDefaults(array('dupSvTplAssoc' => '1'));
	$form->addElement('static', 'dupSvTplAssocText', $lang['h_dupSvTplAssocText']);

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
	$form->addElement('select', 'command_command_id2', $lang['h_eventHandler'], $checkCmdEvent, 'onchange=setArgument(this.form,"command_command_id2","example2")');
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
		$mc_mod_hpar = array();
		$mc_mod_hpar[] = &HTML_QuickForm::createElement('radio', 'mc_mod_hpar', null, $lang['mc_mod_incremental'], '0');
		$mc_mod_hpar[] = &HTML_QuickForm::createElement('radio', 'mc_mod_hpar', null, $lang['mc_mod_replacement'], '1');
		$form->addGroup($mc_mod_hpar, 'mc_mod_hpar', $lang["mc_mod"], '&nbsp;');
		$form->setDefaults(array('mc_mod_hpar'=>'0'));
	}
    $ams3 =& $form->addElement('advmultiselect', 'host_parents', $lang['h_HostParents'], $hostPs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	if ($o == "mc")	{
		$mc_mod_hch = array();
		$mc_mod_hch[] = &HTML_QuickForm::createElement('radio', 'mc_mod_hch', null, $lang['mc_mod_incremental'], '0');
		$mc_mod_hch[] = &HTML_QuickForm::createElement('radio', 'mc_mod_hch', null, $lang['mc_mod_replacement'], '1');
		$form->addGroup($mc_mod_hch, 'mc_mod_hch', $lang["mc_mod"], '&nbsp;');
		$form->setDefaults(array('mc_mod_hch'=>'0'));
	}
    $ams3 =& $form->addElement('advmultiselect', 'host_childs', $lang['h_HostChilds'], $hostPs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	if ($o == "mc")	{
		$mc_mod_hhg = array();
		$mc_mod_hhg[] = &HTML_QuickForm::createElement('radio', 'mc_mod_hhg', null, $lang['mc_mod_incremental'], '0');
		$mc_mod_hhg[] = &HTML_QuickForm::createElement('radio', 'mc_mod_hhg', null, $lang['mc_mod_replacement'], '1');
		$form->addGroup($mc_mod_hhg, 'mc_mod_hhg', $lang["mc_mod"], '&nbsp;');
		$form->setDefaults(array('mc_mod_hhg'=>'0'));
	}
    $ams3 =& $form->addElement('advmultiselect', 'host_hgs', $lang['h_HostGroupMembers'], $hgs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	if ($o == "mc")	{
		$mc_mod_hhg = array();
		$mc_mod_hhg[] = &HTML_QuickForm::createElement('radio', 'mc_mod_nsid', null, $lang['mc_mod_incremental'], '0');
		$mc_mod_hhg[] = &HTML_QuickForm::createElement('radio', 'mc_mod_nsid', null, $lang['mc_mod_replacement'], '1');
		$form->addGroup($mc_mod_hhg, 'mc_mod_nsid', $lang["mc_mod"], '&nbsp;');
		$form->setDefaults(array('mc_mod_nsid'=>'0'));
	}
    $ams3 =& $form->addElement('advmultiselect', 'nagios_server_id', $lang['h_NagiosServer'], $nsServers, $attrsAdvSelect);
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
		$form->addElement('header', 'title3', $lang["mchange"]);

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
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'host_freshness_threshold', $lang['h_FreshnessThreshold'], $attrsText2);

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

	/*	
	if ($oreon->optGen["perfparse_installed"])	{
		$form->addElement('header', 'purge_policy', $lang["mod_purgePolicy"]);
		$form->addElement('select', 'purge_policy_id', $lang["mod_purgePolicy_name"], $ppols);
	}
	*/
	#
	## Sort 4 - Extended Infos
	#
	if ($o == "a")
		$form->addElement('header', 'title4', $lang["h_ExtInf_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title4', $lang["h_ExtInf_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title4', $lang["h_ExtInf_view"]);
	else if ($o == "mc")
		$form->addElement('header', 'title4', $lang["mchange"]);

	$form->addElement('header', 'nagios', $lang['h_nagios']);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'ehi_notes', $lang['h_notes'], $attrsText);
	$form->addElement('text', 'ehi_notes_url', $lang['h_notesUrl'], $attrsText);
	if ($oreon->user->get_version() == 2)
		$form->addElement('text', 'ehi_action_url', $lang['h_actionUrl'], $attrsText);
//	$form->addElement('text', 'ehi_icon_image', $lang['h_iconImg'], $attrsText);
	$form->addElement('select', 'ehi_icon_image', $lang['h_iconImg'], $extImg, array("onChange"=>"showLogo('ehi_icon_image',this.form.elements['ehi_icon_image'].value)"));
	$form->addElement('text', 'ehi_icon_image_alt', $lang['h_iconImgAlt'], $attrsText);
	$form->addElement('select', 'ehi_vrml_image', $lang['h_vrmlImg'], $extImg, array("onChange"=>"showLogo('ehi_vrml_image',this.form.elements['ehi_vrml_image'].value)"));
//	$form->addElement('text', 'ehi_statusmap_image', $lang['h_nagStatImg'], $attrsText);
	$form->addElement('select', 'ehi_statusmap_image', $lang['h_nagStatImg'], $extImgStatusmap, array("onChange"=>"showLogo('ehi_statusmap_image',this.form.elements['ehi_statusmap_image'].value)"));	
	$form->addElement('text', 'ehi_2d_coords', $lang['h_nag2dCoords'], $attrsText2);
	$form->addElement('text', 'ehi_3d_coords', $lang['h_nag3dCoords'], $attrsText2);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'host_id');
	$reg =& $form->addElement('hidden', 'host_register');
	$reg->setValue("1");
	$host_register = 1;
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
	$form->applyFilter('__ALL__', 'myTrim');
	$from_list_menu = false;
	if ($o != "mc")	{
		$form->applyFilter('host_name', 'myReplace');
		$form->addRule('host_name', $lang['ErrName'], 'required');
		$form->registerRule('exist', 'callback', 'testHostExistence');
		$form->addRule('host_name', $lang['ErrAlreadyExist'], 'exist');
		# If we are using a Template, no need to check the value, we hope there are in the Template
		if (!$form->getSubmitValue("host_template_model_htm_id"))	{
			$form->addRule('host_alias', $lang['ErrAlias'], 'required');
			$form->addRule('host_address', $lang['ErrAddress'], 'required');
			$form->addRule('host_max_check_attempts', $lang['ErrRequired'], 'required');
			if ($oreon->user->get_version() == 2)	{
				$form->addRule('timeperiod_tp_id', $lang['ErrTp'], 'required');
				$form->addRule('host_cgs', $lang['ErrCg'], 'required');
			}
			$form->addRule('host_notification_interval', $lang['ErrRequired'], 'required');
			$form->addRule('timeperiod_tp_id2', $lang['ErrTp'], 'required');
			$form->addRule('host_notifOpts', $lang['ErrOpt'], 'required');
	
		}
	}
	else if ($o == "mc")	{
		if ($form->getSubmitValue("submitMC"))
			$from_list_menu = false;
		else
			$from_list_menu = true;
	}
	
	$form->setRequiredNote($lang['requiredFields']);
	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

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
	
	$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version(), "tpl"=>0/*, "perfparse"=>$oreon->optGen["perfparse_installed"]*/));
	$tpl->assign('min', $min);
	$tpl->assign("sort1", $lang['h_conf']);
	$tpl->assign("sort2", $lang['h_head_links']);
	$tpl->assign("sort3", $lang['h_head_treat']);
	$tpl->assign("sort4", $lang['h_extInf']);
	$tpl->assign('javascript', "<script type='text/javascript'>function showLogo(_img_dst, _value) {".
	"var _img = document.getElementById(_img_dst + '_img');".
	"_img.src = 'include/common/getHiddenImage.php?path=' + _value + '&logo=1' ; }</script>" );
	

	$tpl->assign('time_unit', " * ".$oreon->Nagioscfg["interval_length"]." ".$lang["time_sec"]);

	$valid = false;
	if ($form->validate() && $from_list_menu == false)	{
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
		require_once($path."listHost.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('is_not_template', $host_register);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->assign('p', $p);
		$tpl->assign('lang', $lang);
		$tpl->display("formHost.ihtml");
	}
?>