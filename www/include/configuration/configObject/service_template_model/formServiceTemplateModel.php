<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
 	if (!isset($oreon))
 		exit();
 	
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
		# Set base value
		$service_list =& $DBRESULT->fetchRow();
		$service = array_map("myDecodeSvTP", $service_list);
		
		/*
		 * Grab hostgroup || host
		 */
		$DBRESULT =& $pearDB->query("SELECT * FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		while ($parent =& $DBRESULT->fetchRow())	{
			if ($parent["host_host_id"])
				$service["service_hPars"][$parent["host_host_id"]] = $parent["host_host_id"];
			else if ($parent["hostgroup_hg_id"])
				$service["service_hgPars"][$parent["hostgroup_hg_id"]] = $parent["hostgroup_hg_id"];
		}
		# Set Service Notification Options
		$tmp = explode(',', $service["service_notification_options"]);
		foreach ($tmp as $key => $value)
			$service["service_notifOpts"][trim($value)] = 1;
		
		/*
		 * Set Stalking Options
		 */
		$tmp = explode(',', $service["service_stalking_options"]);
		foreach ($tmp as $key => $value)
			$service["service_stalOpts"][trim($value)] = 1;
		$DBRESULT->free();
		
		/*
		 * Set Contact Group
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_service_relation WHERE service_service_id = '".$service_id."'");
		for ($i = 0; $notifCg = $DBRESULT->fetchRow(); $i++)
			$service["service_cgs"][$i] = $notifCg["contactgroup_cg_id"];
		$DBRESULT->free();
		
		/*
		 * Set Contact Group
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contact_id FROM contact_service_relation WHERE service_service_id = '".$service_id."'");
		for ($i = 0; $notifC = $DBRESULT->fetchRow(); $i++)
			$service["service_cs"][$i] = $notifC["contact_id"];
		$DBRESULT->free();
		
		/*
		 * Set Service Group Parents
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT servicegroup_sg_id FROM servicegroup_relation WHERE service_service_id = '".$service_id."'");
		for ($i = 0; $sg = $DBRESULT->fetchRow(); $i++)
			$service["service_sgs"][$i] = $sg["servicegroup_sg_id"];
		$DBRESULT->free();
		
		/*
		 * Set Traps
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT traps_id FROM traps_service_relation WHERE service_id = '".$service_id."'");
		for ($i = 0; $trap = $DBRESULT->fetchRow(); $i++)
			$service["service_traps"][$i] = $trap["traps_id"];
		$DBRESULT->free();
		
		/*
		 * Set Categories
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT sc_id FROM service_categories_relation WHERE service_service_id = '".$service_id."'");
		for ($i = 0; $service_category = $DBRESULT->fetchRow(); $i++)
			$service["service_categories"][$i] = $service_category["sc_id"];
		$DBRESULT->free();
	}
	/*
	 * 	Database retrieve information for differents elements list we need on the page
	 */
	
	/*
	 * Host Templates comes from DB -> Store in $hosts Array
	 */
	$hosts = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '0' ORDER BY host_name");
	while ($host = $DBRESULT->fetchRow()){
		$hosts[$host["host_id"]] = $host["host_name"];
	}
	$DBRESULT->free();
	
	/*
	 * Get all Templates who use himself
	 */
	$svc_tmplt_who_use_me = array(); 
	if (isset($_GET["service_id"]) && $_GET["service_id"]){ 
		$DBRESULT =& $pearDB->query("SELECT service_description, service_id FROM service WHERE service_template_model_stm_id = '".$_GET["service_id"]."'");
		while ($service_tmpl_father = $DBRESULT->fetchRow())
			$svc_tmplt_who_use_me[$service_tmpl_father["service_id"]] = $service_tmpl_father["service_description"];
		$DBRESULT->free();
	}	
	
	/*
	 * Service Templates comes from DB -> Store in $svTpls Array
	 */
	$svTpls = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service WHERE service_register = '0' AND service_id != '".$service_id."' ORDER BY service_description");
	while ($svTpl = $DBRESULT->fetchRow())	{
		if (!$svTpl["service_description"])
			$svTpl["service_description"] = getMyServiceName($svTpl["service_template_model_stm_id"])."'";
		else	{
			$svTpl["service_description"] = str_replace('#S#', "/", $svTpl["service_description"]);
			$svTpl["service_description"] = str_replace('#BS#', "\\", $svTpl["service_description"]);
		}
		if (!isset($svc_tmplt_who_use_me[$svTpl["service_id"]]) || !$svc_tmplt_who_use_me[$svTpl["service_id"]])
			$svTpls[$svTpl["service_id"]] = $svTpl["service_description"];
	}
	$DBRESULT->free();
	# Timeperiods comes from DB -> Store in $tps Array
	$tps = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
	while ($tp = $DBRESULT->fetchRow())
		$tps[$tp["tp_id"]] = $tp["tp_name"];
	$DBRESULT->free();
	# Check commands comes from DB -> Store in $checkCmds Array
	$checkCmds = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' ORDER BY command_name");
	while ($checkCmd = $DBRESULT->fetchRow())
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();
	# Contact Groups comes from DB -> Store in $notifCcts Array
	$notifCgs = array();
	$DBRESULT =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
	while ($notifCg = $DBRESULT->fetchRow())
		$notifCgs[$notifCg["cg_id"]] = $notifCg["cg_name"];
	$DBRESULT->free();
	
	# Contact comes from DB -> Store in $notifCcts Array
	$notifCs = array();
	$DBRESULT =& $pearDB->query("SELECT contact_id, contact_name FROM contact ORDER BY contact_name");
	while ($notifC = $DBRESULT->fetchRow())
		$notifCs[$notifC["contact_id"]] = $notifC["contact_name"];
	$DBRESULT->free();
	
	# Service Groups comes from DB -> Store in $hgs Array
	$sgs = array();
	$DBRESULT =& $pearDB->query("SELECT sg_id, sg_name FROM servicegroup ORDER BY sg_name");
	while ($sg = $DBRESULT->fetchRow())
		$sgs[$sg["sg_id"]] = $sg["sg_name"];
	$DBRESULT->free();
	# Graphs Template comes from DB -> Store in $graphTpls Array
	$graphTpls = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	while ($graphTpl = $DBRESULT->fetchRow())
		$graphTpls[$graphTpl["graph_id"]] = $graphTpl["name"];
	$DBRESULT->free();
	# Traps definition comes from DB -> Store in $traps Array
	$traps = array();
	$DBRESULT =& $pearDB->query("SELECT traps_id, traps_name FROM traps ORDER BY traps_name");
	while ($trap = $DBRESULT->fetchRow())
		$traps[$trap["traps_id"]] = $trap["traps_name"];
	$DBRESULT->free();
		
	# service categories comes from DB -> Store in $service_categories Array
	$service_categories = array();
	$DBRESULT =& $pearDB->query("SELECT sc_name, sc_id FROM service_categories ORDER BY sc_name");
	while ($service_categorie =& $DBRESULT->fetchRow())
		$service_categories[$service_categorie["sc_id"]] = $service_categorie["sc_name"];
	$DBRESULT->free();
	
	/*
	 *  Service on demand macro stored in DB
	 */
	$j = 0;		
	$DBRESULT =& $pearDB->query("SELECT svc_macro_id, svc_macro_name, svc_macro_value, svc_svc_id FROM on_demand_macro_service WHERE svc_svc_id = '". $service_id ."' ORDER BY `svc_macro_id`");
	while ($od_macro = $DBRESULT->fetchRow())
	{
		$od_macro_id[$j] = $od_macro["svc_macro_id"];
		$od_macro_name[$j] = str_replace("\$_SERVICE", "", $od_macro["svc_macro_name"]);
		$od_macro_name[$j] = str_replace("\$", "", $od_macro_name[$j]);		
		$od_macro_value[$j] = $od_macro["svc_macro_value"];
		$od_macro_svc_id[$j] = $od_macro["svc_svc_id"];
		$j++;		
	}
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
	$attrsTextLong 	= array("size"=>"60");
	$attrsAdvSelect_small = array("style" => "width: 200px; height: 70px;");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsAdvSelect_big = array("style" => "width: 200px; height: 200px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Service Template Model"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Service Template Model"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Service Template Model"));
	else if ($o == "mc")
		$form->addElement('header', 'title', _("Massive Change"));

	#
	## Service basic information
	#
	$form->addElement('header', 'information', _("General Information"));

	if ($o != "mc")
		$form->addElement('text', 'service_description', _("Service Template Name"), $attrsText);
	$form->addElement('text', 'service_alias', _("Alias"), $attrsText);
	$form->addElement('header', 'service_alias_interest', _("Name Used for Service in auto-deploy by template"), $attrsText);

	$form->addElement('select', 'service_template_model_stm_id', _("Template Service Model"), $svTpls);
	$form->addElement('static', 'tplText', _("Using a Template Model allows you to have multi-level Template connections"));

    $ams3 =& $form->addElement('advmultiselect', 'service_hPars', _("Linked to host templates "), $hosts, $attrsAdvSelect_big);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	#
	## Check information
	#
	$form->addElement('header', 'check', _("Service State"));

	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("Yes"), '1');
	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("No"), '0');
	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("Default"), '2');
	$form->addGroup($serviceIV, 'service_is_volatile', _("Is volatile"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_is_volatile' => '2'));

	$form->addElement('select', 'command_command_id', _("Check Command"), $checkCmds, 'onchange=setArgument(this.form,"command_command_id","example1")');
	$form->addElement('text', 'command_command_id_arg', _("Args"), $attrsTextLong);
	$form->addElement('text', 'service_max_check_attempts', _("Max Check Attempts"), $attrsText2);
	$form->addElement('text', 'service_normal_check_interval', _("Normal Check Interval"), $attrsText2);
	$form->addElement('text', 'service_retry_check_interval', _("Retry Check Interval"), $attrsText2);

	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("Yes"), '1');
	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("No"), '0');
	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("Default"), '2');
	$form->addGroup($serviceEHE, 'service_event_handler_enabled', _("Event Handler Enabled"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_event_handler_enabled' => '2'));
	$form->addElement('select', 'command_command_id2', _("Event Handler"), $checkCmds, 'onchange=setArgument(this.form,"command_command_id2","example2")');
	$form->addElement('text', 'command_command_id_arg2', _("Args"), $attrsTextLong);

	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("Yes"), '1');
	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("No"), '0');
	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("Default"), '2');
	$form->addGroup($serviceACE, 'service_active_checks_enabled', _("Active Checks Enabled"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_active_checks_enabled' => '2'));

	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("Yes"), '1');
	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("No"), '0');
	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("Default"), '2');
	$form->addGroup($servicePCE, 'service_passive_checks_enabled', _("Passive Checks Enabled"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_passive_checks_enabled' => '2'));

	$form->addElement('select', 'timeperiod_tp_id', _("Check Period"), $tps);

	##
	## Notification informations
	##
	$form->addElement('header', 'notification', _("Notification"));
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("Yes"), '1');
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("No"), '0');
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("Default"), '2');
	$form->addGroup($serviceNE, 'service_notifications_enabled', _("Notification Enabled"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_notifications_enabled' => '2'));
	
	if ($o == "mc")	{
		$mc_mod_cgs = array();
		$mc_mod_cgs[] = &HTML_QuickForm::createElement('radio', 'mc_mod_cgs', null, _("Incremental"), '0');
		$mc_mod_cgs[] = &HTML_QuickForm::createElement('radio', 'mc_mod_cgs', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_cgs, 'mc_mod_cgs', _("Update options"), '&nbsp;');
		$form->setDefaults(array('mc_mod_cgs'=>'0'));
	}
	
	/*
	 *  Contacts
	 */
	$ams3 =& $form->addElement('advmultiselect', 'service_cs', _("Implied Contacts"), $notifCs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	/*
	 *  Contact groups
	 */
    $ams3 =& $form->addElement('advmultiselect', 'service_cgs', _("Implied ContactGroups"), $notifCgs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	$form->addElement('text', 'service_notification_interval', _("Notification Interval"), $attrsText2);
	$form->addElement('select', 'timeperiod_tp_id2', _("Notification Period"), $tps);

 	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unknown');
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'Recovery');
	if ($oreon->user->get_version() >= 2)
		$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', 'Flapping');
	$form->addGroup($serviceNotifOpt, 'service_notifOpts', _("Notification Type"), '&nbsp;&nbsp;');

 	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', 'Ok');
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'unknown');
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	$form->addGroup($serviceStalOpt, 'service_stalOpts', _("Stalking Options"), '&nbsp;&nbsp;');

	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$serviceActivation[] = &HTML_QuickForm::createElement('radio', 'service_activate', null, _("Enabled"), '1');
	$serviceActivation[] = &HTML_QuickForm::createElement('radio', 'service_activate', null, _("Disabled"), '0');
	$form->addGroup($serviceActivation, 'service_activate', _("Status"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_activate' => '1'));
	$form->addElement('textarea', 'service_comment', _("Comments"), $attrsTextarea);

	#
	## Sort 2 - Service relations
	#
	if ($o == "a")
		$form->addElement('header', 'title2', _("Add relations"));
	else if ($o == "c")
		$form->addElement('header', 'title2', _("Modify relations"));
	else if ($o == "w")
		$form->addElement('header', 'title2', _("View relations"));
	else if ($o == "mc")
		$form->addElement('header', 'title2', _("Massive Change"));
		
	$form->addElement('header', 'links', _("Relations"));

 	if ($o == "mc")	{
		$mc_mod_traps = array();
		$mc_mod_traps[] = &HTML_QuickForm::createElement('radio', 'mc_mod_traps', null, _("Incremental"), '0');
		$mc_mod_traps[] = &HTML_QuickForm::createElement('radio', 'mc_mod_traps', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_traps, 'mc_mod_traps', _("Update options"), '&nbsp;');
		$form->setDefaults(array('mc_mod_traps'=>'0'));
	}
	$form->addElement('header', 'traps', _("SNMP Traps"));
    $ams3 =& $form->addElement('advmultiselect', 'service_traps', _("Service Trap Relation"), $traps, $attrsAdvSelect_big);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	if ($o == "mc")	{
		$mc_mod_Pars = array();
		$mc_mod_Pars[] = &HTML_QuickForm::createElement('radio', 'mc_mod_Pars', null, _("Incremental"), '0');
		$mc_mod_Pars[] = &HTML_QuickForm::createElement('radio', 'mc_mod_Pars', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_Pars, 'mc_mod_Pars', _("Update options"), '&nbsp;');
		$form->setDefaults(array('mc_mod_Pars'=>'0'));
	} 
	$ams3 =& $form->addElement('advmultiselect', 'service_hPars', _("Linked to host templates "), $hosts, $attrsAdvSelect_big);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	# trap vendor
	$mnftr = array(NULL=>NULL);	
	$DBRESULT =& $pearDB->query("SELECT id, alias FROM traps_vendor order by alias");
	while ($rmnftr =& $DBRESULT->fetchRow())
		$mnftr[$rmnftr["id"]] = html_entity_decode($rmnftr["alias"], ENT_QUOTES);
	$mnftr[""] = "_"._("ALL")."_";
	$DBRESULT->free();
	$attrs2 = array(
		'onchange'=>"javascript: " .
				" 	getTrap(this.form.elements['mnftr'].value); return false; ");
	$form->addElement('select', 'mnftr', _("Vendor Name"), $mnftr, $attrs2);
	include("./include/configuration/configObject/traps/ajaxTrap_js.php");
	
	##
	## Sort 3 - Data treatment
	##
	
	if ($o == "a")
		$form->addElement('header', 'title3', _("Add Data Processing"));
	else if ($o == "c")
		$form->addElement('header', 'title3', _("Modify Data Processing"));
	else if ($o == "w")
		$form->addElement('header', 'title3', _("View Data Processing"));
	else if ($o == "mc")
		$form->addElement('header', 'title2', _("Massive Change"));
	
	$form->addElement('header', 'treatment', _("Data Processing"));

	$servicePC[] = &HTML_QuickForm::createElement('radio', 'service_parallelize_check', null, _("Yes"), '1');
	$servicePC[] = &HTML_QuickForm::createElement('radio', 'service_parallelize_check', null, _("No"), '0');
	$servicePC[] = &HTML_QuickForm::createElement('radio', 'service_parallelize_check', null, _("Default"), '2');
	$form->addGroup($servicePC, 'service_parallelize_check', _("Parallel Check"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_parallelize_check' => '2'));

	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("Yes"), '1');
	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("No"), '0');
	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("Default"), '2');
	$form->addGroup($serviceOOS, 'service_obsess_over_service', _("Obsess Over Service"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_obsess_over_service' => '2'));

	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("Yes"), '1');
	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("No"), '0');
	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("Default"), '2');
	$form->addGroup($serviceCF, 'service_check_freshness', _("Check Freshness"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_check_freshness' => '2'));

	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("Yes"), '1');
	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("No"), '0');
	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("Default"), '2');
	$form->addGroup($serviceFDE, 'service_flap_detection_enabled', _("Flap Detection Enabled"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_flap_detection_enabled' => '2'));

	$form->addElement('text', 'service_freshness_threshold', _("Freshness Threshold"), $attrsText2);
	$form->addElement('text', 'service_low_flap_threshold', _("Low Flap Threshold"), $attrsText2);
	$form->addElement('text', 'service_high_flap_threshold', _("High Flap Threshold"), $attrsText2);

	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, _("Yes"), '1');
	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, _("No"), '0');
	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, _("Default"), '2');
	$form->addGroup($servicePPD, 'service_process_perf_data', _("Process Perf Data"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_process_perf_data' => '2'));

	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("Yes"), '1');
	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("No"), '0');
	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("Default"), '2');
	$form->addGroup($serviceRSI, 'service_retain_status_information', _("Retain Status Information"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_retain_status_information' => '2'));

	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("Yes"), '1');
	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("No"), '0');
	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("Default"), '2');
	$form->addGroup($serviceRNI, 'service_retain_nonstatus_information', _("Retain Non Status Information"), '&nbsp;');
	if ($o != "mc")
		$form->setDefaults(array('service_retain_nonstatus_information' => '2'));

	#
	## Sort 4 - Extended Infos
	#
	if ($o == "a")
		$form->addElement('header', 'title4', _("Add an Extended Info"));
	else if ($o == "c")
		$form->addElement('header', 'title4', _("Modify an Extended Info"));
	else if ($o == "w")
		$form->addElement('header', 'title4', _("View an Extended Info"));
	else if ($o == "mc")
		$form->addElement('header', 'title2', _("Massive Change"));

	$form->addElement('header', 'nagios', _("Nagios"));
	if ($oreon->user->get_version() >= 2)
		$form->addElement('text', 'esi_notes', _("Notes"), $attrsText);
	$form->addElement('text', 'esi_notes_url', _("URL"), $attrsText);
	if ($oreon->user->get_version() >= 2)
		$form->addElement('text', 'esi_action_url', _("Action URL"), $attrsText);
	$form->addElement('select', 'esi_icon_image', _("Icon"), $extImg, array("onChange"=>"showLogo('esi_icon_image',this.form.elements['esi_icon_image'].value)"));
	$form->addElement('text', 'esi_icon_image_alt', _("Alt icon"), $attrsText);

	$form->addElement('header', 'oreon', _("Centreon"));
	$form->addElement('select', 'graph_id', _("Graph Template"), $graphTpls);

	if ($o == "mc")	{
		$mc_mod_sc = array();
		$mc_mod_sc[] = &HTML_QuickForm::createElement('radio', 'mc_mod_sc', null, _("Incremental"), '0');
		$mc_mod_sc[] = &HTML_QuickForm::createElement('radio', 'mc_mod_sc', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_sc, 'mc_mod_sc', _("Update options"), '&nbsp;');
		$form->setDefaults(array('mc_mod_sc'=>'0'));
	}
	$ams3 =& $form->addElement('advmultiselect', 'service_categories', _("Categories"), $service_categories, $attrsAdvSelect_small);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	#
	## Sort 5 - Macros - Nagios 3
	#
	
	if ($oreon->user->get_version() == 3) {
		if ($o == "a")
			$form->addElement('header', 'title5', _("Add macros"));
		else if ($o == "c")
			$form->addElement('header', 'title5', _("Modify macros"));
		else if ($o == "w")
			$form->addElement('header', 'title5', _("View macros"));
		else if ($o == "mc")
			$form->addElement('header', 'title5', _("Massive Change"));
	
		$form->addElement('header', 'macro', _("Macros"));
		
		$form->addElement('text', 'add_new', _("Add a new macro"), $attrsText2);
		$form->addElement('text', 'macroName', _("Macro name"), $attrsText2);
		$form->addElement('text', 'macroValue', _("Macro value"), $attrsText2);
		$form->addElement('text', 'macroDelete', _("Delete"), $attrsText2);
		
		include_once("include/configuration/configObject/service/makeJS_formService.php");	
		if ($o == "c" || $o == "a" || $o == "mc")
		{			
			for ($k=0; isset($od_macro_id[$k]); $k++) {?>				
				<script type="text/javascript">
				globalMacroTabId[<?php echo $k;?>] = <?php echo $od_macro_id[$k];?>;		
				globalMacroTabName[<?php echo $k;?>] = '<?php echo $od_macro_name[$k];?>';
				globalMacroTabValue[<?php echo $k;?>] = '<?php echo $od_macro_value[$k];?>';
				globalMacroTabSvcId[<?php echo $k;?>] = <?php echo $od_macro_svc_id[$k];?>;				
				</script>			
		<?php
			}
		}
	}


	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
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
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('service_description', 'myReplace');
	$form->applyFilter('service_alias', 'myReplaceAlias');
	$from_list_menu = false;
	if ($o != "mc")	{
		$form->addRule('service_description', _("Compulsory Name"), 'required');
		$form->addRule('service_alias', _("Compulsory Name"), 'required');
		$form->registerRule('exist', 'callback', 'testServiceTemplateExistence');
		$form->addRule('service_description', _("Name is already in use"), 'exist');
	}
	else if ($o == "mc")	{
		if ($form->getSubmitValue("submitMC"))
			$from_list_menu = false;
		else
			$from_list_menu = true;
	}
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path2, $tpl);

	# Just watch a host information
	if ($o == "w")	{
		if (!$min)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&service_id=".$service_id."'"));
	    $form->setDefaults($service);
		$form->freeze();
	}
	# Modify a service information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($service);
	}
	# Add a service information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	# Massive Change
	else if ($o == "mc")	{
		$subMC =& $form->addElement('submit', 'submitMC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version(), "tpl"=>1));
	$tpl->assign("sort1", _("Service Configuration"));
	$tpl->assign("sort2", _("Relations"));
	$tpl->assign("sort3", _("Data Processing"));
	$tpl->assign("sort4", _("Service Extended Info"));
	$tpl->assign("sort5", _("Macros"));
	$tpl->assign('javascript', "<script type='text/javascript'>function showLogo(_img_dst, _value) {".
	"var _img = document.getElementById(_img_dst + '_img');".
	"_img.src = 'include/common/getHiddenImage.php?path=' + _value + '&logo=1' ; }</script>" );	
	$tpl->assign('time_unit', " * ".$oreon->Nagioscfg["interval_length"]." "._(" seconds "));

	$valid = false;
	if ($form->validate() && $from_list_menu == false)	{
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
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&service_id=".$serviceObj->getValue()."'"));
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

		$tpl->assign("Freshness_Control_options", _("Freshness Control options"));
		$tpl->assign("Flapping_Options", _("Flapping options"));
		$tpl->assign("Perfdata_Options", _("Perfdata Options"));
		$tpl->assign("History_Options", _("History Options"));
		$tpl->assign("Event_Handler", _("Event Handler"));
		$tpl->assign("topdoc", _("Documentation"));
		$tpl->assign("seconds", _("seconds"));
		
		$tpl->display("formService.ihtml");
	}
?>
<script type="text/javascript">		
		displayExistingMacroSvc(<?php echo $k;?>);
</script>
