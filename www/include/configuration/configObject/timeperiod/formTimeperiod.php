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
 
	if (!isset($centreon))
		exit();
		
	$tp = array();
	if (($o == "c" || $o == "w") && $tp_id)	{	
		$DBRESULT =& $pearDB->query("SELECT * FROM timeperiod WHERE tp_id = '".$tp_id."' LIMIT 1");
		
		/*
		 * Set base value
		 */
		$tp = array_map("myDecode", $DBRESULT->fetchRow());
		$tp["contact_exclude"] = array();
	}
	
	$excludeTP = array();
	$DBRESULT =& $pearDB->query("SELECT tp_name, tp_id FROM timeperiod");
	while ($data =& $DBRESULT->fetchRow())
		if ($o != "a" || $tp_id != $data["tp_id"])
			$excludeTP[$data["tp_id"]] = $data["tp_name"];
	$DBRESULT->free();
	unset($data);
	
	/*
	 * Var information to format the element
	 */
	$attrsText 		= array("size"=>"35");
	$attrsAdvSelect = array("style" => "width: 300px; height: 130px;");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Time Period"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Time Period"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Time Period"));

	/*
	 * 	Time Period basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'tp_name', _("Time Period Name"), $attrsText);
	$form->addElement('text', 'tp_alias', _("Alias"), $attrsText);
	
	/*
	 * Notification informations
	 */
	$form->addElement('header', 'notification', _("Notification Time Range"));
	$form->addElement('header', 'notification_base', _("Basic Notification"));
	$form->addElement('header', 'exclude', _("Time Range exclusion"));
	$form->addElement('header', 'exception', _("Time Range exceptions"));
	
	$form->addElement('text', 'tp_sunday', _("Sunday"), $attrsText);
	$form->addElement('text', 'tp_monday', _("Monday"), $attrsText);
	$form->addElement('text', 'tp_tuesday', _("Tuesday"), $attrsText);
	$form->addElement('text', 'tp_wednesday', _("Wednesday"), $attrsText);
	$form->addElement('text', 'tp_thursday', _("Thursday"), $attrsText);
	$form->addElement('text', 'tp_friday', _("Friday"), $attrsText);
	$form->addElement('text', 'tp_saturday', _("Saturday"), $attrsText);
	
	/*
	 * Exclude Timeperiod
	 */
	$ams3 =& $form->addElement('advmultiselect', 'tp_exclude', _("Exclude Timeperiods"), $excludeTP, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	/*
	 * Include javascript for dynamique entries
	 */
	require_once "./include/configuration/configObject/timeperiod/timeperiod_JS.php";

	/*
	 *  Host multiple templates relations stored in DB
	 */	
	$mTp = array();
	$k = 0;
	$DBRESULT =& $pearDB->query("SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = '". $host_id ."' ORDER BY `order`");
	while ($multiTp =& $DBRESULT->fetchRow()){
		$mTp[$k] = $multiTp["host_tpl_id"];
		$k++;
	}
	$DBRESULT->free();
	
	
	/*
	 * Further informations
	 */
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'tp_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	/*
	 * Form Rules
	 */
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["tp_name"]));
	}
	
	/*
	 * Set rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('tp_name', 'myReplace');
	$form->addRule('tp_name', _("Compulsory Name"), 'required');
	$form->addRule('tp_alias', _("Compulsory Alias"), 'required');
	$form->registerRule('exist', 'callback', 'testTPExistence');
	$form->addRule('tp_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	if ($o == "w")	{
		# Just watch a Time Period information
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&tp_id=".$tp_id."'"));
	    $form->setDefaults($tp);
		$form->freeze();
	} else if ($o == "c")	{
		# Modify a Time Period information
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($tp);
	} else if ($o == "a")	{
		# Add a Time Period information
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	/*
	 * Translations
	 */
	$tpl->assign("tRDay", _("Days"));
	$tpl->assign("tRHours", _("Timerange"));

	$valid = false;
	if ($form->validate())	{
		$tpObj =& $form->getElement('tp_id');
		if ($form->getSubmitValue("submitA"))
			$tpObj->setValue(insertTimeperiodInDB());
		else if ($form->getSubmitValue("submitC"))
			updateTimeperiodInDB($tpObj->getValue());
		$o = NULL;		
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&tp_id=".$tpObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listTimeperiod.php");
	else {
		/*
		 * Apply a template definition
		 */
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formTimeperiod.ihtml");
	}
?><script type="text/javascript">
		displayExistingExceptions(<?php echo $k;?>);
</script>