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

	isset($_GET["sg_id"]) ? $sG = $_GET["sg_id"] : $sG = NULL;
	isset($_POST["sg_id"]) ? $sP = $_POST["sg_id"] : $sP = NULL;
	$sG ? $sg_id = $sG : $sg_id = $sP;

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
	$path = "./include/configuration/configObject/servicegroup/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

    $acl = $oreon->user->access;
    $aclDbName = $acl->getNameDBAcl($oreon->broker->getBroker());
    $dbmon = $oreon->broker->getBroker() == 'broker' ? new CentreonDB('centstorage') : new CentreonDB('ndo');
    $sgs = $acl->getServiceGroupAclConf(null, $oreon->broker->getBroker());

    function mywrap ($el) {
        return "'".$el."'";
    }
    $sgString = implode(',', array_map('mywrap', array_keys($sgs)));

	switch ($o)	{
		case "a" : require_once($path."formServiceGroup.php"); break; #Add a Servicegroup
		case "w" : require_once($path."formServiceGroup.php"); break; #Watch a Servicegroup
		case "c" : require_once($path."formServiceGroup.php"); break; #Modify a Servicegroup
		case "s" : enableServiceGroupInDB($sg_id); require_once($path."listServiceGroup.php"); break; #Activate a Servicegroup
		case "u" : disableServiceGroupInDB($sg_id); require_once($path."listServiceGroup.php"); break; #Desactivate a Servicegroup
		case "m" : multipleServiceGroupInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServiceGroup.php"); break; #Duplicate n Service grou
		case "d" : deleteServiceGroupInDB(isset($select) ? $select : array()); require_once($path."listServiceGroup.php"); break; #Delete n Service group
		default : require_once($path."listServiceGroup.php"); break;
	}
?>
