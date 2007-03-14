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
	require_once("./DBPerfparseConnect.php");
	$metrics = array(NULL=>NULL);
	$DBRESULT =& $pearDBpp->query("SELECT DISTINCT metric FROM perfdata_service_metric ORDER BY metric");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($metric))
		$metrics[$metric["metric"]] = $metric["metric"];
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
	$calType = array("AVE"=>$lang['ms_selAvr'], "SOM"=>$lang['ms_selSum'], "MIN"=>$lang['ms_selMin'], "MAX"=>$lang['ms_selMax']);
	
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
		$form->addElement('header', 'title', $lang["ms_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["ms_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["ms_view"]);

	# Sort 1
	#
	## Service basic information
	#
	$form->addElement('header', 'information', $lang['ms_infos']);

	$form->addElement('text', 'meta_name', $lang['ms_name'], $attrsText);
	$form->addElement('text', 'meta_display', $lang['ms_display'], $attrsText);
	$form->addElement('text', 'warning', $lang['ms_levelw'], $attrsText2);
	$form->addElement('text', 'critical', $lang['ms_levelc'], $attrsText2);
	$form->addElement('select', 'calcul_type', $lang['ms_calType'], $calType);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'meta_select_mode', null, $lang['ms_selList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'meta_select_mode', null, $lang['ms_sqlMatch'], '2');
	$form->addGroup($tab, 'meta_select_mode', $lang['ms_selMod'], '<br>');
	$form->setDefaults(array('meta_select_mode' => array('meta_select_mode'=>'1')));

	$form->addElement('text', 'regexp_str', $lang['ms_sqlMatch'], $attrsText);
	$form->addElement('select', 'metric', $lang['ms_metric'], $metrics);

	#
	## Check information
	#
	$form->addElement('header', 'check', $lang['ms_head_state']);

	$form->addElement('select', 'check_period', $lang['ms_checkPeriod'], $tps);
	$form->addElement('text', 'max_check_attempts', $lang['ms_checkMca'], $attrsText2);
	$form->addElement('text', 'normal_check_interval', $lang['ms_normalCheckInterval'], $attrsText2);
	$form->addElement('text', 'retry_check_interval', $lang['ms_retryCheckInterval'], $attrsText2);

	##
	## Notification informations
	##
	$form->addElement('header', 'notification', $lang['ms_head_notif']);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'notifications_enabled', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'notifications_enabled', null, $lang["no"], '0');
	$tab[] = &HTML_QuickForm::createElement('radio', 'notifications_enabled', null, $lang["nothing"], '2');
	$form->addGroup($tab, 'notifications_enabled', $lang['ms_notifEnabled'], '&nbsp;');
	$form->setDefaults(array('notifications_enabled' => '2'));

    $ams3 =& $form->addElement('advmultiselect', 'ms_cgs', $lang['ms_CgMembers'], $notifCgs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams3->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);

	$form->addElement('text', 'notification_interval', $lang['ms_notifInt'], $attrsText2);
	$form->addElement('select', 'notification_period', $lang['ms_notifTp'], $tps);

 	$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unknown');
	$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'Recovery');
	if ($oreon->user->get_version() == 2)
		$msNotifOpt[] = &HTML_QuickForm::createElement('checkbox', 'f', '&nbsp;', 'Flapping');
	$form->addGroup($msNotifOpt, 'ms_notifOpts', $lang['ms_notifOpts'], '&nbsp;&nbsp;');

	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$form->addElement('select', 'graph_id', $lang['sv_graphTpl'], $graphTpls);
	$msActivation[] = &HTML_QuickForm::createElement('radio', 'meta_activate', null, $lang["enable"], '1');
	$msActivation[] = &HTML_QuickForm::createElement('radio', 'meta_activate', null, $lang["disable"], '0');
	$form->addGroup($msActivation, 'meta_activate', $lang["status"], '&nbsp;');
	$form->setDefaults(array('meta_activate' => '1'));
	$form->addElement('textarea', 'meta_comment', $lang["cmt_comment"], $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
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
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('meta_name', 'myReplace');
	$form->addRule('meta_name', $lang['ErrName'], 'required');
	$form->addRule('max_check_attempts', $lang['ErrRequired'], 'required');
	$form->addRule('calcul_type', $lang['ErrRequired'], 'required');
	$form->addRule('meta_select_mode', $lang['ErrRequired'], 'required');
	$form->addRule('warning', $lang['ErrRequired'], 'required');
	$form->addRule('critical', $lang['ErrRequired'], 'required');
	$form->addRule('normal_check_interval', $lang['ErrRequired'], 'required');
	$form->addRule('retry_check_interval', $lang['ErrRequired'], 'required');
	$form->addRule('check_period', $lang['ErrTp'], 'required');
	$form->addRule('ms_cgs', $lang['ErrCg'], 'required');
	$form->addRule('notification_interval', $lang['ErrRequired'], 'required');
	$form->addRule('notification_period', $lang['ErrTp'], 'required');
	$form->addRule('notifications_enabled', $lang['ErrRequired'], 'required');
	$form->addRule('ms_notifOpts', $lang['ErrRequired'], 'required');
	$form->addRule('notifOpts', $lang['ErrOpt'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('meta_name', $lang['ErrAlreadyExist'], 'exist');
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
			$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&meta_id=".$meta_id."'"));
	    $form->setDefaults($ms);
		$form->freeze();
	}
	# Modify a service information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($ms);
	}
	# Add a service information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}

	$tpl->assign('msg', array ("nagios"=>$oreon->user->get_version()));
	$tpl->assign('time_unit', " * ".$oreon->Nagioscfg["interval_length"]." ".$lang["time_sec"]);

	$valid = false;
	if ($form->validate())	{
		$msObj =& $form->getElement('meta_id');
		if ($form->getSubmitValue("submitA"))
			$msObj->setValue(insertMetaServiceInDB());
		else if ($form->getSubmitValue("submitC"))
			updateMetaServiceInDB($msObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&meta_id=".$msObj->getValue()."'"));
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