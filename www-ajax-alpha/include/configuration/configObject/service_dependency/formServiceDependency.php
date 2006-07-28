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
	## Database retrieve information for Dependency
	#
	$dep = array();
	if (($o == "c" || $o == "w") && $dep_id)	{
		$res =& $pearDB->query("SELECT * FROM dependency WHERE dep_id = '".$dep_id."' LIMIT 1");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		# Set base value
		$dep = array_map("myDecode", $res->fetchRow());
		# Set Notification Failure Criteria
		$dep["notification_failure_criteria"] =& explode(',', $dep["notification_failure_criteria"]);
		foreach ($dep["notification_failure_criteria"] as $key => $value)
			$dep["notification_failure_criteria"][trim($value)] = 1;
		# Set Execution Failure Criteria
		$dep["execution_failure_criteria"] =& explode(',', $dep["execution_failure_criteria"]);
		foreach ($dep["execution_failure_criteria"] as $key => $value)
			$dep["execution_failure_criteria"][trim($value)] = 1;
		# Set Host Service Childs
		$res =& $pearDB->query("SELECT DISTINCT dscr.service_service_id FROM dependency_serviceChild_relation dscr, host_service_relation hsr WHERE dscr.dependency_dep_id = '".$dep_id."' AND hsr.service_service_id = dscr.service_service_id AND hsr.host_host_id IS NOT NULL GROUP BY service_service_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($service); $i++)
			$dep["dep_hSvChi"][$i] = $service["service_service_id"];
		$res->free();
		# Set HostGroup Service Childs
		$res =& $pearDB->query("SELECT DISTINCT dscr.service_service_id FROM dependency_serviceChild_relation dscr, host_service_relation hsr WHERE dscr.dependency_dep_id = '".$dep_id."' AND hsr.service_service_id = dscr.service_service_id AND hsr.hostgroup_hg_id IS NOT NULL GROUP BY service_service_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($service); $i++)
			$dep["dep_hgSvChi"][$i] = $service["service_service_id"];
		$res->free();
		# Set Host Service Parents
		$res =& $pearDB->query("SELECT DISTINCT dspr.service_service_id FROM dependency_serviceParent_relation dspr, host_service_relation hsr WHERE dspr.dependency_dep_id = '".$dep_id."' AND hsr.service_service_id = dspr.service_service_id AND hsr.host_host_id IS NOT NULL GROUP BY service_service_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($service); $i++)
			$dep["dep_hSvPar"][$i] = $service["service_service_id"];
		$res->free();
		# Set HostGroup Service Parents
		$res =& $pearDB->query("SELECT DISTINCT dspr.service_service_id FROM dependency_serviceParent_relation dspr, host_service_relation hsr WHERE dspr.dependency_dep_id = '".$dep_id."' AND hsr.service_service_id = dspr.service_service_id AND hsr.hostgroup_hg_id IS NOT NULL GROUP BY service_service_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($service); $i++)
			$dep["dep_hgSvPar"][$i] = $service["service_service_id"];
		$res->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Services comes from DB -> Store in $hServices Array and $hgServices
	$hServices = array();
	$hgServices = array();
	$res =& $pearDB->query("SELECT DISTINCT h.host_name, sv.service_description, sv.service_template_model_stm_id, sv.service_id FROM host_service_relation hsr, service sv, host h WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND h.host_id = hsr.host_host_id AND h.host_id IN (".$oreon->user->lcaHStr.") ORDER BY sv.service_description, h.host_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($elem))	{
		# If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		if (!$elem["service_description"])
			$elem["service_description"] = getMyServiceName($elem['service_template_model_stm_id']);
		if (isset($hServices[$elem["service_id"]]))
			$hServices[$elem["service_id"]] = $elem["host_name"]."&nbsp;&nbsp;&nbsp;&nbsp;(*)".$elem["service_description"];
		else
			$hServices[$elem["service_id"]] = $elem["host_name"]."&nbsp;&nbsp;&nbsp;&nbsp;".$elem["service_description"];
	}
	$res->free();
	$res =& $pearDB->query("SELECT DISTINCT hg.hg_name, sv.service_description, sv.service_template_model_stm_id, sv.service_id FROM host_service_relation hsr, service sv, hostgroup hg WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hg.hg_id = hsr.hostgroup_hg_id AND hg.hg_id IN (".$oreon->user->lcaHGStr.") ORDER BY sv.service_description, hg.hg_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($elem))	{
		# If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		if (!$elem["service_description"])
			$elem["service_description"] = getMyServiceName($elem['service_template_model_stm_id']);
		if (isset($hgServices[$elem["service_id"]]))
			$hgServices[$elem["service_id"]] = $elem["hg_name"]."&nbsp;&nbsp;&nbsp;&nbsp;(*)".$elem["service_description"];
		else
			$hgServices[$elem["service_id"]] = $elem["hg_name"]."&nbsp;&nbsp;&nbsp;&nbsp;".$elem["service_description"];
	}
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

	#
	## Sort 3 HostGroup Service Dependencies
	#
	$ams1 =& $form->addElement('advmultiselect', 'dep_hgSvPar', $lang['dep_hgSvPar'], $hgServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

    $ams1 =& $form->addElement('advmultiselect', 'dep_hgSvChi', $lang['dep_hgSvChi'], $hgServices, $attrsAdvSelect);
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
	$form->applyFilter('_ALL_', 'trim');
	$form->addRule('dep_name', $lang['ErrName'], 'required');
	$form->addRule('dep_description', $lang['ErrRequired'], 'required');
	$form->registerRule('cycleH', 'callback', 'testCycleH');
	$form->registerRule('cycleHg', 'callback', 'testCycleHg');
	$form->addRule('dep_hSvChi', $lang['ErrCycleDef'], 'cycleH');
	$form->addRule('dep_hgSvChi', $lang['ErrCycleDef'], 'cycleHg');
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
	$tpl->assign("sort3", $lang['dep_sort3']);

	$tpl->assign("legend1", $lang['legend1']);

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
		$o = "w";
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