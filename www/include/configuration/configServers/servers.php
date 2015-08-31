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

	isset($_GET["server_id"]) ? $cG = $_GET["server_id"] : $cG = NULL;
	isset($_POST["server_id"]) ? $cP = $_POST["server_id"] : $cP = NULL;
	$cG ? $server_id = $cG : $server_id = $cP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	// Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	// Path to the configuration dir
	$path = "./include/configuration/configServers/";

	// PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

    $serverResult = $oreon->user->access->getPollerAclConf(array('fields' => array('id', 'name', 'last_restart'),
                                                                 'order'  => array('name'),
                                                                 'keys'   => array('id')));
    
    $instanceObj = new CentreonInstance($pearDB);
    
    switch ($o)	{
		case "a" : require_once($path."formServers.php"); break; // Add Servers
		case "w" : require_once($path."formServers.php"); break; // Watch Servers
		case "c" : require_once($path."formServers.php"); break; // Modify Servers
		case "s" : enableServerInDB($server_id); require_once($path."listServers.php"); break; // Activate a Server
		case "u" : disableServerInDB($server_id); require_once($path."listServers.php"); break; // Desactivate a Server
        case "i" : require_once($path."getServersVersions.php"); break; // Search for version of engines Servers
		case "m" : multipleServerInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServers.php"); break; // Duplicate n Servers
		case "d" : deleteServerInDB(isset($select) ? $select : array()); require_once($path."listServers.php"); break; // Delete n Servers
		default : require_once($path."listServers.php"); break;
	}
?>
