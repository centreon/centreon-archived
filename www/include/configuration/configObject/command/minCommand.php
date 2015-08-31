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
 
 	if (!isset($oreon))
 		exit();

	$cmd = array("command_type"=>null, "command_name"=>null, "command_line"=>null);
	if (isset($_POST["command_id1"]) && $_POST["command_id1"])
		$command_id = $_POST["command_id1"];
	else if (isset($_POST["command_id2"]) && $_POST["command_id2"])
		$command_id = $_POST["command_id2"];

	if ($o == "w" && $command_id)	{

		if (!function_exists("myDecodeCommand")) {
			function myDecodeCommand($arg)	{
				$arg = str_replace('#BR#', "\\n", $arg);
				$arg = str_replace('#T#', "\\t", $arg);
				$arg = str_replace('#R#', "\\r", $arg);
				$arg = str_replace('#S#', "/", $arg);
				$arg = str_replace('#BS#', "\\", $arg);
				return($arg);
			}
		}

		$DBRESULT = $pearDB->query("SELECT * FROM `command` WHERE `command_id` = '".$command_id."' LIMIT 1");
		if ($DBRESULT->numRows())
			$cmd = array_map("myDecodeCommand", $DBRESULT->fetchRow());
	}

	/*
	 * Notification commands comes from DB -> Store in $notifCmds Array
	 */
	$notifCmds = array(null=>null);
	$DBRESULT = $pearDB->query("SELECT `command_id`, `command_name` FROM `command` WHERE `command_type` = '1' ORDER BY `command_name`");
	while ($notifCmd = $DBRESULT->fetchRow())
		$notifCmds[$notifCmd["command_id"]] = $notifCmd["command_name"];
	$DBRESULT->free();
	
	/*
	 * Check commands comes from DB -> Store in $checkCmds Array
	 */
	
	$checkCmds = array(null=>null);
	$DBRESULT = $pearDB->query("SELECT `command_id`, `command_name` FROM `command` WHERE `command_type` = '2' ORDER BY `command_name`");
	while ($checkCmd = $DBRESULT->fetchRow())
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();


	$attrsText 	= array("size"=>"35");
	$attrsTextarea 	= array("rows"=>"9", "cols"=>"80");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("View command definition"));

	/*
	 * Command information
	 */
	if ($cmd["command_type"] == "1") {
		$form->addElement('header', 'information', _("Notification command"));
		$elemname = "command_id2";
	} else if ($cmd["command_type"] == "2") {
		$form->addElement('header', 'information', _("Check command"));
		$elemname = "command_id2";
	} else if ($cmd["command_type"] == "3") {
		$form->addElement('header', 'information', _("Information command"));
	} else {
		$form->addElement('header', 'information', _("No command selected"));
	}
	
	
	$cmdType[] = HTML_QuickForm::createElement('radio', 'command_type', null, _("Notification"), '1');
	$cmdType[] = HTML_QuickForm::createElement('radio', 'command_type', null, _("Check"), '2');
	
	$v1 = $form->addGroup($cmdType, 'command_type', _("Command Type"), '&nbsp;&nbsp;');
	$v1->freeze();
	
	$v2 = $form->addElement('text', 'command_name', _("Command Name"), $attrsText);
	$v2->freeze();
	
	$v3 = $form->addElement('textarea', 'command_line', _("Command Line"), $attrsTextarea);
	$v3->freeze();
	
	/*
	 * Command Select
	 */
	$form->addElement('select', 'command_id1', _("Check"), $checkCmds, array("onChange"=>"this.form.submit()"));
	$form->addElement('select', 'command_id2', _("Notif"), $notifCmds, array("onChange"=>"this.form.submit()"));
	$form->setConstants(array("command_name"=>$cmd["command_name"], "command_line"=>$cmd["command_line"], "command_type"=>$cmd["command_type"]["command_type"]));
	
	$form->setDefaults(array("command_id1"=>$cmd["command_id"]));
  	
  	/*
  	 * Further informations
  	 */
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	$min = $form->addElement('hidden', 'min');
	$min->setValue(1);
	

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	/*
	 * Apply a template definition
	 */	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');

	$form->accept($renderer);

	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('command_id', $command_id);
	$tpl->assign('command_name', $cmd["command_name"]);

	$tpl->display("minCommand.ihtml");
?>