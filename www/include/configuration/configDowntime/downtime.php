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

	if (!isset($centreon)) {
		exit();
	}

	isset($_GET["dt_id"]) ? $dtG = $_GET["dt_id"] : $dtG = NULL;
	isset($_POST["dt_id"]) ? $dtP = $_POST["dt_id"] : $dtP = NULL;
	$dtG ? $downtime_id = CentreonDB::escape($dtG) : $downtime_id = CentreonDB::escape($dtP);

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	isset($_GET["type"]) ? $typeG = $_GET["type"] : $typeG = NULL;
	isset($_POST["type"]) ? $typeP = $_POST["type"] : $typeP = NULL;
	$typeG ? $type = $typeG : $type = $typeP;

	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	$path = "./include/configuration/configDowntime/";

	require_once "./class/centreonDowntime.class.php";
	$downtime = new CentreonDowntime($pearDB);

	require_once "./include/common/common-Func.php";

	if (isset($_POST["o1"]) && isset($_POST["o2"])){
		if ($_POST["o1"] != "") {
			$o = $_POST["o1"];
		}
		if ($_POST["o2"] != "") {
			$o = $_POST["o2"];
		}
	}

	/*
	 * Set the real page
	 */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
		$p = $ret['topology_page'];
	}

	if (isset($_GET["period_form"]) || isset($_GET["period"]) && $o == "") {
		require_once $path."ajaxForms.php";
	} else {
		switch ($o)	{
			case "a" : require_once($path."formDowntime.php"); break; #Add a downtime
			case "w" : require_once($path."formDowntime.php"); break; #Watch a downtime
			case "c" : require_once($path."formDowntime.php"); break; #Modify a downtime
			case "e" : $downtime->enable($downtime_id); require_once($path."listDowntime.php"); break; #Activate a service
			case "ms" : $downtime->multiEnable(isset($select) ? $select : array()); require_once($path."listDowntime.php"); break;
			case "u" : $downtime->disable($downtime_id); require_once($path."listDowntime.php"); break; #Desactivate a service
			case "mu" : $downtime->multiDisable(isset($select) ? $select : array()); require_once($path."listDowntime.php"); break;
			case "m" : $downtime->duplicate(isset($select) ? $select : array(), $dupNbr); require_once($path."listDowntime.php"); break; #Duplicate n services
			case "d" : $downtime->multiDelete(isset($select) ? $select : array()); require_once($path."listDowntime.php"); break; #Delete n services
			default : require_once($path."listDowntime.php"); break;
		}
	}
?>