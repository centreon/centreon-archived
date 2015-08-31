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

	isset($_GET["service_id"]) ? $sG = $_GET["service_id"] : $sG = NULL;
	isset($_POST["service_id"]) ? $sP = $_POST["service_id"] : $sP = NULL;
	$sG ? $service_id = $sG : $service_id = $sP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	if ($o == "c" && $service_id == NULL) {
		$o = "";
	}

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/configuration/configObject/service_template_model/";
	$path2 = "./include/configuration/configObject/service/";

	/*
	 * PHP functions
	 */
	require_once $path2."DB-Func.php";
	require_once "./include/common/common-Func.php";

        $serviceObj = new CentreonService($pearDB);
        $lockedElements = $serviceObj->getLockedServiceTemplates();
        
	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

	switch ($o)	{
		case "a" : require_once($path."formServiceTemplateModel.php"); break; #Add a Service Template Model
		case "w" : require_once($path."formServiceTemplateModel.php"); break; #Watch a Service Template Model
		case "c" : require_once($path."formServiceTemplateModel.php"); break; #Modify a Service Template Model
		case "mc" : require_once($path."formServiceTemplateModel.php"); break; #Massive change
		case "s" : enableServiceInDB($service_id); require_once($path."listServiceTemplateModel.php"); break; #Activate a Service Template Model
		case "ms" : enableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break;
		case "u" : disableServiceInDB($service_id); require_once($path."listServiceTemplateModel.php"); break; #Desactivate a Service Template Model
		case "mu" : disableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break;
		case "m" : multipleServiceInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServiceTemplateModel.php"); break; #Duplicate n Service Template Models
		case "d" : deleteServiceInDB(isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break; #Delete n Service Template Models
		default : require_once($path."listServiceTemplateModel.php"); break;
	}
?>