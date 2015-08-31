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

	isset($_GET["id"]) ? $cG = $_GET["id"] : $cG = NULL;
	isset($_POST["id"]) ? $cP = $_POST["id"] : $cP = NULL;
	$cG ? $id = $cG : $id = $cP;

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
	$path = "./include/configuration/configNdo2db/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
	    $p = $ret['topology_page'];
    }

    $acl = $oreon->user->access;
    $serverString = $acl->getPollerString();
    $allowedNdo2db = array();
    if ($serverString != "''") {
        $sql = "SELECT id
                FROM cfg_ndomod
                WHERE ns_nagios_server IN (".$serverString.")";
        $res = $pearDB->query($sql);
        while ($row = $res->fetchRow()) {
            $allowedNdo2db[$row['id']] = true;
        }
    }

	switch ($o)	{
		case "a" :
			require_once($path."formNdo2db.php");
			break; // Add Ndo2db.cfg
		case "w" :
			require_once($path."formNdo2db.php");
			break; // Watch Ndo2db.cfg
		case "c" :
			require_once($path."formNdo2db.php");
			break; // 2dbify Ndo2db.cfg
		case "s" :
			enableNdo2dbInDB($id);
			require_once($path."listNdo2db.php");
			break; // Activate a Ndo2db CFG
		case "u" :
			disableNdo2dbInDB($id);
			require_once($path."listNdo2db.php");
			break; // Deactivate a Ndo2db CFG
		case "m" :
			multipleNdo2dbInDB(isset($select) ? $select : array(), $dupNbr);
			require_once($path."listNdo2db.php");
			break; // Duplicate n Ndo2db CFGs
		case "d" :
			deleteNdo2dbInDB(isset($select) ? $select : array());
			require_once($path."listNdo2db.php");
			break; // Delete n Ndo2db CFG
		default :
			require_once($path."listNdo2db.php");
			break;
	}
?>
