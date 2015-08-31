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
	
	isset($_GET["list"]) ? $mG = $_GET["list"] : $mG = NULL;
	isset($_POST["list"]) ? $mP = $_POST["list"] : $mP = NULL;
	$mG ? $list = $mG : $list = $mP;
	
	isset($_GET["id"]) ? $mG = $_GET["id"] : $mG = NULL;
	isset($_POST["id"]) ? $mP = $_POST["id"] : $mP = NULL;
	$mG ? $id = $mG : $id = $mP;
	
	isset($_GET["name"]) ? $nameG = $_GET["name"] : $nameG = NULL;	
	isset($_POST["name"]) ? $nameP = $_POST["name"] : $nameP = NULL;
	$nameG ? $name = $nameG : $name = $nameP;
		
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the options dir
	 */	
	$path = "./include/options/oreon/modules/";
	
	require_once "./include/common/common-Func.php";
	require_once $path ."DB-Func.php";
	
	if ($list)
		require_once($path."listModules.php");
	else	{
		switch ($o)	{
			case "i" : require_once($path."formModule.php"); break;
			case "u" : require_once($path."formModule.php"); break;
			case "d" : require_once($path."listModules.php"); break;
			case "w" : require_once($path."formModule.php"); break;
			default : require_once($path."listModules.php");  break;
		}
	}
?>