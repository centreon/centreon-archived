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

	isset($_GET["resource_id"]) ? $resourceG = $_GET["resource_id"] : $resourceG = NULL;
	isset($_POST["resource_id"]) ? $resourceP = $_POST["resource_id"] : $resourceP = NULL;
	$resourceG ? $resource_id = $resourceG : $resource_id = $resourceP;

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
	$path = "./include/configuration/configResources/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

    $acl = $oreon->user->access;
    $serverString = $acl->getPollerString();
    $allowedResourceConf = array();
    if ($serverString != "''") {
        $sql = "SELECT resource_id
                FROM cfg_resource_instance_relations
                WHERE instance_id IN (".$serverString.")";
                $res = $pearDB->query($sql);
        while ($row = $res->fetchRow()) {
            $allowedResourceConf[$row['resource_id']] = true;
        }
    }

	switch ($o)	{
		case "a" :
			/*
			 * Add a Resource
			 */
			require_once($path."formResources.php");
			break;
		case "w" :
			/*
			 * Watch a Resource
			 */
			require_once($path."formResources.php");
			break;
		case "c" :
			/*
			 * Modify a Resource
			 */
			require_once($path."formResources.php");
			break;
		case "s" :
			/*
			 * Activate a Resource
			 */
			enableResourceInDB($resource_id);
			require_once($path."listResources.php");
			break;
		case "u" :
			/*
			 * Desactivate a Resource
			 */
			disableResourceInDB($resource_id);
			require_once($path."listResources.php");
			break;
		case "m" :
			/*
			 * Duplicate n Resources
			 */
			multipleResourceInDB(isset($select) ? $select : array(), $dupNbr);
			require_once($path."listResources.php");
			break;
		case "d" :
			/*
			 * Delete n Resources
			 */
			deleteResourceInDB(isset($select) ? $select : array());
			require_once($path."listResources.php");
			break;
		default :
			require_once($path."listResources.php");
			break;
	}
?>
