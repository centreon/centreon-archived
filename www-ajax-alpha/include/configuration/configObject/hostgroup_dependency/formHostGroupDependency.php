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
		# Set HostGroup Parents
		$res =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM dependency_hostgroupParent_relation WHERE dependency_dep_id = '".$dep_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($hgP); $i++)
			$dep["dep_hgParents"][$i] = $hgP["hostgroup_hg_id"];
		$res->free();
		# Set HostGroup Childs
		$res =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM dependency_hostgroupChild_relation WHERE dependency_dep_id = '".$dep_id."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		for($i = 0; $res->fetchInto($hgC); $i++)
			$dep["dep_hgChilds"][$i] = $hgC["hostgroup_hg_id"];
		$res->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# HostGroup comes from DB -> Store in $hgs Array
	$hgs = array();
	$res =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup WHERE hg_id IN (".$oreon->user->lcaHGStr.") ORDER BY hg_name");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	while($res->fetchInto($hg))
		$hgs[$hg["hg_id"]] = $hg["hg_name"];
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
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', 'Ok/Up');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', 'Down');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unreachable');
	if ($oreon->user->get_version() == 2)
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', 'Pending');
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', 'None');
	$form->addGroup($tab, 'notification_failure_criteria', $lang["dep_notifFC"], '&nbsp;&nbsp;');
	if ($oreon->user->get_version() == 2)	{
		$tab = array();
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', 'Ok/Up');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', 'Down');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', 'Unreachable');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', 'Pending');
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', 'None');
		$form->addGroup($tab, 'execution_failure_criteria', $lang["dep_exeFC"], '&nbsp;&nbsp;');
	}

	$ams1 =& $form->addElement('advmultiselect', 'dep_hgParents', $lang['dep_hgPar'], $hgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

    $ams1 =& $form->addElement('advmultiselect', 'dep_hgChilds', $lang['dep_hgChi'], $hgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$form->addElement('textarea', 'dep_comment', $lang["dep_comment"], $attrsTextarea);

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
	$form->addRule('dep_hgParents', $lang['ErrRequired'], 'required');
	$form->addRule('dep_hgChilds', $lang['ErrRequired'], 'required');
	if ($oreon->user->get_version() == 1)
		$form->addRule('notification_failure_criteria', $lang['ErrRequired'], 'required');
	$form->registerRule('cycle', 'callback', 'testHostGroupDependencyCycle');
	$form->addRule('dep_hgChilds', $lang['ErrCycleDef'], 'cycle');
	$form->registerRule('exist', 'callback', 'testHostGroupDependencyExistence');
	$form->addRule('dep_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

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
			$depObj->setValue(insertHostGroupDependencyInDB());
		else if ($form->getSubmitValue("submitC"))
			updateHostGroupDependencyInDB($depObj->getValue("dep_id"));
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$depObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listHostGroupDependency.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formHostGroupDependency.ihtml");
	}
?>