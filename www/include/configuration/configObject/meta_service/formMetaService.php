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
	## Database retrieve information for Service
	#

	$ms = array();
	if (($o == "c" || $o == "w") && $meta_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE meta_id = '".$meta_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$ms = array_map("myDecode", $DBRESULT->fetchRow());
		# Set Service Notification Options
		$tmp = explode(',', $ms["notification_options"]);
		foreach ($tmp as $key => $value)
			$ms["ms_notifOpts"][trim($value)] = 1;
		# Set Contact Group
		$DBRESULT =& $pearDB->query("SELECT DISTINCT cg_cg_id FROM meta_contactgroup_relation WHERE meta_id = '".$meta_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($notifCg); $i++)
			$ms["ms_cgs"][$i] = $notifCg["cg_cg_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Perfparse Metric comes from DB -> Store in $metrics Array
	require_once("./DBOdsConnect.php");
	$metrics = array(NULL=>NULL);
	$DBRESULT =& $pearDBO->query("select DISTINCT metric_name from metrics ORDER BY metric_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($metric))
		$metrics[$metric["metric_name"]] = $metric["metric_name"];
	$DBRESULT->free();
	# Timeperiods comes from DB -> Store in $tps Array
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
	# Escalations comes from DB -> Store in $escs Array
	$escs = array();
	$DBRESULT =& $pearDB->query("SELECT esc_id, esc_name FROM escalation ORDER BY esc_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($esc))
		$escs[$esc["esc_id"]] = $esc["esc_name"];
	$DBRESULT->free();
	# Meta Service Dependencies comes from DB -> Store in $deps Array
	$deps = array();
	$DBRESULT =& $pearDB->query("SELECT meta_id, meta_name FROM meta_service WHERE meta_id != '".$meta_id."' ORDER BY meta_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($dep))
		$deps[$dep["meta_id"]] = $dep["meta_name"];
	$DBRESULT->free();
	# Calc Type
	$calType = array("AVE"=>_("Average"), "SOM"=>_("Sum"), "MIN"=>_("Min"), "MAX"=>_("Max"));
	
	# Graphs Template comes from DB -> Store in $graphTpls Array
	$graphTpls = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($graphTpl))
		$graphTpls[$graphTpl["graph_id"]] = $graphTpl["name"];
	$DBRESULT->free();

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
		$form->addElement('header', 'title', _("Add a Meta Service"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Meta Service"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Meta Service"));

	# Sort 1
	#
	## Service basic information
	#
	$form->addElement('header', 'information', _("General Information"));

	$form->addElement('text', 'meta_name', _("Meta Service Name"), $attrsText);
	$form->addElement('text', 'meta_display', _("Display format"), $attrsText);
	$form->addElement('text', 'warning', _("Warning Level"), $attrsText2);
	$form->addElement('text', 'critical', _("Critical Level"), $attrsText2);
	$form->addElement('select', 'calcul_type', _("Calculation Type"), $calType);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'meta_select_mode', null, _("Service List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'meta_select_mode', null, _("SQL matching"), '2');
	$form->addGroup($tab, 'meta_select_mode', _("Selection Mode"), '<br>');
	$form->setDefaults(array('meta_select_mode' => array('meta_select_mode'=>'1')));

	$form->addElement('text', 'regexp_str', _("SQL matching"), $attrsText);
	$form->addElement('select', 'metric', _("Metric"), $metrics);

	#
	## Check information
	#
	$form->addElement('header', 'check', _("Meta Service State"));

	$form->addElement('select', 'check_period', _("Check Period"), $tps);
	$form->addElement('text', 'max_check_attempts', _("Max Check Attempts"), $attrsText2);
	$form->addElement('text', 'normal_check_interval', _("Normal Check Interval"), $attrsText2);
	$form->addElement('text', 'retry_check_interval', _("Retry Check Interval"), $attrsText2);

	##
	## Notification informations
	##
	$form->addElement('header', 'notification', _("Notification"));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'notifications_enabled', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'notifications_enabled', null, _("No"), '0');
	$tab[] = &HTML_QuickForm::createElement('radio', 'notifications_enabled', null, _("Default"), '2');
	$form->addGroup($tab, 'notifications_enabled', _("Notification Enabled"), '&nbsp;');
	$form->setDefaults(array('notifications_enabled' => '2'));

    $ams3 =& $form->addElement('advmultiselect', 'ms_cgs', _("Linked ContactGroups"), $notifCgs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	$form->addElement('text', 'notification_interval', _("Notification Interval"), $attrsText2);
	$form->addElement('select', 'notification_period', _("Notification Period"), $tps);

 	$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unknown');
	$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'Recovery');
	if ($oreon->user->get_version() == 2)
		$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', 'Flapping');
	$form->addGroup($msNotifOpt, 'ms_notifOpts', _("Notification Type"), '&nbsp;&nbsp;');

	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$form->addElement('select', 'graph_id', _("Graph Template"), $graphTpls);
	$msActivation[] = &HTML_QuickForm::createElement('radio', 'meta_activate', null, _("Enabled"), '1');
	$msActivation[] = &HTML_QuickForm::createElement('radio', 'meta_activate', null, _("Disabled"), '0');
	$form->addGroup($msActivation, 'meta_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('meta_activate' => '1'));
	$form->addElement('textarea', 'meta_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'meta_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("meta_name")));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('meta_name', 'myReplace');
	$form->addRule('meta_name', _("Compulsory Name"), 'required');
	$form->addRule('max_check_attempts', _("Required Field"), 'required');
	$form->addRule('calcul_type', _("Required Field"), 'required');
	$form->addRule('meta_select_mode', _("Required Field"), 'required');
	$form->addRule('warning', _("Required Field"), 'required');
	$form->addRule('critical', _("Required Field"), 'required');
	$form->addRule('normal_check_interval', _("Required Field"), 'required');
	$form->addRule('retry_check_interval', _("Required Field"), 'required');
	$form->addRule('check_period', _("Compulsory Period"), 'required');
	$form->addRule('ms_cgs', _("Compulsory Contact Group"), 'required');
	$form->addRule('notification_interval', _("Required Field"), 'required');
	$form->addRule('notification_period', _("Compulsory Period"), 'required');
	$form->addRule('notifications_enabled', _("Required Field"), 'required');
	$form->addRule('ms_notifOpts', _("Required Field"), 'required');
	$form->addRule('notifOpts', _("Compulsory Option"), 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('meta_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a host information
	if ($o == "w")	{
		if (!$min)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&meta_id=".$meta_id."'"));
	    $form->setDefaults($ms);
		$form->freeze();
	}
	# Modify a service information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($ms);
	}
	# Add a service information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}

	$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version()));
	$tpl->assign('time_unit', " * ".$oreon->Nagioscfg["interval_length"]." "._(" seconds "));

	$valid = false;
	if ($form->validate())	{
		$msObj =& $form->getElement('meta_id');
		if ($form->getSubmitValue("submitA"))
			$msObj->setValue(insertMetaServiceInDB());
		else if ($form->getSubmitValue("submitC"))
			updateMetaServiceInDB($msObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&meta_id=".$msObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listMetaService.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formMetaService.ihtml");
	}
?>