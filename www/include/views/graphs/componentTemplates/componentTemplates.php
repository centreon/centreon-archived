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

	isset($_GET["compo_id"]) ? $cG = $_GET["compo_id"] : $cG = NULL;
	isset($_POST["compo_id"]) ? $cP = $_POST["compo_id"] : $cP = NULL;
	$cG ? $compo_id = $cG : $compo_id = $cP;

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
	$path = "./include/views/graphs/componentTemplates/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" :
			require_once $path."formComponentTemplate.php";
			break; //Add a Component Template
		case "w" :
			require_once $path."formComponentTemplate.php";
			break; //Watch a Component Template
		case "c" :
			require_once $path."formComponentTemplate.php" ;
			break; //Modify a Component Template
		case "s" :
			enableComponentTemplateInDB($lca_id);
			require_once $path."listComponentTemplates.php";
			break; //Activate a Component Template
		case "u" :
			disableComponentTemplateInDB($lca_id);
			require_once $path."listComponentTemplates.php";
			break; //Desactivate a Component Template
		case "m" :
			multipleComponentTemplateInDB(isset($select) ? $select : array(), $dupNbr);
			require_once $path."listComponentTemplates.php";
			break; //Duplicate n Component Templates
		case "d" :
			deleteComponentTemplateInDB(isset($select) ? $select : array());
			require_once $path."listComponentTemplates.php";
			break; //Delete n Component Templates
		default :
			require_once $path."listComponentTemplates.php";
			break;
	}
?>