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
	
	isset($_GET["vmetric_id"]) ? $cG = $_GET["vmetric_id"] : $cG = NULL;
	isset($_POST["vmetric_id"]) ? $cP = $_POST["vmetric_id"] : $cP = NULL;
	$cG ? $vmetric_id = $cG : $vmetric_id = $cP;

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
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the configuration dir
	 */
	$path = "./include/views/graphs/virtualMetrics/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once $path."checkRRDGraph.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : 
			require_once $path."formVirtualMetrics.php"; 
			break; #Add a Virtual Metrics
		case "w" : 
			require_once $path."formVirtualMetrics.php"; 
			break; #Watch a Virtual Metrics
		case "c" : 
			require_once $path."formVirtualMetrics.php" ; 
			break; #Modify a Virtual Metrics
		case "s" : 
			enableVirtualMetricInDB($vmetric_id); 
			require_once $path."listVirtualMetrics.php"; 
			break; #Activate a Virtual Metrics
		case "u" : 
			disableVirtualMetricInDB($vmetric_id); 
			require_once $path."listVirtualMetrics.php"; 
			break; #Desactivate a Virtual Metrics...
		case "m" : 
			multipleVirtualMetricInDB(isset($select) ? $select : array(), $dupNbr); 
			require_once $path."listVirtualMetrics.php"; 
			break; #Duplicate n Virtual Metrics
		case "d" : 
			deleteVirtualMetricInDB(isset($select) ? $select : array()); 
			require_once $path."listVirtualMetrics.php"; 
			break; #Delete n Virtual Metrics
		default : 
			require_once $path."listVirtualMetrics.php"; 
			break;
	}
?>
