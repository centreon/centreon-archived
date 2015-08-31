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

	isset($_GET["sc_id"]) ? $cG = $_GET["sc_id"] : $cG = NULL;
	isset($_POST["sc_id"]) ? $cP = $_POST["sc_id"] : $cP = NULL;
	$cG ? $sc_id = $cG : $sc_id = $cP;

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
	$path = "./include/configuration/configObject/service_categories/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

    $acl = $oreon->user->access;
    $dbmon = $oreon->broker->getBroker() == 'broker' ? new CentreonDB('centstorage') : new CentreonDB('ndo');
    $aclDbName = $acl->getNameDBAcl($oreon->broker->getBroker());
    $scString = $acl->getServiceCategoriesString();

	switch ($o)	{
		case "mc" : require_once($path."formServiceCategories.php"); break; # Massive Change
		case "a" : require_once($path."formServiceCategories.php"); break; #Add a contact
		case "w" : require_once($path."formServiceCategories.php"); break; #Watch a contact
		case "c" : require_once($path."formServiceCategories.php"); break; #Modify a contact
		case "s" : enableServiceCategorieInDB($sc_id); require_once($path."listServiceCategories.php"); break; #Activate a ServiceCategories
		case "ms" : enableServiceCategorieInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceCategories.php"); break;
		case "u" : disableServiceCategorieInDB($sc_id); require_once($path."listServiceCategories.php"); break; #Desactivate a contact
		case "mu" : disableServiceCategorieInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceCategories.php"); break;

		case "m" : multipleServiceCategorieInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServiceCategories.php"); break; #Duplicate n contacts

		case "d" : deleteServiceCategorieInDB(isset($select) ? $select : array()); require_once($path."listServiceCategories.php"); break; #Delete n contacts
		default : require_once($path."listServiceCategories.php"); break;
	}
?>
