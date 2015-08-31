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
		exit ();
	}

	global $form_service_type;
    $form_service_type = "BYHOSTGROUP";

	isset($_GET["service_id"]) ? $sG = $_GET["service_id"] : $sG = NULL;
	isset($_POST["service_id"]) ? $sP = $_POST["service_id"] : $sP = NULL;
	$sG ? $service_id = $sG : $service_id = $sP;

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
	$path = "./include/configuration/configObject/service/";

	/*
	 * PHP functions
	 */
	require_once("./class/centreonDB.class.php");

	$pearDBO = new CentreonDB("centstorage");

	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	if (isset($_POST["o1"]) && isset($_POST["o2"])){
		if ($_POST["o1"] != "") {
			$o = $_POST["o1"];
		}
		if ($_POST["o2"] != "") {
			$o = $_POST["o2"];
		}
	}

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
		$p = $ret['topology_page'];
	}

    $acl = $oreon->user->access;
    $acldbname = $acl->getNameDBAcl($oreon->broker->getBroker());

	switch ($o)	{
		case "a" : require_once($path."formService.php"); break; #Add a service
		case "w" : require_once($path."formService.php"); break; #Watch a service
		case "c" : require_once($path."formService.php"); break; #Modify a service
		case "mc" : require_once($path."formService.php"); break; #Massive change
		case "dv" : divideGroupedServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceByHostGroup.php"); break; # Divide service linked to n hostgroups
		case "mvH" : divideGroupedServiceInDB(NULL, isset($select) ? $select : array(), 1); require_once($path."listServiceByHostGroup.php"); break; # Divide service linked to n hostgroups
		case "s" : enableServiceInDB($service_id); require_once($path."listServiceByHostGroup.php"); break; #Activate a service
		case "ms" : enableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceByHostGroup.php"); break;
		case "u" : disableServiceInDB($service_id); require_once($path."listServiceByHostGroup.php"); break; #Desactivate a service
		case "mu" : disableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceByHostGroup.php"); break;
		case "m" : multipleServiceInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServiceByHostGroup.php"); break; #Duplicate n services
		case "d" : deleteServiceInDB(isset($select) ? $select : array()); require_once($path."listServiceByHostGroup.php"); break; #Delete n services
		default : require_once($path."listServiceByHostGroup.php"); break;
	}
?>
