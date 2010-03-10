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

	#
	## Database retrieve information for Dependency
	#
	$dep = array();
	if (($o == "c" || $o == "w") && $dep_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM dependency WHERE dep_id = '".$dep_id."' LIMIT 1");
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
		# Set Meta Service Parents
		$DBRESULT =& $pearDB->query("SELECT DISTINCT meta_service_meta_id FROM dependency_metaserviceParent_relation WHERE dependency_dep_id = '".$dep_id."'");
		for($i = 0; $msP =& $DBRESULT->fetchRow(); $i++)
			$dep["dep_msParents"][$i] = $msP["meta_service_meta_id"];
		$DBRESULT->free();
		# Set Meta Service Childs
		$DBRESULT =& $pearDB->query("SELECT DISTINCT meta_service_meta_id FROM dependency_metaserviceChild_relation WHERE dependency_dep_id = '".$dep_id."'");
		for($i = 0; $msC =& $DBRESULT->fetchRow(); $i++)
			$dep["dep_msChilds"][$i] = $msC["meta_service_meta_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Meta Service comes from DB -> Store in $metas Array
	$metas = array();
	$DBRESULT =& $pearDB->query("SELECT meta_id, meta_name FROM meta_service ORDER BY meta_name");
	while($meta =& $DBRESULT->fetchRow())
		$metas[$meta["meta_id"]] = $meta["meta_name"];
	$DBRESULT->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 300px; height: 150px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"30");
	$template	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Dependency"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Dependency"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Dependency"));

	#
	## Dependency basic information
	#
	$form->addElement('header', 'information', _("Information"));
	$form->addElement('text', 'dep_name', _("Name"), $attrsText);
	$form->addElement('text', 'dep_description', _("Description"), $attrsText);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("No"), '0');
	$form->addGroup($tab, 'inherits_parent', _("Parent relationship"), '&nbsp;');

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok"), array('id' => 'sOk', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"), array('id' => 'sWarning', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"), array('id' => 'sUnknown', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"), array('id' => 'sCritical', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"), array('id' => 'sPending', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'sNone', 'onClick' => 'uncheckAllS(this);'));
	$form->addGroup($tab, 'notification_failure_criteria', _("Notification Failure Criteria"), '&nbsp;&nbsp;');
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok"), array('id' => 'sOk2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"), array('id' => 'sWarning2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"), array('id' => 'sUnknown2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"), array('id' => 'sCritical2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"), array('id' => 'sPending2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'sNone2', 'onClick' => 'uncheckAllS2(this);'));
	$form->addGroup($tab, 'execution_failure_criteria', _("Execution Failure Criteria"), '&nbsp;&nbsp;');

	$ams1 =& $form->addElement('advmultiselect', 'dep_msParents', array(_("Meta Service Names"), _("Available"), _("Selected")), $metas, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$ams1 =& $form->addElement('advmultiselect', 'dep_msChilds', array(_("Dependent Meta Service Names"), _("Available"), _("Selected")), $metas, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$form->addElement('textarea', 'dep_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'dep_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('dep_name', _("Compulsory Name"), 'required');
	$form->addRule('dep_description', _("Required Field"), 'required');
	$form->addRule('dep_msParents', _("Required Field"), 'required');
	$form->addRule('dep_msChilds', _("Required Field"), 'required');
	$form->registerRule('cycle', 'callback', 'testCycle');
	$form->addRule('dep_msChilds', _("Circular Definition"), 'cycle');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('dep_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a Dependency information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$dep_id."'"));
	    $form->setDefaults($dep);
		$form->freeze();
	}
	# Modify a Dependency information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($dep);
	}
	# Add a Dependency information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	$tpl->assign("nagios", $oreon->user->get_version());

	$valid = false;
	if ($form->validate())	{
		$depObj =& $form->getElement('dep_id');
		if ($form->getSubmitValue("submitA"))
			$depObj->setValue(insertMetaServiceDependencyInDB());
		else if ($form->getSubmitValue("submitC"))
			updateMetaServiceDependencyInDB($depObj->getValue("dep_id"));
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$depObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listMetaServiceDependency.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formMetaServiceDependency.ihtml");
	}
?>
<script type="text/javascript">
function uncheckAllS(object) {	
	if (object.id == "sNone" && object.checked) {
		document.getElementById('sOk').checked = false;	
		document.getElementById('sWarning').checked = false;
		document.getElementById('sUnknown').checked = false;
		document.getElementById('sCritical').checked = false;
		document.getElementById('sRecovery').checked = false;		
	}
	else {
		document.getElementById('sNone').checked = false;
	}
}
function uncheckAllS2(object) {	
	if (object.id == "sNone2" && object.checked) {
		document.getElementById('sOk2').checked = false;	
		document.getElementById('sWarning2').checked = false;
		document.getElementById('sUnknown2').checked = false;
		document.getElementById('sCritical2').checked = false;
		document.getElementById('sRecovery2').checked = false;		
	}
	else {
		document.getElementById('sNone2').checked = false;
	}
}
</script>
