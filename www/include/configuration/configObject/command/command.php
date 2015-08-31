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
 
	if (!isset ($oreon))
		exit ();

	isset($_GET["command_id"]) ? $cmdG = $_GET["command_id"] : $cmdG = NULL;
	isset($_POST["command_id"]) ? $cmdP = $_POST["command_id"] : $cmdP = NULL;
	$cmdG ? $command_id = $cmdG : $command_id = $cmdP;

	isset($_GET["type"]) ? $typeG = $_GET["type"] : $typeG = NULL;
	isset($_POST["type"]) ? $typeP = $_POST["type"] : $typeP = NULL;
	$typeG ? $type = $typeG : $type = $typeP;
	
	if (!isset($type) || !$type)
		$type = 2;
	
	isset($_POST["command_type"]) ? $type = $_POST["command_type"]["command_type"] : null;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/configuration/configObject/command/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	if (isset($_POST["o1"]) && isset($_POST["o2"])){
		if ($_POST["o1"] != "")
			$o = $_POST["o1"];
		if ($_POST["o2"] != "")
			$o = $_POST["o2"];
	}
	
	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

	if ($min) {
		switch ($o)	{
			case "h" 	:
				/*
				 * Show Help Command
				 */ 
				require_once($path."minHelpCommand.php"); 
				break; 
			case "p" 	: 
				/*
				 * Test the plugin
				 */
				require_once($path."minPlayCommand.php"); 
				break; 
			default 	: 
				require_once($path."minCommand.php"); 
				break;
		}
	} else {
		switch ($o)	{
			case "a" 	: 
				/*
				 * Add a Command
				 */
				require_once($path."formCommand.php"); 
				break;
			case "w" 	: 
				/*
				 * Watch a Command
				 */
				require_once($path."formCommand.php"); 
				break;
			case "c" 	:
				/*
				 * Modify a Command
				 */ 
				require_once($path."formCommand.php"); 
				break; 
			case "m" 	:
				/*
				 * Duplicate n Commands
				 */ 
				multipleCommandInDB(isset($select) ? $select : array(), $dupNbr); 
				require_once($path."listCommand.php"); 
				break; 
			case "d" 	:
				/*
				 * Delete n Commands
				 */ 
				deleteCommandInDB(isset($select) ? $select : array()); 
				require_once($path."listCommand.php"); 
				break;
			default 	: 
				require_once($path."listCommand.php"); 
				break;
		}
	}
?>