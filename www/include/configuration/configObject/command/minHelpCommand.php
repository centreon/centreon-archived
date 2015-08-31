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

 	if (!isset($oreon)) {
 		exit();
 	}

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
		$cmd_array = explode("/", $full_line);
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
	$command = str_replace("#BS#", "\\", $command);

	if (strncmp($command, "/usr/lib/nagios/", strlen("/usr/lib/nagios/"))) {
	    if (is_dir("/usr/lib64/nagios/")) {
	        $command = str_replace("/usr/lib/nagios/plugins/", "/usr/lib64/nagios/plugins/", $command);
	        $oreon->optGen["nagios_path_plugins"] = str_replace("/usr/lib/nagios/plugins/", "/usr/lib64/nagios/plugins/", $oreon->optGen["nagios_path_plugins"]);
	    }
	}

	$tab = explode(' ', $command);
	if (strncmp(realpath($tab[0]), $oreon->optGen["nagios_path_plugins"], strlen($oreon->optGen["nagios_path_plugins"]))) {
        $msg = _('Error: Cannot Execute this command due to an path security problem.');
        $command = realpath($tab[0]);
   	} else {
   	    $command = realpath($tab[0]);
    	$stdout = shell_exec(realpath($tab[0])." --help");
    	$msg = str_replace ("\n", "<br />", $stdout);
	}

	$attrsText 	= array("size" => "25");
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Plugin Help"));

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
	if (isset($msg) && $msg) {
		$tpl->assign('msg', $msg);
	}

	$tpl->display("minHelpCommand.ihtml");
?>
