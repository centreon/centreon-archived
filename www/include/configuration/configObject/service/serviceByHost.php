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

	if (!isset ($oreon)) {
		exit ();
	}

	global $form_service_type;
	$form_service_type = "BYHOST";


	isset($_GET["service_id"]) ? $sG = $_GET["service_id"] : $sG = NULL;
	isset($_POST["service_id"]) ? $sP = $_POST["service_id"] : $sP = NULL;
	$sG ? $service_id = CentreonDB::escape($sG) : $service_id = CentreonDB::escape($sP);

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
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/*
	 * Create a suffix for file name in order to redirect service by hostgroup
	 * on a good page.
	 */
	$linkType = '';

	/*
	 * Check if a service is a service by hostgroup or not
	 */
    $request = "SELECT * FROM host_service_relation WHERE service_service_id = '".(int)$service_id."'";
    $DBRESULT = $pearDB->query($request);
    while ($data = $DBRESULT->fetchRow()) {
        if (isset($data["hostgroup_hg_id"]) && $data["hostgroup_hg_id"] != "") {
            $linkType = 'Group';
            $form_service_type = "BYHOSTGROUP";
        }
    }

    /*
     * Check options
     */
	if (isset($_POST["o1"]) && isset($_POST["o2"])) {
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
		case "dv" : divideGroupedServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceByHost$linkType.php"); break; # Divide service linked to n hosts
		case "s" : enableServiceInDB($service_id); require_once($path."listServiceByHost$linkType.php"); break; #Activate a service
		case "ms" : enableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceByHost$linkType.php"); break;
		case "u" : disableServiceInDB($service_id); require_once($path."listServiceByHost$linkType.php"); break; #Desactivate a service
		case "mu" : disableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceByHost$linkType.php"); break;
		case "m" : multipleServiceInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServiceByHost$linkType.php"); break; #Duplicate n services
		case "d" : deleteServiceInDB(isset($select) ? $select : array()); require_once($path."listServiceByHost$linkType.php"); break; #Delete n services
		default : require_once($path."listServiceByHost$linkType.php"); break;
	}

?>
