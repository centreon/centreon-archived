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
        
	isset($_GET["traps_id"]) ? $trapG = $_GET["traps_id"] : $trapG = NULL;
	isset($_POST["traps_id"]) ? $trapP = $_POST["traps_id"] : $trapP = NULL;
	$trapG ? $traps_id = $trapG : $traps_id = $trapP;

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
	$path = "./include/configuration/configObject/traps/";

	#PHP functions
	//require_once $path."DB-Func.php";
    require_once './class/centreonTraps.class.php';
	require_once "./include/common/common-Func.php";

    $trapObj = new Centreon_Traps($oreon, $pearDB);
    $acl = $oreon->user->access;
    $aclDbName = $acl->getNameDBAcl($oreon->broker->getBroker());
    $dbmon = $oreon->broker->getBroker() == 'broker' ? new CentreonDB('centstorage') : new CentreonDB('ndo');
    $sgs = $acl->getServiceGroupAclConf(null, $oreon->broker->getBroker());
    $severityObj = new CentreonCriticality($pearDB);
    
    /* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];
    
	switch ($o)	{
		case "a" : require_once($path."formTraps.php"); break; #Add a Trap
		case "w" : require_once($path."formTraps.php"); break; #Watch a Trap
		case "c" : require_once($path."formTraps.php"); break; #Modify a Trap
		case "m" : $trapObj->duplicate(isset($select) ? $select : array(), $dupNbr); require_once($path."listTraps.php"); break; #Duplicate n Traps
		case "d" : $trapObj->delete(isset($select) ? $select : array()); require_once($path."listTraps.php"); break; #Delete n Traps
		default : require_once($path."listTraps.php"); break;
	}
?>