<?php
/**
Oreon is developped with GPL Licence 2.0 :
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
	## Database retrieve information for Dependency
	#
	$dep = array();
	if (($o == "c" || $o == "w") && $dep_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM dependency WHERE dep_id = '".$dep_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$dep = array_map("myDecode", $DBRESULT->fetchRow());
		# Set Notification Failure Criteria
		$dep["notification_failure_criteria"] =& explode(',', $dep["notification_failure_criteria"]);
		foreach ($dep["notification_failure_criteria"] as $key => $value)
			$dep["notification_failure_criteria"][trim($value)] = 1;
		# Set Execution Failure Criteria
		$dep["execution_failure_criteria"] =& explode(',', $dep["execution_failure_criteria"]);
		foreach ($dep["execution_failure_criteria"] as $key => $value)
			$dep["execution_failure_criteria"][trim($value)] = 1;
		# Set Host Service Childs
		$DBRESULT =& $pearDB->query("SELECT * FROM dependency_serviceChild_relation dscr WHERE dscr.dependency_dep_id = '".$dep_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($service); $i++)
			$dep["dep_hSvChi"][$i] = $service["host_host_id"]."_".$service["service_service_id"];
		$DBRESULT->free();
		# Set Host Service Parents
		$DBRESULT =& $pearDB->query("SELECT * FROM dependency_serviceParent_relation dspr WHERE dspr.dependency_dep_id = '".$dep_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		for($i = 0; $DBRESULT->fetchInto($service); $i++)
			$dep["dep_hSvPar"][$i] = $service["host_host_id"]."_".$service["service_service_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Services comes from DB -> Store in $hServices Array
	
	$hServices = array();
	if ($oreon->user->admin || !HadUserLca($pearDB))
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_id, host_name FROM host WHERE host_register = '1'  ORDER BY host_name");
	else
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_id, host_name FROM host WHERE host_register = '1' AND host_id IN (".$lcaHostStr.") ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($elem))	{
		$services = getMyHostServices($elem["host_id"]);
		foreach ($services as $key=>$index)	{						
			$index = str_replace('#S#', "/", $index);
			$index = str_replace('#BS#', "\\", $index);
			$hServices[$elem["host_id"]."_".$key] = $elem["host_name"]." / ".$index;
		}
	}
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 260px; height: 200px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"30");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["dep_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["dep_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["dep_view"]);

	#
	## Dependency basic information
	#
	$form->addElement('header', 'information', $lang['dep_infos']);
	$form->addElement('text', 'dep_name', $lang["dep_name"], $attrsText);
	$form->addElement('text', 'dep_description', $lang["dep_description"], $attrsText);
	if ($oreon->user->get_version() == 2)	{
		$tab = array();
		$tab[] = &HTML_QuickForm::createElement('radio', 'inherits_parent', null, $lang['yes'], '1');
		$tab[] = &HTML_QuickForm::createElement('radio', 'inherits_parent', null, $lang['no'], '0');
		$form->addGroup($tab, 'inherits_parent', $lang["dep_inheritsP"], '&nbsp;');
	}
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', 'Ok');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unknown');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	if ($oreon->user->get_version() == 2)
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', 'Pending');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', 'None');
	$form->addGroup($tab, 'notification_failure_criteria', $lang["dep_notifFC"], '&nbsp;&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', 'Ok');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', 'Warning');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unknown');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', 'Critical');
	if ($oreon->user->get_version() == 2)
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', 'Pending');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', 'None');
	$form->addGroup($tab, 'execution_failure_criteria', $lang["dep_exeFC"], '&nbsp;&nbsp;');

	$form->addElement('textarea', 'dep_comment', $lang["dep_comment"], $attrsTextarea);
	#
	## Sort 2 Host Service Dependencies
	#
	$ams1 =& $form->addElement('advmultiselect', 'dep_hSvPar', $lang['dep_hSvPar'], $hServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

    $ams1 =& $form->addElement('advmultiselect', 'dep_hSvChi', $lang['dep_hSvChi'], $hServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'dep_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('dep_name', $lang['ErrName'], 'required');
	$form->addRule('dep_description', $lang['ErrRequired'], 'required');
	$form->registerRule('cycleH', 'callback', 'testCycleH');
	$form->addRule('dep_hSvChi', $lang['ErrCycleDef'], 'cycleH');
	$form->registerRule('exist', 'callback', 'testServiceDependencyExistence');
	$form->addRule('dep_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("sort1", $lang['dep_infos']);
	$tpl->assign("sort2", $lang['dep_sort2']);

	# Just watch a Dependency information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$dep_id."'"));
	    $form->setDefaults($dep);
		$form->freeze();
	}
	# Modify a Dependency information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($dep);
	}
	# Add a Dependency information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	$tpl->assign("nagios", $oreon->user->get_version());

	$valid = false;
	if ($form->validate())	{
		$depObj =& $form->getElement('dep_id');
		if ($form->getSubmitValue("submitA"))
			$depObj->setValue(insertServiceDependencyInDB());
		else if ($form->getSubmitValue("submitC"))
			updateServiceDependencyInDB($depObj->getValue("dep_id"));
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$depObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listServiceDependency.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formServiceDependency.ihtml");
	}
?>