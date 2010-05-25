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
		
		# Set Host Service Childs
		$DBRESULT =& $pearDB->query("SELECT * FROM dependency_serviceChild_relation dscr WHERE dscr.dependency_dep_id = '".$dep_id."'");
		for($i = 0; $service =& $DBRESULT->fetchRow(); $i++)
			$dep["dep_hSvChi"][$i] = $service["host_host_id"]."_".$service["service_service_id"];
		$DBRESULT->free();
		
		# Set Host Service Parents
		$DBRESULT =& $pearDB->query("SELECT * FROM dependency_serviceParent_relation dspr WHERE dspr.dependency_dep_id = '".$dep_id."'");
		for($i = 0; $service =& $DBRESULT->fetchRow(); $i++)
			$dep["dep_hSvPar"][$i] = $service["host_host_id"]."_".$service["service_service_id"];
		$DBRESULT->free();
	}

	/*
	 * Database retrieve information for differents elements list we need on the page
	 */

	/*
	 * Services comes from DB -> Store in $hServices Array
	 */
	
	$hServices = array();
	$DBRESULT =& $pearDB->query("SELECT DISTINCT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($elem =& $DBRESULT->fetchRow())	{
		$services = getMyHostServices($elem["host_id"]);
		foreach ($services as $key=>$index)	{						
			$index = str_replace('#S#', "/", $index);
			$index = str_replace('#BS#', "\\", $index);
			$hServices[$elem["host_id"]."_".$key] = $elem["host_name"]." / ".$index;
		}
	}

	/*
	 * Var information to format the element
	 */
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 400px; height: 200px;");
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

	if ($oreon->user->get_version() >= 2)	{
		$tab = array();
		$tab[] = &HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("Yes"), '1');
		$tab[] = &HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("No"), '0');
		$form->addGroup($tab, 'inherits_parent', _("Parent relationship"), '&nbsp;');
	}
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok"), array('id' => 'sOk', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"), array('id' => 'sWarning', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"), array('id' => 'sUnknown', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"), array('id' => 'sCritical', 'onClick' => 'uncheckAllS(this);'));

	if ($oreon->user->get_version() >= 2)
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"), array('id' => 'sPending', 'onClick' => 'uncheckAllS(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'sNone', 'onClick' => 'uncheckAllS(this);'));
	$form->addGroup($tab, 'notification_failure_criteria', _("Notification Failure Criteria"), '&nbsp;&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok"), array('id' => 'sOk2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'w', '&nbsp;', _("Warning"), array('id' => 'sWarning2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unknown"), array('id' => 'sUnknown2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'c', '&nbsp;', _("Critical"), array('id' => 'sCritical2', 'onClick' => 'uncheckAllS2(this);'));

	if ($oreon->user->get_version() >= 2)
		$tab[] = &HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"), array('id' => 'sPending2', 'onClick' => 'uncheckAllS2(this);'));
	$tab[] = &HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'sNone2', 'onClick' => 'uncheckAllS2(this);'));
	$form->addGroup($tab, 'execution_failure_criteria', _("Execution Failure Criteria"), '&nbsp;&nbsp;');

	$form->addElement('textarea', 'dep_comment', _("Comments"), $attrsTextarea);
	
	/*
	 * Sort 2 Host Service Dependencies
	 */
	$ams1 =& $form->addElement('advmultiselect', 'dep_hSvPar', array(_("Services"), _("Available"), _("Selected")), $hServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$ams1 =& $form->addElement('advmultiselect', 'dep_hSvChi', array(_("Dependent Services"), _("Available"), _("Selected")), $hServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

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
	$form->addRule('dep_hSvPar', _("Required Field"), 'required');
	$form->addRule('dep_hSvChi', _("Required Field"), 'required');
	$form->registerRule('cycleH', 'callback', 'testCycleH');
	$form->addRule('dep_hSvChi', _("Circular Definition"), 'cycleH');
	$form->registerRule('exist', 'callback', 'testServiceDependencyExistence');
	$form->addRule('dep_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));


	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("sort1", _("Information"));
	$tpl->assign("sort2", _("Service Description"));

	$tpl->assign("helpattr", 'TITLE, "Help", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	# prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) { 
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

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
			$depObj->setValue(insertServiceDependencyInDB());
		else if ($form->getSubmitValue("submitC"))
			updateServiceDependencyInDB($depObj->getValue("dep_id"));
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$depObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"]){
		require_once("listServiceDependency.php");
	} else {
		/*
		 * Apply a template definition
		 */
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formServiceDependency.ihtml");
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
