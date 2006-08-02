<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
	## Database retrieve information for Escalation
	#
	$esc = array();
	if (($o == "c" || $o == "w") && $esc_id)	{	
		$res =& $pearDB->query("SELECT * FROM escalation WHERE esc_id = '".$esc_id."' LIMIT 1");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		# Set base value
		$esc = array_map("myDecode", $res->fetchRow());
		# Set Host Options
		$esc["escalation_options1"] =& explode(',', $esc["escalation_options1"]);
		foreach ($esc["escalation_options1"] as $key => $value)
			$esc["escalation_options1"][trim($value)] = 1;
		# Set Service Options
		$esc["escalation_options2"] =& explode(',', $esc["escalation_options2"]);
		foreach ($esc["escalation_options2"] as $key => $value)
			$esc["escalation_options2"][trim($value)] = 1;
		# Set Host Groups relations
		$res =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM escalation_hostgroup_relation WHERE escalation_esc_id = '".$esc_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($hg); $i++)
			$esc["esc_hgs"][$i] = $hg["hostgroup_hg_id"];
		$res->free();
		# Set Host relations
		$res =& $pearDB->query("SELECT DISTINCT host_host_id FROM escalation_host_relation WHERE escalation_esc_id = '".$esc_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($host); $i++)
			$esc["esc_hosts"][$i] = $host["host_host_id"];
		$res->free();
		# Set Meta Service
		$res =& $pearDB->query("SELECT DISTINCT emsr.meta_service_meta_id FROM escalation_meta_service_relation emsr WHERE emsr.escalation_esc_id = '".$esc_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($metas); $i++)
			$esc["esc_metas"][$i] = $metas["meta_service_meta_id"];
		$res->free();
		# Set Host Service
		$res =& $pearDB->query("SELECT DISTINCT * FROM escalation_service_relation esr WHERE esr.escalation_esc_id = '".$esc_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($services); $i++)
			$esc["esc_hServices"][$i] = $services["host_host_id"]."_".$services["service_service_id"];
		$res->free();
		# Set Contact Groups relations
		$res =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM escalation_contactgroup_relation WHERE escalation_esc_id = '".$esc_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($cg); $i++)
			$esc["esc_cgs"][$i] = $cg["contactgroup_cg_id"];
		$res->free();		
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Host Groups comes from DB -> Store in $hgs Array
	$hgs = array();
	$res =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup WHERE hg_id IN (".$oreon->user->lcaHGStr.") ORDER BY hg_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($hg))
		$hgs[$hg["hg_id"]] = $hg["hg_name"];
	$res->free();
	#
	# Host comes from DB -> Store in $hosts Array
	$hosts = array();
	$res =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' AND host_id IN (".$oreon->user->lcaHStr.") ORDER BY host_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($host))
		$hosts[$host["host_id"]] = $host["host_name"];
	$res->free();
	#
	# Services comes from DB -> Store in $hServices Array	
	$hServices = array();
	$res =& $pearDB->query("SELECT DISTINCT host_id, host_name FROM host WHERE host_register = '1' AND host_id IN (".$oreon->user->lcaHStr.") ORDER BY host_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($elem))	{
		$services = getMyHostServices($elem["host_id"]);
		foreach ($services as $key=>$index)
			$hServices[$elem["host_id"]."_".$key] = $elem["host_name"]." / ".$index;
	}
	$res->free();
	# Meta Services comes from DB -> Store in $metas Array
	$metas = array();
	$res =& $pearDB->query("SELECT meta_id, meta_name FROM meta_service ORDER BY meta_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($meta))
		$metas[$meta["meta_id"]] = $meta["meta_name"];
	$res->free();
	# Contact Groups comes from DB -> Store in $cgs Array
	$cgs = array();
	$res =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($cg))
		$cgs[$cg["cg_id"]] = $cg["cg_name"];
	$res->free();
	#
	# TimePeriods comes from DB -> Store in $tps Array
	$tps = array();
	$res =& $pearDB->query("SELECT tp_id, tp_name FROM timeperiod ORDER BY tp_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($tp))
		$tps[$tp["tp_id"]] = $tp["tp_name"];
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 250px; height: 150px;");
	$attrsAdvSelect2 = array("style" => "width: 250px; height: 400px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"30");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["esc_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["esc_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["esc_view"]);

	#
	## Escalation basic information
	#
	$form->addElement('header', 'information', $lang['esc_infos']);
	$form->addElement('text', 'esc_name', $lang["esc_name"], $attrsText);
	$form->addElement('text', 'first_notification', $lang["esc_firstNotif"], $attrsText2);
	$form->addElement('text', 'last_notification', $lang["esc_lastNotif"], $attrsText2);
	$form->addElement('text', 'notification_interval', $lang["esc_notifInt"], $attrsText2);
	if ($oreon->user->get_version() == 2)	{
		$form->addElement('select', 'escalation_period', $lang["esc_escPeriod"], $tps);
		$tab = array();
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', 'd');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'u');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'r');
		$form->addGroup($tab, 'escalation_options1', $lang['esc_hOpt'], '&nbsp;&nbsp;');
		$tab = array();
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'w');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'u');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'c');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'r', '&nbsp;', 'r');
		$form->addGroup($tab, 'escalation_options2', $lang['esc_sOpt'], '&nbsp;&nbsp;');
	}
	$form->addElement('textarea', 'esc_comment', $lang["esc_comment"], $attrsTextarea);
	
    $ams1 =& $form->addElement('advmultiselect', 'esc_cgs', $lang['esc_appCG'], $cgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	#
	## Sort 2
	#
	$form->addElement('header', 'hosts', $lang['esc_sortHosts']);
	
    $ams1 =& $form->addElement('advmultiselect', 'esc_hosts', $lang['h'], $hosts, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	#
	## Sort 3
	#
	$form->addElement('header', 'services', $lang['esc_sortSv']);
	
    $ams1 =& $form->addElement('advmultiselect', 'esc_hServices', $lang['esc_hostServiceMembers'], $hServices, $attrsAdvSelect2);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	#
	## Sort 4
	#
	$form->addElement('header', 'hgs', $lang['esc_sortHg']);
	
    $ams1 =& $form->addElement('advmultiselect', 'esc_hgs', $lang['hg'], $hgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	#
	## Sort 5
	#
	$form->addElement('header', 'metas', $lang['esc_sortMs']);
	
    $ams1 =& $form->addElement('advmultiselect', 'esc_metas', $lang['ms'], $metas, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'esc_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('_ALL_', 'trim');
	$form->addRule('esc_name', $lang['ErrName'], 'required');
	$form->addRule('first_notification', $lang['ErrRequired'], 'required');
	$form->addRule('last_notification', $lang['ErrRequired'], 'required');
	$form->addRule('notification_interval', $lang['ErrRequired'], 'required');
	$form->addRule('esc_cgs', $lang['ErrRequired'], 'required');
	$form->addRule('dep_hostChilds', $lang['ErrRequired'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('esc_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
		
	# Just watch a Escalation information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&esc_id=".$esc_id."'"));
	    $form->setDefaults($esc);
		$form->freeze();
	}
	# Modify a Escalation information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($esc);
	}
	# Add a Escalation information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	$tpl->assign("nagios", $oreon->user->get_version());
	
	$tpl->assign("sort1", $lang['esc_infos']);
	$tpl->assign("sort2", $lang['esc_sort2']);
	$tpl->assign("sort3", $lang['esc_sort3']);
	$tpl->assign("sort4", $lang['esc_sort4']);
	$tpl->assign("sort5", $lang['esc_sort5']);
	
	$tpl->assign('time_unit', " * ".$oreon->Nagioscfg["interval_length"]." ".$lang["time_sec"]);
	
	$valid = false;
	if ($form->validate())	{
		$escObj =& $form->getElement('esc_id');
		if ($form->getSubmitValue("submitA"))
			$escObj->setValue(insertEscalationInDB());
		else if ($form->getSubmitValue("submitC"))
			updateEscalationInDB($escObj->getValue("esc_id"));
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&esc_id=".$escObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listEscalation.php");
	else	{
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formEscalation.ihtml");
	}
?>