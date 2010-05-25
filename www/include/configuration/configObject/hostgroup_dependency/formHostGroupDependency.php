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
 
 	if (!isset($oreon))
 		exit();

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
		
		# Set HostGroup Parents
		$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM dependency_hostgroupParent_relation WHERE dependency_dep_id = '".$dep_id."'");
		for($i = 0; $hgP =& $DBRESULT->fetchRow(); $i++)
			$dep["dep_hgParents"][$i] = $hgP["hostgroup_hg_id"];
		$DBRESULT->free();
		
		# Set HostGroup Childs
		$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM dependency_hostgroupChild_relation WHERE dependency_dep_id = '".$dep_id."'");
		for($i = 0; $hgC =& $DBRESULT->fetchRow(); $i++)
			$dep["dep_hgChilds"][$i] = $hgC["hostgroup_hg_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# HostGroup comes from DB -> Store in $hgs Array
	$hgs = array();
	$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name");
	while ($hg =& $DBRESULT->fetchRow())
		$hgs[$hg["hg_id"]] = $hg["hg_name"];
	$DBRESULT->free();
	
	/*
	 * Var information to format the element
	 */
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 300px; height: 150px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"30");
	$template	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

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
	$tab[] = &HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("No"), '0');
	$form->addGroup($tab, 'inherits_parent', _("Parent relationship"), '&nbsp;');

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok/Up"), array('id' => 'hUp', 'onClick' => 'uncheckAllH(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', _("Down"), array('id' => 'hDown', 'onClick' => 'uncheckAllH(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unreachable"), array('id' => 'hUnreachable', 'onClick' => 'uncheckAllH(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"), array('id' => 'hPending', 'onClick' => 'uncheckAllH(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'hNone', 'onClick' => 'uncheckAllH(this);'));
	$form->addGroup($tab, 'notification_failure_criteria', _("Notification Failure Criteria"), '&nbsp;&nbsp;');
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok/Up"));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', _("Down"));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unreachable"));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"));
	$form->addGroup($tab, 'execution_failure_criteria', _("Execution Failure Criteria"), '&nbsp;&nbsp;');

	$ams1 =& $form->addElement('advmultiselect', 'dep_hgParents', array(_("Host Groups Name"), _("Available"), _("Selected")), $hgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$ams1 =& $form->addElement('advmultiselect', 'dep_hgChilds', array(_("Dependent Host Groups Name"), _("Available"), _("Selected")), $hgs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
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

	/*
	 * Form Rules
	 */	
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('dep_name', _("Compulsory Name"), 'required');
	$form->addRule('dep_description', _("Required Field"), 'required');
	$form->addRule('dep_hgParents', _("Required Field"), 'required');
	$form->addRule('dep_hgChilds', _("Required Field"), 'required');
	
	$form->addRule('notification_failure_criteria', _("Required Field"), 'required');
	
	$form->registerRule('cycle', 'callback', 'testHostGroupDependencyCycle');
	$form->addRule('dep_hgChilds', _("Circular Definition"), 'cycle');
	$form->registerRule('exist', 'callback', 'testHostGroupDependencyExistence');
	$form->addRule('dep_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
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

	$tpl->assign("helpattr", 'TITLE, "Help", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	# prepare help texts
	$helptext = "";
	include_once("include/configuration/configObject/host_dependency/help.php");
	foreach ($help as $key => $text) { 
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	$valid = false;
	if ($form->validate())	{
		$depObj =& $form->getElement('dep_id');
		if ($form->getSubmitValue("submitA"))
			$depObj->setValue(insertHostGroupDependencyInDB());
		else if ($form->getSubmitValue("submitC"))
			updateHostGroupDependencyInDB($depObj->getValue("dep_id"));
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$depObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listHostGroupDependency.php");
	else	{
		/*
		 * Apply a template definition
		 */
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formHostGroupDependency.ihtml");
	}
?>
<script type="text/javascript">
function uncheckAllH(object) {
	if (object.id == "hNone" && object.checked) {		
		document.getElementById('hUp').checked = false;
		document.getElementById('hDown').checked = false;
		document.getElementById('hUnreachable').checked = false;
		document.getElementById('hPending').checked = false;
		if (document.getElementById('hFlapping')) {
			document.getElementById('hFlapping').checked = false;
		}		
	}	
	else {
		document.getElementById('hNone').checked = false;
	}
}
</script>
