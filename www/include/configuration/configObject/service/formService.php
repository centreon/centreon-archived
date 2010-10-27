<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	if (!isset($oreon)) {
		exit();
	}
	#
	## Database retrieve information for Service
	#

	function myDecodeService($arg)
	{
		$arg = str_replace('#BR#', "\\n", $arg);
		$arg = str_replace('#T#', "\\t", $arg);
		$arg = str_replace('#R#', "\\r", $arg);
		$arg = str_replace('#S#', "/", $arg);
		$arg = str_replace('#BS#', "\\", $arg);

		return html_entity_decode($arg, ENT_QUOTES, "UTF-8");
	}

	$cmdId = 0;
	$service = array();
	$serviceTplId = null;
	if (($o == "c" || $o == "w") && $service_id) {

		$DBRESULT = $pearDB->query("SELECT * FROM service, extended_service_information esi WHERE service_id = '".$service_id."' AND esi.service_service_id = service_id LIMIT 1");
		/*
		 * Set base value
		 */
		$service = array_map("myDecodeService", $DBRESULT->fetchRow());
		$serviceTplId = $service['service_template_model_stm_id'];
		$cmdId = $service['command_command_id'];

		/*
		 * Grab hostgroup || host
		 */
		$DBRESULT = $pearDB->query("SELECT host_host_id FROM host_service_relation hsr, host WHERE hsr.service_service_id = '".$service_id."' AND host_host_id IS NOT NULL AND host_id = host_host_id ORDER BY host_name, host_alias");
		while ($parent = $DBRESULT->fetchRow())	{
			if ($parent["host_host_id"]) {
				$service["service_hPars"][$parent["host_host_id"]] = $parent["host_host_id"];
			}
		}
		$DBRESULT->free();

		$DBRESULT = $pearDB->query("SELECT hostgroup_hg_id FROM host_service_relation hsr, hostgroup WHERE hsr.service_service_id = '".$service_id."' AND hostgroup_hg_id IS NOT NULL AND hostgroup_hg_id = hg_id ORDER BY hg_name, hg_alias");
		while ($parent = $DBRESULT->fetchRow())	{
			if ($parent["hostgroup_hg_id"]) {
				$service["service_hgPars"][$parent["hostgroup_hg_id"]] = $parent["hostgroup_hg_id"];
			}
		}
		$DBRESULT->free();

		/*
		 * Set Service Notification Options
		 */
		$tmp = explode(',', $service["service_notification_options"]);
		foreach ($tmp as $key => $value) {
			$service["service_notifOpts"][trim($value)] = 1;
		}

		/*
		 * Set Stalking Options
		 */
		$tmp = explode(',', $service["service_stalking_options"]);
		foreach ($tmp as $key => $value) {
			$service["service_stalOpts"][trim($value)] = 1;
		}

		/*
		 * Set Contact Group
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM contactgroup_service_relation WHERE service_service_id = '".$service_id."'");
		for ($i = 0; $notifCg =& $DBRESULT->fetchRow(); $i++) {
			$service["service_cgs"][$i] = $notifCg["contactgroup_cg_id"];
		}
		$DBRESULT->free();

		/*
		 * Set Contact Group
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT contact_id FROM contact_service_relation WHERE service_service_id = '".$service_id."'");
		for ($i = 0; $notifC =& $DBRESULT->fetchRow(); $i++) {
			$service["service_cs"][$i] = $notifC["contact_id"];
		}
		$DBRESULT->free();

		/*
		 * Set Service Group Parents
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT servicegroup_sg_id FROM servicegroup_relation WHERE service_service_id = '".$service_id."'");
		for ($i = 0; $sg =& $DBRESULT->fetchRow(); $i++) {
			$service["service_sgs"][$i] = $sg["servicegroup_sg_id"];
		}
		$DBRESULT->free();

		/*
		 * Set Traps
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT traps_id FROM traps_service_relation WHERE service_id = '".$service_id."'");
		for ($i = 0; $trap =& $DBRESULT->fetchRow(); $i++) {
			$service["service_traps"][$i] = $trap["traps_id"];
		}
		$DBRESULT->free();

		/*
		 * Set Categories
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT sc_id FROM service_categories_relation WHERE service_service_id = '".$service_id."'");
		for ($i = 0; $service_category =& $DBRESULT->fetchRow(); $i++) {
			$service["service_categories"][$i] = $service_category["sc_id"];
		}
		$DBRESULT->free();
	}


	/*
	 * Database retrieve information for differents elements list we need on the page
	 */

	# Hosts comes from DB -> Store in $hosts Array
	$hosts = array();
	$DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host = $DBRESULT->fetchRow()) {
		$hosts[$host["host_id"]] = $host["host_name"];
	}
	$DBRESULT->free();

	# Service Templates comes from DB -> Store in $svTpls Array
	$svTpls = array(null => null);
	$DBRESULT = $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service WHERE service_register = '0' AND service_id != '".$service_id."' ORDER BY service_description");
	while ($svTpl = $DBRESULT->fetchRow())	{
		if (!$svTpl["service_description"]) {
			$svTpl["service_description"] = getMyServiceName($svTpl["service_template_model_stm_id"])."'";
		} else {
			$svTpl["service_description"] = str_replace('#S#', "/", $svTpl["service_description"]);
			$svTpl["service_description"] = str_replace('#BS#', "\\", $svTpl["service_description"]);
		}
		$svTpls[$svTpl["service_id"]] = $svTpl["service_description"];
	}
	$DBRESULT->free();

	# HostGroups comes from DB -> Store in $hgs Array
	$hgs = array();
	$DBRESULT = $pearDB->query("SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name");
	while ($hg = $DBRESULT->fetchRow()) {
		$hgs[$hg["hg_id"]] = $hg["hg_name"];
	}
	$DBRESULT->free();

	# Timeperiods comes from DB -> Store in $tps Array
	$tps = array(null => null);
	$DBRESULT = $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
	while ($tp = $DBRESULT->fetchRow()) {
		$tps[$tp["tp_id"]] = $tp["tp_name"];
	}
	$DBRESULT->free();

	# Check commands comes from DB -> Store in $checkCmds Array
	$checkCmds = array(null => null);
	$DBRESULT = $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' ORDER BY command_name");
	while ($checkCmd = $DBRESULT->fetchRow()) {
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	}
	$DBRESULT->free();

	# Check commands comes from DB -> Store in $checkCmdEvent Array
	$checkCmdEvent = array(null => null);
	$DBRESULT = $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' OR command_type = '3' ORDER BY command_name");
	while ($checkCmd = $DBRESULT->fetchRow()) {
		$checkCmdEvent[$checkCmd["command_id"]] = $checkCmd["command_name"];
	}
	$DBRESULT->free();

	# Contact Groups comes from DB -> Store in $notifCcts Array
	$notifCgs = array();
	$DBRESULT = $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
	while ($notifCg = $DBRESULT->fetchRow()) {
		$notifCgs[$notifCg["cg_id"]] = $notifCg["cg_name"];
	}
	$DBRESULT->free();

	# Contact comes from DB -> Store in $notifCcts Array
	$notifCs = array();
	$DBRESULT = $pearDB->query("SELECT contact_id, contact_name FROM contact ORDER BY contact_name");
	while ($notifC = $DBRESULT->fetchRow()) {
		$notifCs[$notifC["contact_id"]] = $notifC["contact_name"];
	}
	$DBRESULT->free();

	# Service Groups comes from DB -> Store in $sgs Array
	$sgs = array();
	$DBRESULT = $pearDB->query("SELECT sg_id, sg_name FROM servicegroup ORDER BY sg_name");
	while ($sg = $DBRESULT->fetchRow()) {
		$sgs[$sg["sg_id"]] = $sg["sg_name"];
	}
	$DBRESULT->free();

	# Graphs Template comes from DB -> Store in $graphTpls Array
	$graphTpls = array(null => null);
	$DBRESULT = $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	while ($graphTpl = $DBRESULT->fetchRow()) {
		$graphTpls[$graphTpl["graph_id"]] = $graphTpl["name"];
	}
	$DBRESULT->free();

	# service categories comes from DB -> Store in $service_categories Array
	$service_categories = array();
	$DBRESULT = $pearDB->query("SELECT sc_name, sc_id FROM service_categories ORDER BY sc_name");
	while ($service_categorie =& $DBRESULT->fetchRow()) {
		$service_categories[$service_categorie["sc_id"]] = $service_categorie["sc_name"];
	}
	$DBRESULT->free();

	# Traps definition comes from DB -> Store in $traps Array
	$traps = array();
	$DBRESULT = $pearDB->query("SELECT traps_id, traps_name FROM traps ORDER BY traps_name");
	while ($trap = $DBRESULT->fetchRow()) {
		$traps[$trap["traps_id"]] = $trap["traps_name"];
	}
	$DBRESULT->free();

	# IMG comes from DB -> Store in $extImg Array
	$extImg = array();
	$extImg = return_image_list(1);


	/*
	 *  Service on demand macro stored in DB
	 */
	$j = 0;
	$DBRESULT = $pearDB->query("SELECT svc_macro_id, svc_macro_name, svc_macro_value, svc_svc_id FROM on_demand_macro_service WHERE svc_svc_id = '". $service_id ."' ORDER BY `svc_macro_id`");
	while($od_macro = $DBRESULT->fetchRow()) {
		$od_macro_id[$j] = $od_macro["svc_macro_id"];
		$od_macro_name[$j] = str_replace("\$_SERVICE", "", $od_macro["svc_macro_name"]);
		$od_macro_name[$j] = str_replace("\$", "", $od_macro_name[$j]);
		$od_macro_name[$j] = str_replace("#BS#", "\\", $od_macro_name[$j]);
		$od_macro_name[$j] = str_replace("#S#", "/", $od_macro_name[$j]);
		$od_macro_value[$j] = str_replace("#BS#", "\\", $od_macro["svc_macro_value"]);
		$od_macro_value[$j] = str_replace("#S#", "/", $od_macro_value[$j]);
		$od_macro_svc_id[$j] = $od_macro["svc_svc_id"];
		$j++;
	}
	$DBRESULT->free();

	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 				= array("size"=>"30");
	$attrsText2				= array("size"=>"6");
	$attrsTextURL 			= array("size"=>"50");
	$attrsAdvSelect_small 	= array("style" => "width: 270px; height: 70px;");
	$attrsAdvSelect 		= array("style" => "width: 270px; height: 100px;");
	$attrsAdvSelect_big 	= array("style" => "width: 270px; height: 200px;");
	$attrsTextarea 			= array("rows"=>"5", "cols"=>"40");
	$template	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a") {
		$form->addElement('header', 'title', _("Add a Service"));
	} elseif ($o == "c") {
		$form->addElement('header', 'title', _("Modify a Service"));
	} elseif ($o == "w") {
		$form->addElement('header', 'title', _("View a Service"));
	} elseif ($o == "mc") {
		$form->addElement('header', 'title', _("Massive Change"));
	}

	# Sort 1
	#
	## Service basic information
	#
	$form->addElement('header', 'information', _("General Information"));

	/*
	 * - No possibility to change name and alias, because there's no interest
	 * - May be ? #409
	 */
	$form->addElement('text', 'service_description', _("Description"), $attrsText);
	$form->addElement('text', 'service_alias', _("Alias"), $attrsText);

	$form->addElement('select', 'service_template_model_stm_id', _("Service Template"), $svTpls, array('id'=>'svcTemplate', 'onChange'=>'changeServiceTemplate(this.value)'));
	$form->addElement('static', 'tplText', _("Using a Template exempts you to fill required fields"));

	#
	## Check information
	#
	$form->addElement('header', 'check', _("Service State"));

	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("Yes"), '1');
	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("No"), '0');
	$serviceIV[] = &HTML_QuickForm::createElement('radio', 'service_is_volatile', null, _("Default"), '2');
	$form->addGroup($serviceIV, 'service_is_volatile', _("Is Volatile"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_is_volatile' => '2'));
	}

    if ($o == "mc") {
	    $form->addElement('select', 'command_command_id', _("Check Command"), $checkCmds, 'onchange=setArgument(this.form,"command_command_id","example1")');
    } else {
        $form->addElement('select', 'command_command_id', _("Check Command"), $checkCmds, array('id' => "checkCommand", 'onChange' => "changeCommand(this.value);"));
    }
	$form->addElement('text', 'command_command_id_arg', _("Args"), $attrsText);
	$form->addElement('text', 'service_max_check_attempts', _("Max Check Attempts"), $attrsText2);
	$form->addElement('text', 'service_normal_check_interval', _("Normal Check Interval"), $attrsText2);
	$form->addElement('text', 'service_retry_check_interval', _("Retry Check Interval"), $attrsText2);

	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("Yes"), '1');
	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("No"), '0');
	$serviceEHE[] = &HTML_QuickForm::createElement('radio', 'service_event_handler_enabled', null, _("Default"), '2');
	$form->addGroup($serviceEHE, 'service_event_handler_enabled', _("Event Handler Enabled"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_event_handler_enabled' => '2'));
	}
	$form->addElement('select', 'command_command_id2', _("Event Handler"), $checkCmdEvent, 'onchange=setArgument(this.form,"command_command_id2","example2")');
	$form->addElement('text', 'command_command_id_arg2', _("Args"), $attrsText);

	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("Yes"), '1');
	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("No"), '0');
	$serviceACE[] = &HTML_QuickForm::createElement('radio', 'service_active_checks_enabled', null, _("Default"), '2');
	$form->addGroup($serviceACE, 'service_active_checks_enabled', _("Active Checks Enabled"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_active_checks_enabled' => '2'));
	}

	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("Yes"), '1');
	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("No"), '0');
	$servicePCE[] = &HTML_QuickForm::createElement('radio', 'service_passive_checks_enabled', null, _("Default"), '2');
	$form->addGroup($servicePCE, 'service_passive_checks_enabled', _("Passive Checks Enabled"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_passive_checks_enabled' => '2'));
	}

	$form->addElement('select', 'timeperiod_tp_id', _("Check Period"), $tps);

	##
	## Notification informations
	##
	$form->addElement('header', 'notification', _("Notification"));
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("Yes"), '1');
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("No"), '0');
	$serviceNE[] = &HTML_QuickForm::createElement('radio', 'service_notifications_enabled', null, _("Default"), '2');
	$form->addGroup($serviceNE, 'service_notifications_enabled', _("Notification Enabled"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_notifications_enabled' => '2'));
	}

	if ($o == "mc")	{
		$mc_mod_cgs = array();
		$mc_mod_cgs[] = &HTML_QuickForm::createElement('radio', 'mc_mod_cgs', null, _("Incremental"), '0');
		$mc_mod_cgs[] = &HTML_QuickForm::createElement('radio', 'mc_mod_cgs', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_cgs, 'mc_mod_cgs', _("Update mode"), '&nbsp;');
		$form->setDefaults(array('mc_mod_cgs'=>'0'));
	}
	/*
	 *  Contacts
	 */
	$ams3 = $form->addElement('advmultiselect', 'service_cs', array(_("Implied Contacts"), _("Available"), _("Selected")), $notifCs, $attrsAdvSelect, SORT_ASC);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	/*
	 *  Contact groups
	 */
	$ams3 = $form->addElement('advmultiselect', 'service_cgs', array(_("Implied Contact Groups"), _("Available"), _("Selected")), $notifCgs, $attrsAdvSelect, SORT_ASC);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	$form->addElement('text', 'service_first_notification_delay', _("First notification delay"), $attrsText2);
	$form->addElement('text', 'service_notification_interval', _("Notification Interval"), $attrsText2);
	$form->addElement('select', 'timeperiod_tp_id2', _("Notification Period"), $tps);

 	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"));
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"));
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"));
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', _("Recovery"));
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', _("Flapping"));
	$serviceNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 's', '&nbsp;', _("Downtime Scheduled"));
	$form->addGroup($serviceNotifOpt, 'service_notifOpts', _("Notification Type"), '&nbsp;&nbsp;');

 	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok"));
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"));
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"));
	$serviceStalOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"));
	$form->addGroup($serviceStalOpt, 'service_stalOpts', _("Stalking Options"), '&nbsp;&nbsp;');

	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$serviceActivation[] = &HTML_QuickForm::createElement('radio', 'service_activate', null, _("Enabled"), '1');
	$serviceActivation[] = &HTML_QuickForm::createElement('radio', 'service_activate', null, _("Disabled"), '0');
	$form->addGroup($serviceActivation, 'service_activate', _("Status"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_activate' => '1'));
	}
	$form->addElement('textarea', 'service_comment', _("Comments"), $attrsTextarea);

	#
	## Sort 2 - Service Relations
	#
	if ($o == "a") {
		$form->addElement('header', 'title2', _("Add relations"));
	} elseif ($o == "c") {
		$form->addElement('header', 'title2', _("Modify relations"));
	} elseif ($o == "w") {
		$form->addElement('header', 'title2', _("View relations"));
	} elseif ($o == "mc") {
		$form->addElement('header', 'title2', _("Massive Change"));
	}

	if ($o == "mc")	{
		$mc_mod_Pars = array();
		$mc_mod_Pars[] = &HTML_QuickForm::createElement('radio', 'mc_mod_Pars', null, _("Incremental"), '0');
		$mc_mod_Pars[] = &HTML_QuickForm::createElement('radio', 'mc_mod_Pars', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_Pars, 'mc_mod_Pars', _("Update mode"), '&nbsp;');
		$form->setDefaults(array('mc_mod_Pars'=>'0'));
	}
	$ams3 = $form->addElement('advmultiselect', 'service_hPars', array(_("Linked with Hosts"), _("Available"), _("Selected")), $hosts, $attrsAdvSelect_big, SORT_ASC);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	$ams3 = $form->addElement('advmultiselect', 'service_hgPars', array(_("Linked with Host Groups"), _("Available"), _("Selected")), $hgs, $attrsAdvSelect, SORT_ASC);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	# Service relations
	$form->addElement('header', 'links', _("Relations"));
	if ($o == "mc")	{
		$mc_mod_sgs = array();
		$mc_mod_sgs[] = &HTML_QuickForm::createElement('radio', 'mc_mod_sgs', null, _("Incremental"), '0');
		$mc_mod_sgs[] = &HTML_QuickForm::createElement('radio', 'mc_mod_sgs', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_sgs, 'mc_mod_sgs', _("Update mode"), '&nbsp;');
		$form->setDefaults(array('mc_mod_sgs'=>'0'));
	}
	$ams3 = $form->addElement('advmultiselect', 'service_sgs', array(_("Parent Service Groups"), _("Available"), _("Selected")), $sgs, $attrsAdvSelect, SORT_ASC);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	$form->addElement('header', 'traps', _("SNMP Traps"));
 	if ($o == "mc")	{
		$mc_mod_traps = array();
		$mc_mod_traps[] = &HTML_QuickForm::createElement('radio', 'mc_mod_traps', null, _("Incremental"), '0');
		$mc_mod_traps[] = &HTML_QuickForm::createElement('radio', 'mc_mod_traps', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_traps, 'mc_mod_traps', _("Update mode"), '&nbsp;');
		$form->setDefaults(array('mc_mod_traps'=>'0'));
	}
	$ams3 = $form->addElement('advmultiselect', 'service_traps', array(_("Service Trap Relation"), _("Available"), _("Selected")), $traps, $attrsAdvSelect_big, SORT_ASC);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);


	# trap vendor
	$mnftr = array(null => null);
	$DBRESULT = $pearDB->query("SELECT id, alias FROM traps_vendor order by alias");
	while ($rmnftr = $DBRESULT->fetchRow()) {
		$mnftr[$rmnftr["id"]] =  html_entity_decode($rmnftr["alias"], ENT_QUOTES, "UTF-8");
	}
	$mnftr[""] = "_"._("ALL")."_";
	$DBRESULT->free();
	$attrs2 = array('onchange' => "javascript:getTrap(this.form.elements['mnftr'].value); return false;");
	$form->addElement('select', 'mnftr', _("Vendor Name"), $mnftr, $attrs2);
	include("./include/configuration/configObject/traps/ajaxTrap_js.php");

	#
	## Sort 3 - Data treatment
	#
	if ($o == "a") {
		$form->addElement('header', 'title3', _("Add Data Processing"));
	} elseif ($o == "c") {
		$form->addElement('header', 'title3', _("Modify Data Processing"));
	} elseif ($o == "w") {
		$form->addElement('header', 'title3', _("View Data Processing"));
	} elseif ($o == "mc") {
		$form->addElement('header', 'title2', _("Massive Change"));
	}

	$form->addElement('header', 'treatment', _("Data Processing"));

	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("Yes"), '1');
	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("No"), '0');
	$serviceOOS[] = &HTML_QuickForm::createElement('radio', 'service_obsess_over_service', null, _("Default"), '2');
	$form->addGroup($serviceOOS, 'service_obsess_over_service', _("Obsess Over Service"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_obsess_over_service' => '2'));
	}

	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("Yes"), '1');
	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("No"), '0');
	$serviceCF[] = &HTML_QuickForm::createElement('radio', 'service_check_freshness', null, _("Default"), '2');
	$form->addGroup($serviceCF, 'service_check_freshness', _("Check Freshness"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_check_freshness' => '2'));
	}

	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("Yes"), '1');
	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("No"), '0');
	$serviceFDE[] = &HTML_QuickForm::createElement('radio', 'service_flap_detection_enabled', null, _("Default"), '2');
	$form->addGroup($serviceFDE, 'service_flap_detection_enabled', _("Flap Detection Enabled"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_flap_detection_enabled' => '2'));
	}

	$form->addElement('text', 'service_freshness_threshold', _("Freshness Threshold"), $attrsText2);
	$form->addElement('text', 'service_low_flap_threshold', _("Low Flap Threshold"), $attrsText2);
	$form->addElement('text', 'service_high_flap_threshold', _("High Flap Threshold"), $attrsText2);

	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, _("Yes"), '1');
	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, _("No"), '0');
	$servicePPD[] = &HTML_QuickForm::createElement('radio', 'service_process_perf_data', null, _("Default"), '2');
	$form->addGroup($servicePPD, 'service_process_perf_data', _("Process Perf Data"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_process_perf_data' => '2'));
	}

	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("Yes"), '1');
	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("No"), '0');
	$serviceRSI[] = &HTML_QuickForm::createElement('radio', 'service_retain_status_information', null, _("Default"), '2');
	$form->addGroup($serviceRSI, 'service_retain_status_information', _("Retain Status Information"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_retain_status_information' => '2'));
	}

	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("Yes"), '1');
	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("No"), '0');
	$serviceRNI[] = &HTML_QuickForm::createElement('radio', 'service_retain_nonstatus_information', null, _("Default"), '2');
	$form->addGroup($serviceRNI, 'service_retain_nonstatus_information', _("Retain Non Status Information"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('service_retain_nonstatus_information' => '2'));
	}

	#
	## Sort 4 - Extended Infos
	#
	if ($o == "a") {
		$form->addElement('header', 'title4', _("Add an Extended Info"));
	} elseif ($o == "c") {
		$form->addElement('header', 'title4', _("Modify an Extended Info"));
	} elseif ($o == "w") {
		$form->addElement('header', 'title4', _("View an Extended Info"));
	} elseif ($o == "mc") {
		$form->addElement('header', 'title3', _("Massive Change"));
	}

	$form->addElement('header', 'nagios', _("Nagios"));
	$form->addElement('text', 'esi_notes', _("Notes"), $attrsText);
	$form->addElement('text', 'esi_notes_url', _("URL"), $attrsTextURL);
	$form->addElement('text', 'esi_action_url', _("Action URL"), $attrsTextURL);
	$form->addElement('select', 'esi_icon_image', _("Icon"), $extImg, array("id"=>"esi_icon_image", "onChange"=>"showLogo('esi_icon_image_img',this.value)"));
	$form->addElement('text', 'esi_icon_image_alt', _("Alt icon"), $attrsText);

	$form->addElement('header', 'oreon', _("Centreon"));
	$form->addElement('select', 'graph_id', _("Graph Template"), $graphTpls);

	if ($o == "mc")	{
		$mc_mod_sc = array();
		$mc_mod_sc[] = &HTML_QuickForm::createElement('radio', 'mc_mod_sc', null, _("Incremental"), '0');
		$mc_mod_sc[] = &HTML_QuickForm::createElement('radio', 'mc_mod_sc', null, _("Replacement"), '1');
		$form->addGroup($mc_mod_sc, 'mc_mod_sc', _("Update mode"), '&nbsp;');
		$form->setDefaults(array('mc_mod_sc'=>'0'));
	}
	$ams3 = $form->addElement('advmultiselect', 'service_categories', array(_("Categories"), _("Available"), _("Selected")), $service_categories, $attrsAdvSelect_small, SORT_ASC);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	/*
	 * Sort 5 - Macros - Nagios 3
	 */
	if ($o == "a") {
		$form->addElement('header', 'title5', _("Add macros"));
	} elseif ($o == "c") {
		$form->addElement('header', 'title5', _("Modify macros"));
	} elseif ($o == "w") {
		$form->addElement('header', 'title5', _("View macros"));
	} elseif ($o == "mc") {
		$form->addElement('header', 'title5', _("Massive Change"));
	}

	$form->addElement('header', 'macro', _("Macros"));

	$form->addElement('text', 'add_new', _("Add a new macro"), $attrsText2);
	$form->addElement('text', 'macroName', _("Macro name"), $attrsText2);
	$form->addElement('text', 'macroValue', _("Macro value"), $attrsText2);
	$form->addElement('text', 'macroDelete', _("Delete"), $attrsText2);

	include_once("makeJS_formService.php");
	for($k=0; isset($od_macro_id[$k]); $k++) {?>
		<script type="text/javascript">
		globalMacroTabId[<?php echo$k;?>] = <?php echo$od_macro_id[$k];?>;
		globalMacroTabName[<?php echo$k;?>] = '<?php echo$od_macro_name[$k];?>';
		globalMacroTabValue[<?php echo$k;?>] = '<?php echo$od_macro_value[$k];?>';
		globalMacroTabSvcId[<?php echo$k;?>] = <?php echo$od_macro_svc_id[$k];?>;
		</script>
	<?php
	}

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'service_id');
	$reg = $form->addElement('hidden', 'service_register');
	$reg->setValue("1");
	$service_register = 1;
	$page = $form->addElement('hidden', 'p');
	$page->setValue($p);
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	if (is_array($select))	{
		$select_str = null;
		foreach ($select as $key => $value) {
			$select_str .= $key.",";
		}
		$select_pear = $form->addElement('hidden', 'select');
		$select_pear->setValue($select_str);
	}

	#
	## Form Rules
	#
	function myReplace()
	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("service_description")));
	}

	$form->applyFilter('__ALL__', 'myTrim');
	$from_list_menu = false;
	if ($o != "mc")	{
		$form->addRule('service_description', _("Compulsory Name"), 'required');
		# If we are using a Template, no need to check the value, we hope there are in the Template
		if (!$form->getSubmitValue("service_template_model_stm_id"))	{
			$form->addRule('command_command_id', _("Compulsory Command"), 'required');
			$form->addRule('service_max_check_attempts', _("Required Field"), 'required');
			$form->addRule('service_normal_check_interval', _("Required Field"), 'required');
			$form->addRule('service_retry_check_interval', _("Required Field"), 'required');
			$form->addRule('timeperiod_tp_id', _("Compulsory Period"), 'required');
			$form->addRule('service_cgs', _("Compulsory Contact Group"), 'required');
			$form->addRule('service_notification_interval', _("Required Field"), 'required');
			$form->addRule('timeperiod_tp_id2', _("Compulsory Period"), 'required');
			$form->addRule('service_notifOpts', _("Compulsory Option"), 'required');
			if (!$form->getSubmitValue("service_hPars")) {
				$form->addRule('service_hgPars', _("HostGroup or Host Required"), 'required');
			}
			if (!$form->getSubmitValue("service_hgPars")) {
				$form->addRule('service_hPars', _("HostGroup or Host Required"), 'required');
			}
		}
		if (!$form->getSubmitValue("service_hPars")) {
			$form->addRule('service_hgPars', _("HostGroup or Host Required"), 'required');
		}
		if (!$form->getSubmitValue("service_hgPars")) {
			$form->addRule('service_hPars', _("HostGroup or Host Required"), 'required');
		}
		$form->registerRule('exist', 'callback', 'testServiceExistence');
		$form->addRule('service_description', _("This description is in conflict with another one that is already defined in the selected relation(s)"), 'exist');

		$argChecker = $form->addElement("hidden", "argChecker");
	    $argChecker->setValue(1);
        $form->registerRule("argHandler", "callback", "argHandler");
	    $form->addRule("argChecker", _("You must either fill all the arguments or leave them all empty"), "argHandler");

		$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));
	} elseif ($o == "mc") {
		if ($form->getSubmitValue("submitMC")) {
			$from_list_menu = false;
		} else {
			$from_list_menu = true;
		}
	}

	#
	##End of form definition
	#

	// Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	// Just watch a host information
	if ($o == "w") {
		if (!$min && $centreon->user->access->page($p) != 2) {
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&service_id=".$service_id."'"));
		}
	    $form->setDefaults($service);
		$form->freeze();
	} elseif ($o == "c") {
		// Modify a service information
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('button', 'reset', _("Reset"), array("onClick" => "history.go(0);"));
	    $form->setDefaults($service);
	} elseif ($o == "a") {
		// Add a service information
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	} elseif ($o == "mc")	{
		// Massive Change
		$subMC = $form->addElement('submit', 'submitMC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

	$tpl->assign('msg', array ("nagios" => $oreon->user->get_version(), "tpl"=>0/*, "perfparse"=>$oreon->optGen["perfparse_installed"]*/));
	$tpl->assign("sort1", _("Service Configuration"));
	$tpl->assign("sort2", _("Relations"));
	$tpl->assign("sort3", _("Data Processing"));
	$tpl->assign("sort4", _("Service Extended Info"));
	$tpl->assign("sort5", _("Macros"));
	$tpl->assign('javascript', "<script type='text/javascript' src='./include/common/javascript/showLogo.js'></script>" );
	$tpl->assign('time_unit', " * ".$oreon->Nagioscfg["interval_length"]." "._("seconds"));
	$tpl->assign("p", $p);
	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );

	// prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	$valid = false;
	if ($form->validate() && $from_list_menu == false) {
		$serviceObj = $form->getElement('service_id');
		if ($form->getSubmitValue("submitA")) {
			$serviceObj->setValue(insertServiceInDB());
		} elseif ($form->getSubmitValue("submitC")) {
			updateServiceInDB($serviceObj->getValue());
		} elseif ($form->getSubmitValue("submitMC")) {
			$select = explode(",", $select);
			foreach ($select as $key=>$value) {
				if ($value) {
					updateServiceInDB($value, true);
				}
			}
		}
		if (count($form->getSubmitValue("service_hgPars")))	{
			$hPars = $form->getElement('service_hPars');
			$hPars->setValue(array());
		}
		$o = "w";
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&service_id=".$serviceObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	} elseif ($form->isSubmitted()) {
	    $tpl->assign("argChecker", "<font color='red'>". $form->getElementError("argChecker") . "</font>");
	}

	require_once $path.'javascript/argumentJs.php';
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])	{
		if ($p == "60201") {
			require_once($path."listServiceByHost.php");
		} elseif ($p == "60202") {
			require_once($path."listServiceByHostGroup.php");
		} elseif ($p == "602") {
			require_once($path."listServiceByHost.php");
		}
	} else {
		// Apply a template definition
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('is_not_template', $service_register);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->assign('custom_macro_label', _('Custom macros'));
		$tpl->assign("Freshness_Control_options", _("Freshness Control options"));
		$tpl->assign("Flapping_Options", _("Flapping options"));
		$tpl->assign("Perfdata_Options", _("Perfdata Options"));
		$tpl->assign("History_Options", _("History Options"));
		$tpl->assign("Event_Handler", _("Event Handler"));
		$tpl->assign("topdoc", _("Documentation"));
		$tpl->assign("seconds", _("seconds"));

		$tpl->assign('v', $oreon->user->get_version());
		$tpl->display("formService.ihtml");
?>
<script type="text/javascript">
	setTimeout('transformForm()', 200);
	displayExistingMacroSvc(<?php echo $k;?>, '<?php echo $o;?>');
	showLogo('esi_icon_image_img', document.getElementById('esi_icon_image').value);
</script>
<?php } ?>
