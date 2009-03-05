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
 
 	if (!isset($oreon))
 		exit();

	$cmd = array("command_type"=>null, "command_name"=>null, "command_line"=>null);
	if (isset($_POST["command_id1"]) && $_POST["command_id1"])
		$command_id = $_POST["command_id1"];
	else if (isset($_POST["command_id2"]) && $_POST["command_id2"])
		$command_id = $_POST["command_id2"];

	if ($o == "w" && $command_id)	{

		function myDecodeCommand($arg)	{
			$arg = html_entity_decode($arg, ENT_QUOTES);
			$arg = str_replace('#BR#', "\\n", $arg);
			$arg = str_replace('#T#', "\\t", $arg);
			$arg = str_replace('#R#', "\\r", $arg);
			$arg = str_replace('#S#', "/", $arg);
			$arg = str_replace('#BS#', "\\", $arg);
			return($arg);
		}

		$DBRESULT =& $pearDB->query("SELECT * FROM `command` WHERE `command_id` = '".$command_id."' LIMIT 1");
		if ($DBRESULT->numRows())
			$cmd = array_map("myDecodeCommand", $DBRESULT->fetchRow());
	}

	/*
	 * Notification commands comes from DB -> Store in $notifCmds Array
	 */
	$notifCmds = array(null=>null);
	$DBRESULT =& $pearDB->query("SELECT `command_id`, `command_name` FROM `command` WHERE `command_type` = '1' ORDER BY `command_name`");
	while ($notifCmd =& $DBRESULT->fetchRow())
		$notifCmds[$notifCmd["command_id"]] = $notifCmd["command_name"];
	$DBRESULT->free();
	
	/*
	 * Check commands comes from DB -> Store in $checkCmds Array
	 */
	
	$checkCmds = array(null=>null);
	$DBRESULT =& $pearDB->query("SELECT `command_id`, `command_name` FROM `command` WHERE `command_type` = '2' ORDER BY `command_name`");
	while ($checkCmd =& $DBRESULT->fetchRow())
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();


	$attrsText 		= array("size"=>"35");
	$attrsTextarea 	= array("rows"=>"9", "cols"=>"80");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("View a Command"));

	/*
	 * Command information
	 */
	if ($cmd["command_type"] == "1")
		$form->addElement('header', 'information', _("Notification"));
	else if ($cmd["command_type"] == "2")
		$form->addElement('header', 'information', _("Check"));
	else
		$form->addElement('header', 'information', _("Information"));
	
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, _("Notification"), '1');
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, _("Check"), '2');
	
	$v1 =& $form->addGroup($cmdType, 'command_type', _("Command Type"), '&nbsp;&nbsp;');
	$v1->freeze();
	
	$v2 =& $form->addElement('text', 'command_name', _("Command Name"), $attrsText);
	$v2->freeze();
	
	$v3 =& $form->addElement('textarea', 'command_line', _("Command Line"), $attrsTextarea);
	$v3->freeze();
	
	/*
	 * Command Select
	 */
    $form->addElement('select', 'command_id1', _("Check"), $checkCmds, array("onChange"=>"this.form.submit()"));
    $form->addElement('select', 'command_id2', _("Notif"), $notifCmds, array("onChange"=>"this.form.submit()"));
	$form->setConstants(array("command_name"=>$cmd["command_name"], "command_line"=>$cmd["command_line"], "command_type"=>$cmd["command_type"]["command_type"]));
  	
  	/*
  	 * Further informations
  	 */
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	$min =& $form->addElement('hidden', 'min');
	$min->setValue(1);
	

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	/*
	 * Apply a template definition
	 */	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');

	$form->accept($renderer);

	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('command_id', $command_id);
	$tpl->assign('command_name', $cmd["command_name"]);

	$tpl->display("minCommand.ihtml");
?>