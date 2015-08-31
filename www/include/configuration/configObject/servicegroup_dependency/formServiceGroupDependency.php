<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */


	#
	## Database retrieve information for Dependency
	#
	$dep = array();
        $initialValues = array();
	if (($o == "c" || $o == "w") && $dep_id)	{
		$DBRESULT = $pearDB->query("SELECT * FROM dependency WHERE dep_id = '".$dep_id."' LIMIT 1");

		# Set base value
		$dep = array_map("myDecode", $DBRESULT->fetchRow());

		# Set Notification Failure Criteria
		$dep["notification_failure_criteria"] = explode(',', $dep["notification_failure_criteria"]);
		foreach ($dep["notification_failure_criteria"] as $key => $value)
			$dep["notification_failure_criteria"][trim($value)] = 1;

		# Set Execution Failure Criteria
		$dep["execution_failure_criteria"] = explode(',', $dep["execution_failure_criteria"]);
		foreach ($dep["execution_failure_criteria"] as $key => $value)
			$dep["execution_failure_criteria"][trim($value)] = 1;

		# Set ServiceGroup Parents
		$DBRESULT = $pearDB->query("SELECT DISTINCT servicegroup_sg_id FROM dependency_servicegroupParent_relation WHERE dependency_dep_id = '".$dep_id."'");
		for ($i = 0; $sgP = $DBRESULT->fetchRow(); $i++) {
                    if (!$oreon->user->admin && !isset($sgs[$sgP["servicegroup_sg_id"]])) {
                        $initialValues['dep_sgParents'][] = $sgP["servicegroup_sg_id"];
                    } else {
                        $dep["dep_sgParents"][$i] = $sgP["servicegroup_sg_id"];
                    }
                }
		$DBRESULT->free();

		# Set ServiceGroup Childs
		$DBRESULT = $pearDB->query("SELECT DISTINCT servicegroup_sg_id FROM dependency_servicegroupChild_relation WHERE dependency_dep_id = '".$dep_id."'");
		for ($i = 0; $sgC = $DBRESULT->fetchRow(); $i++) {
                    if (!$oreon->user->admin && !isset($sgs[$sgC["servicegroup_sg_id"]])) {
                        $initialValues['dep_sgChilds'][] = $sgC["servicegroup_sg_id"];
                    } else {
                        $dep["dep_sgChilds"][$i] = $sgC["servicegroup_sg_id"];
                    }
                }
		$DBRESULT->free();
	}

	/*
	 * Database retrieve information for differents elements list we need on the page
	 */


	/*
	 * Var information to format the element
	 */
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 300px; height: 150px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"30");
	$eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Dependency"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Dependency"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Dependency"));

	/*
	 * Dependency basic information
	 */
	$form->addElement('header', 'information', _("Information"));
	$form->addElement('text', 'dep_name', _("Name"), $attrsText);
	$form->addElement('text', 'dep_description', _("Description"), $attrsText);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("No"), '0');
	$form->addGroup($tab, 'inherits_parent', _("Parent relationship"), '&nbsp;');
	$form->setDefaults(array('inherits_parent'=>'1'));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok"), array('id' => 'sOk', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"), array('id' => 'sWarning', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"), array('id' => 'sUnknown', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"), array('id' => 'sCritical', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"), array('id' => 'sPending', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'sNone', 'onClick' => 'uncheckAllS(this);'));
	$form->addGroup($tab, 'notification_failure_criteria', _("Notification Failure Criteria"), '&nbsp;&nbsp;');

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok"), array('id' => 'sOk2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"), array('id' => 'sWarning2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"), array('id' => 'sUnknown2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"), array('id' => 'sCritical2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"), array('id' => 'sPending2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'sNone2', 'onClick' => 'uncheckAllS2(this);'));
	$form->addGroup($tab, 'execution_failure_criteria', _("Execution Failure Criteria"), '&nbsp;&nbsp;');

	$ams1 = $form->addElement('advmultiselect', 'dep_sgParents', array(_("Service Group Names"), _("Available"), _("Selected")), $sgs, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	$ams1 = $form->addElement('advmultiselect', 'dep_sgChilds', array(_("Dependent Service Group Names"), _("Available"), _("Selected")), $sgs, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	$form->addElement('textarea', 'dep_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'dep_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

        $init = $form->addElement('hidden', 'initialValues');
        $init->setValue(serialize($initialValues));
        
	/*
	 * Form Rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('dep_name', _("Compulsory Name"), 'required');
	$form->addRule('dep_description', _("Required Field"), 'required');
	$form->addRule('dep_sgParents', _("Required Field"), 'required');
	$form->addRule('dep_sgChilds', _("Required Field"), 'required');

	$form->addRule('notification_failure_criteria', _("Required Field"), 'required');

	$form->registerRule('cycle', 'callback', 'testServiceGroupDependencyCycle');
	$form->addRule('dep_sgChilds', _("Circular Definition"), 'cycle');
	$form->registerRule('exist', 'callback', 'testServiceGroupDependencyExistence');
	$form->addRule('dep_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Just watch a Dependency information
	 */
	if ($o == "w")	{
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$dep_id."'"));
	    $form->setDefaults($dep);
		$form->freeze();
	} else if ($o == "c")	{
		# Modify a Dependency information
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($dep);
	} else if ($o == "a")	{
		# Add a Dependency information
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
		$form->setDefaults(array('inherits_parent', '0'));
	}
	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	# prepare help texts
	$helptext = "";
	include_once("include/configuration/configObject/service_dependency/help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	$valid = false;
	if ($form->validate())	{
		$depObj = $form->getElement('dep_id');
		if ($form->getSubmitValue("submitA"))
			$depObj->setValue(insertServiceGroupDependencyInDB());
		else if ($form->getSubmitValue("submitC"))
			updateServiceGroupDependencyInDB($depObj->getValue("dep_id"));
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$depObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}

	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"])
		require_once("listServiceGroupDependency.php");
	else {
		/*
		 * Apply a template definition
		 */
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formServiceGroupDependency.ihtml");
	}
?>
<script type="text/javascript">
function uncheckAllS(object) {
	if (object.id == "sNone" && object.checked) {
		document.getElementById('sOk').checked = false;
		document.getElementById('sWarning').checked = false;
		document.getElementById('sUnknown').checked = false;
		document.getElementById('sCritical').checked = false;
		document.getElementById('sPending').checked = false;
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
		document.getElementById('sPending2').checked = false;
	}
	else {
		document.getElementById('sNone2').checked = false;
	}
}
</script>
