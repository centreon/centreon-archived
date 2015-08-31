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
		
	isset($_GET["esc_id"]) ? $cG = $_GET["esc_id"] : $cG = NULL;
	isset($_POST["esc_id"]) ? $cP = $_POST["esc_id"] : $cP = NULL;
	$cG ? $esc_id = $cG : $esc_id = $cP;

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
	$path = "./include/configuration/configObject/escalation/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];
	
        $acl = $oreon->user->access;
        $dbmon = $acl->getNameDBAcl($oreon->broker->getBroker());
        
        $hgs = $acl->getHostGroupAclConf(null, $oreon->broker->getBroker());
        $hgString = CentreonUtils::toStringWithQuotes($hgs);            
        $sgs = $acl->getServiceGroupAclConf(null, $oreon->broker->getBroker());
        $sgString = CentreonUtils::toStringWithQuotes($sgs);
        
	switch ($o)	{
		case "a" : 
			require_once($path."formEscalation.php"); 
			break; #Add a Escalation
		case "w" : 
			require_once($path."formEscalation.php"); 
			break; #Watch a Escalation
		case "c" : 
			require_once($path."formEscalation.php"); 
			break; #Modify a Escalation
		case "m" : 
			multipleEscalationInDB(isset($select) ? $select : array(), $dupNbr); 
			require_once($path."listEscalation.php"); 
			break; #Duplicate n Escalations
		case "d" : 
			deleteEscalationInDB(isset($select) ? $select : array()); 
			require_once($path."listEscalation.php"); 
			break; #Delete n Escalation
		default : 
			require_once($path."listEscalation.php"); 
			break;
	}
?>
