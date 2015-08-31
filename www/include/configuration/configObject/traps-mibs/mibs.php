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

	isset($_GET["id"]) ? $mnftrG = $_GET["id"] : $mnftrG = NULL;
	isset($_POST["id"]) ? $mnftrP = $_POST["id"] : $mnftrP = NULL;
	$mnftrG ? $id = $mnftrG : $id = $mnftrP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/configuration/configObject/traps-mibs/";
	//$nagios_plugins = "/etc/nagios/libexec";

	#PHP functions
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formMibs.php"); break; #Show command execution
		default : require_once($path."formMibs.php"); break;
	}
?>