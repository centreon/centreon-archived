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
	
	isset($_GET["gopt_id"]) ? $cG = $_GET["gopt_id"] : $cG = NULL;
	isset($_POST["lca_id"]) ? $cP = $_POST["gopt_id"] : $cP = NULL;
	$cG ? $gopt_id = $cG : $gopt_id = $cP;
		
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the option dir
	 */
	$path = "./include/options/oreon/generalOpt/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "nagios" : 
			require_once $path."nagios/form.php" ; 
			break;
		case "colors" : 
			require_once $path."colors/form.php" ; 
			break;
		case "snmp" : 
			require_once $path."snmp/form.php" ; 
			break;
		case "rrdtool" : 
			require_once $path."rrdtool/form.php" ; 
			break;
		case "ldap" : 
			require_once $path."ldap/ldap.php" ; 
			break;
		case "debug" : 
			require_once $path."debug/form.php" ; 
			break;
		case "general" : 
			require_once $path."general/form.php" ; 
			break;
		case "css" : 
			require_once $path."css/form.php" ; 
			break;
		case "ods" : 
			require_once $path."centstorage/form.php" ; 
			break;
		case "ndo" : 
			require_once $path."ndo/form.php" ; 
			break;
		case "cas" : 
			require_once $path."CAS/form.php" ; 
			break;
		case "reporting" : 
			require_once $path."reporting/form.php" ; 
			break;
                case "centcore" :
                    require_once $path.'centcore/centcore.php';
                    break;
		default : 
			require_once $path."general/form.php" ; 
			break;
	}
?>