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

	isset($_GET["nagios_id"]) ? $cG = $_GET["nagios_id"] : $cG = NULL;
	isset($_POST["nagios_id"]) ? $cP = $_POST["nagios_id"] : $cP = NULL;
	$cG ? $nagios_id = $cG : $nagios_id = $cP;

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
	$path = "./include/configuration/configNagios/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

    $acl = $oreon->user->access;
    $serverString = $acl->getPollerString();
    $allowedMainConf = array();
    if ($serverString != "''") {
        $sql = "SELECT nagios_id
                FROM cfg_nagios
                WHERE nagios_server_id IN (".$serverString.")";
        $res = $pearDB->query($sql);
        while ($row = $res->fetchRow()) {
            $allowedMainConf[$row['nagios_id']] = true;
        }
    }

	switch ($o)	{
		case "a" : require_once($path."formNagios.php"); break; #Add Nagios.cfg
		case "w" : require_once($path."formNagios.php"); break; #Watch Nagios.cfg
		case "c" : require_once($path."formNagios.php"); break; #Modify Nagios.cfg
		case "s" : enableNagiosInDB($nagios_id); require_once($path."listNagios.php"); break; #Activate a nagios CFG
		case "u" : disableNagiosInDB($nagios_id); require_once($path."listNagios.php"); break; #Desactivate a nagios CFG
		case "m" : multipleNagiosInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listNagios.php"); break; #Duplicate n nagios CFGs
		case "d" : deleteNagiosInDB(isset($select) ? $select : array()); require_once($path."listNagios.php"); break; #Delete n nagios CFG
		default : require_once($path."listNagios.php"); break;
	}
?>
