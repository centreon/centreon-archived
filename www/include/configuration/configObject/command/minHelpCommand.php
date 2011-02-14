<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 		
	if (isset($_GET["command_id"]))
		$command_id = $_GET["command_id"];
	else if (isset($_POST["command_id"]))
		$command_id = $_POST["command_id"];
	else
		$command_id = NULL;

	if (isset($_GET["command_name"]))
		$command_name = $_GET["command_name"];
	else if (isset($_POST["command_name"]))
		$command_name = $_POST["command_name"];
	else
		$command_name = NULL;

	if ($command_id != NULL){
		/*
		 * Get command informations
		 */
		$DBRESULT = $pearDB->query("SELECT * FROM `command` WHERE `command_id` = '".$command_id."' LIMIT 1");
		$cmd = $DBRESULT->fetchRow();

		$cmd_array = explode(" ", $cmd["command_line"]);
		$full_line = $cmd_array[0];
		$cmd_array = explode("#S#", $full_line);
		$resource_info = $cmd_array[0];
		$resource_def = str_replace('$', '@DOLLAR@', $resource_info);

		/*
		 * Match if the first part of the path is a MACRO
		 */
		if (preg_match("/@DOLLAR@USER([0-9]+)@DOLLAR@/", $resource_def, $matches))	{			
			/*
			 * Select Resource line
			 */
			$DBRESULT = $pearDB->query("SELECT `resource_line` FROM `cfg_resource` WHERE `resource_name` = '\$USER".$matches[1]."\$' LIMIT 1");

			$resource = $DBRESULT->fetchRow();
			unset($DBRESULT);

			$resource_path = $resource["resource_line"];			
			unset($cmd_array[0]);
			$command = rtrim($resource_path, "/")."#S#".implode("#S#", $cmd_array);
		} else {
			$command = $full_line;
		}
	} else {
		$command = $oreon->optGen["nagios_path_plugins"] . $command_name;
	}

	$command = str_replace("#S#", "/", $command);
	$stdout = shell_exec($command." --help");
	$msg = str_replace ("\n", "<br />", $stdout);

	$attrsText 	= array("size"=>"25");
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title',_("Plugin Help"));
	
	/*
	 * Command information
	 */
	$form->addElement('header', 'information', _("Help"));
	$form->addElement('text', 'command_line', _("Command Line"), $attrsText);
	$form->addElement('text', 'command_help', _("Output"), $attrsText);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('command_line', $command." --help");
	if (isset($msg) && $msg)
		$tpl->assign('msg', $msg);

	$tpl->display("minHelpCommand.ihtml");
?>