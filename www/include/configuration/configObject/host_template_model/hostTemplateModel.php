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
	
	isset($_GET["host_id"]) ? $hG = $_GET["host_id"] : $hG = NULL;
	isset($_POST["host_id"]) ? $hP = $_POST["host_id"] : $hP = NULL;
	$hG ? $host_id = $hG : $host_id = $hP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/configuration/configObject/host_template_model/";
	$path2 = "./include/configuration/configObject/host/";
	
	#PHP functions
	require_once $path2."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];
	
	switch ($o)	{
		case "a" : require_once($path."formHostTemplateModel.php"); break; #Add a host template model
		case "w" : require_once($path."formHostTemplateModel.php"); break; #Watch a host template model
		case "c" : require_once($path."formHostTemplateModel.php"); break; #Modify a host template model
		case "mc" : require_once($path."formHostTemplateModel.php"); break; #Massive change
		case "s" : enableHostInDB($host_id); require_once($path."listHostTemplateModel.php"); break; #Activate a host template model
		case "ms" : enableHostInDB(NULL, isset($select) ? $select : array()); require_once($path."listHostTemplateModel.php"); break;
		case "u" : disableHostInDB($host_id); require_once($path."listHostTemplateModel.php"); break; #Desactivate a host template model
		case "mu" : disableHostInDB(NULL, isset($select) ? $select : array()); require_once($path."listHostTemplateModel.php"); break;
		case "m" : multipleHostInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listHostTemplateModel.php"); break; #Duplicate n host template model
		case "d" : deleteHostInDB(isset($select) ? $select : array()); require_once($path."listHostTemplateModel.php"); break; #Delete n host template models
		default : require_once($path."listHostTemplateModel.php"); break;
	}
?>