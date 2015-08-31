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

	if (!isset($oreon)) {
		exit ();
	}

	isset($_GET["contact_id"]) ? $cG = $_GET["contact_id"] : $cG = NULL;
	isset($_POST["contact_id"]) ? $cP = $_POST["contact_id"] : $cP = NULL;
	$cG ? $contact_id = $cG : $contact_id = $cP;

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
	$path = "./include/configuration/configObject/contact_template_model/";

	/*
	 * PHP functions
	 */
	require_once "./include/configuration/configObject/contact/DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
            $p = $ret['topology_page'];
	}

        $contactObj = new CentreonContact($pearDB);
        
	switch ($o)	{
		case "mc" : require_once($path."formContactTemplateModel.php"); break; // Massive Change
		case "a" : require_once($path."formContactTemplateModel.php"); break; // Add a contact template
                case "w" : require_once($path."formContactTemplateModel.php"); break; // Watch a contact template
		case "c" : require_once($path."formContactTemplateModel.php"); break; // Modify a contact template
		case "s" : enableContactInDB($contact_id); require_once($path."listContactTemplateModel.php"); break; // Activate a contact template
		case "ms" : enableContactInDB(NULL, isset($select) ? $select : array()); require_once($path."listContactTemplateModel.php"); break;
		case "u" : disableContactInDB($contact_id); require_once($path."listContactTemplateModel.php"); break; // Desactivate a contact
		case "mu" : disableContactInDB(NULL, isset($select) ? $select : array()); require_once($path."listContactTemplateModel.php"); break;
                case "m" : multipleContactInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listContactTemplateModel.php"); break; // Duplicate n contacts
		case "d" : deleteContactInDB(isset($select) ? $select : array()); require_once($path."listContactTemplateModel.php"); break; // Delete n contacts
		default : require_once($path."listContactTemplateModel.php"); break;
	}
