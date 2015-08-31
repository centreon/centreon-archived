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

  	if (!isset ($oreon))
		exit ();

	global $centreon_path;
	require_once($centreon_path . "www/include/common/common-Func.php");

 	$error_msg = "";
	$command = $_GET["command_line"];
	$example = $_GET["command_example"];
	$args = preg_split("/\!/", $example);

	for ($i = 0; $i < count($args); $i++)
	    $args[$i] = escapeshellarg ($args[$i]);
	$resource_def = str_replace('$', '@DOLLAR@', $command);
	$resource_def = escapeshellcmd($resource_def);

	while (preg_match("/@DOLLAR@USER([0-9]+)@DOLLAR@/", $resource_def, $matches) and $error_msg == "")	{
		$DBRESULT = $pearDB->query("SELECT resource_line FROM cfg_resource WHERE resource_name = '\$USER".$matches[1]."\$' LIMIT 1");
		$resource = $DBRESULT->fetchRow();
		if (!isset($resource["resource_line"])){
			$error_msg .= "\$USER".$matches[1]."\$";
		} else {
			$resource_def = str_replace("@DOLLAR@USER". $matches[1] ."@DOLLAR@", $resource["resource_line"], $resource_def);
		}
	}

	while (preg_match("/@DOLLAR@HOSTADDRESS@DOLLAR@/", $resource_def, $matches) and $error_msg == "")	{
		if (isset($_GET["command_hostaddress"]) && $_GET["command_hostaddress"] != "") {
			$resource_def = str_replace("@DOLLAR@HOSTADDRESS@DOLLAR@", $_GET["command_hostaddress"], $resource_def);
		} else {
			$error_msg .= "\$HOSTADDRESS\$";
		}
	}
	
	while (preg_match("/@DOLLAR@ARG([0-9]+)@DOLLAR@/", $resource_def, $matches) and $error_msg == "")	{
		$match_id = $matches[1];
		if (isset($args[$match_id])){
			$resource_def = str_replace("@DOLLAR@ARG". $match_id ."@DOLLAR@", $args[$match_id], $resource_def);
			$resource_def = str_replace('$', '@DOLLAR@', $resource_def);
			if (preg_match("/@DOLLAR@USER([0-9]+)@DOLLAR@/", $resource_def, $matches)) {
				$DBRESULT = $pearDB->query("SELECT resource_line FROM cfg_resource WHERE resource_name = '\$USER".$matches[1]."\$' LIMIT 1");
				$resource = $DBRESULT->fetchRow();
				if (!isset($resource["resource_line"])){
					$error_msg .= "\$USER".$match_id."\$";
				} else {
					$resource_def = str_replace("@DOLLAR@USER". $matches[1] ."@DOLLAR@", $resource["resource_line"], $resource_def);
				}
			}
			if (preg_match("/@DOLLAR@HOSTADDRESS@DOLLAR@/", $resource_def, $matches)) {
				if (isset($_GET["command_hostaddress"])){
					$resource_def = str_replace("@DOLLAR@HOSTADDRESS@DOLLAR@", $_GET["command_hostaddress"], $resource_def);
				} else {
					$error_msg .= "\$HOSTADDRESS\$";
				}
			}			
		} else {
			$error_msg = "\$USER" . $match_id . "\$";
		}
	}

	if ($error_msg != "") {
		$command = $resource_def;
		$command = str_replace('@DOLLAR@', '$', $command);
		$msg = _("Could not find macro ") . $error_msg;
		$status = _("ERROR");
	} else {
		$command = $resource_def;
		$command = str_replace('@DOLLAR@', '$', $command);
		$splitter = preg_split("/\;/", $command);
		$command = $splitter[0];
		$stdout = array();
		unset($stdout);

		/*
		 * for security reasons, we do not allow the execution of any command unless it is located in path $USER1$
		 */
		$DBRESULT = $pearDB->query("SELECT `resource_line` FROM `cfg_resource` WHERE `resource_name` = '\$USER1\$' LIMIT 1");
		$resource = $DBRESULT->fetchRow();
		$user1Path = $resource["resource_line"];
		$pathMatch = str_replace('/', '\/', $user1Path);

		if (preg_match("/^$pathMatch/", $command)){
                    if (preg_match("/\.\./", $command)) {
                        $msg = _("Directory traversal detected");
                    } else {
                        $msg = exec($command, $stdout, $status);
                        $msg = join("<br/>",$stdout);
                        if ($status == 1) {
                            $status = _("WARNING");
                        } elseif ($status == 2) {
                            $status = _("CRITICAL");
                        } elseif ($status == 0) {
                                    $status = _("OK");
                        } else {
                            $status = _("UNKNOWN");
                        }
                    }
		} else {
                    $msg = _("Plugin has to be in : ") . $user1Path;
		}
	}

	$attrsText 	= array("size" => "25");
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title',_("Plugin Test"));

	/*
	 * Command information
	 */
	$form->addElement('header', 'information', _("Plugin test"));
	$form->addElement('text', 	'command_line', _("Command Line"), $attrsText);
	$form->addElement('text', 	'command_help', _("Output"), $attrsText);
	$form->addElement('text', 	'command_status', _("Status"), $attrsText);

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
	$tpl->assign('command_line', $command);

	if (isset($msg) && $msg)
		$tpl->assign('msg', $msg);

	if (isset($status))
		$tpl->assign('status', $status);

	$tpl->display("minPlayCommand.ihtml");
?>