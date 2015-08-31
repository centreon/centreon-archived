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

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle1 = create_file($nagiosCFGPath.$tab['id']."/misccommands.cfg", $oreon->user->get_name());
	$handle2 = create_file($nagiosCFGPath.$tab['id']."/checkcommands.cfg", $oreon->user->get_name());

	/*
	 * Define preg arguments
	 */
    $slashesOri = array('/#BR#/',
				    	'/#T#/',
    				    '/#R#/',
    				    '/#S#/',
    				    '/#BS#/',
    				    '/#P#/');
	$slashesRep = array("\\n",
    				    "\\t",
                        "\\r",
                        "/",
                        "\\",
                        "|");

	$DBRESULT = $pearDB->query('SELECT * FROM `command` ORDER BY `command_type`,`command_name`');
	$command = array();
	$i1 = 1;
	$i2 = 1;
	$str1 = NULL;
	$str2 = NULL;
	while ($command = $DBRESULT->fetchRow())	{
	    if (isset($command['command_line'])) {
	        $command['command_line'] = trim(preg_replace($slashesOri, $slashesRep, $command['command_line']));
	    }

		if ($command["command_comment"] != NULL) {
			$command["command_comment"] = trim(preg_replace($slashesOri,$slashesRep,$command["command_comment"]));
		}

        
        
        
        // Ajoute un connecteur si nécessaire
        if ($tab['monitoring_engine'] == 'CENGINE')
        {
            $connectorLine = "";
            if ($command["connector_id"] != NULL)
            {
                $DBRESULT2 = $pearDB->query("SELECT `name` FROM `connector` WHERE `connector`.`id` = '".$command["connector_id"]."'");
                if (!PEAR::isError($DBRESULT2))
                {
                    $connector = $DBRESULT2->fetchRow();
                    $connectorLine = print_line("connector", $connector["name"]);
                    unset($DBRESULT2);
                }
            }
        }
        
        
		if ($command["command_type"] == 1 || $command["command_type"] == 3)	{
			/*
			 * Notification Command case -> command_type == 1
			 */
			if ($ret["comment"]) {
				$str1 .= "; '" . $command["command_name"] . "' command definition " . $i1 . "\n";
				if ($command["command_comment"] != "") {
                    $t = explode("\n", $command["command_comment"]);
                    foreach ($t as $comment) {
                        $str1 .= "; ". trim($comment) ."\n";
                    }
				}
			}

			$str1 .= "define command{\n";
			if ($command["command_name"]) {
				$str1 .= print_line("command_name", $command["command_name"]);
			}
			if ($command["command_line"]) {
                $commandLineTmp = str_replace("@MAILER@", $oreon->optGen["mailer_path_bin"], $command["command_line"]);
                if ($tab['monitoring_engine'] == 'CENGINE' && $command['enable_shell']) {
                    $commandLineTmp = '/bin/sh -c ' . escapeshellarg($commandLineTmp);
                }
			    $str1 .= print_line("command_line", $commandLineTmp);
			}
			if ($command["command_example"]) {
				$str1 .= print_line(";command_example", $command["command_example"]);
			}

			/*
			 * Display arguments used in the command line.
			 */
			$DBRESULT2 = $pearDB->query("SELECT macro_name, macro_description FROM command_arg_description WHERE cmd_id = '".$command["command_id"]."' ORDER BY macro_name");
			while ($args = $DBRESULT2->fetchRow())	{
				$str2 .= print_line(";\$".$args["macro_name"]."\$", $args["macro_description"]);
			}
			$DBRESULT2->free();
			unset($args);
            
            if (isset($connectorLine) && !empty($connectorLine))
                $str1 .= $connectorLine;

			$str1 .= "}\n\n";
			$i1++;
		} else if ($command["command_type"] == 2) {
			/*
			 * Check Command case -> command_type == 2
			 */

			if ($ret["comment"]) {
				$str2 .= "; '" . $command["command_name"] . "' command definition " . $i2 . "\n";
				if ($command["command_comment"] != "") {
                    $t = explode("\n", $command["command_comment"]);
                    foreach ($t as $comment) {
                        $str2 .= "; ". trim($comment) ."\n";
                    }
				}
			}

			$str2 .= "define command{\n";
			if ($command["command_name"]) {
				$str2 .= print_line("command_name", $command["command_name"]);
			}
			if ($command["command_line"]) {
                $commandLineTmp = str_replace("@MAILER@", $oreon->optGen["mailer_path_bin"], $command["command_line"]);
                if ($tab['monitoring_engine'] == 'CENGINE' && $command['enable_shell']) {
                    $commandLineTmp = '/bin/sh -c ' . escapeshellarg($commandLineTmp);
                }
			    $str2 .= print_line("command_line", $commandLineTmp);
			}
			if ($command["command_example"]) {
				$str2 .= print_line(";command_example", $command["command_example"]);
			}

			/*
			 * Display arguments used in the command line.
			 */
			$DBRESULT2 = $pearDB->query("SELECT macro_name, macro_description FROM command_arg_description WHERE cmd_id = '".$command["command_id"]."' ORDER BY macro_name");
			while ($args = $DBRESULT2->fetchRow())	{
				$str2 .= print_line(";\$".$args["macro_name"]."\$", $args["macro_description"]);
			}
			$DBRESULT2->free();
			unset($args);
            
            if (isset($connectorLine))
                $str2 .= $connectorLine;

			$str2 .= "}\n\n";
			$i2++;
		}
	}
	write_in_file($handle1, html_entity_decode($str1, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/misccommands.cfg");
	write_in_file($handle2, html_entity_decode($str2, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/checkcommands.cfg");
	fclose($handle1);
	fclose($handle2);
	
	setFileMod($nagiosCFGPath.$tab['id']."/misccommands.cfg");
	setFileMod($nagiosCFGPath.$tab['id']."/checkcommands.cfg");
	
	$DBRESULT->free();
	unset($str1);
	unset($str2);
?>
