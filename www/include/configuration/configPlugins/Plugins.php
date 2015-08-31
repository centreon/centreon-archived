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

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;



	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/configuration/configPlugins/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

	if ($min)
		switch ($o)	{
			case "h" : require_once($path."minHelpCommand.php"); break; #Show Help Command	# Wistof
			default : require_once($path."minCommand.php"); break;
		}
	else
		switch ($o)	{
			case "a" : require_once($path."listPlugins.php"); break; #Add a Command
			case "w" : require_once($path."listPlugins.php"); break; #Watch a Command
			case "d" : require_once($path."listPlugins.php"); break; #Delete n Commands
			default : require_once($path."listPlugins.php"); break;
		}
?>