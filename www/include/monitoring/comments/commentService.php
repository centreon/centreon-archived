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

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/monitoring/comments/";

	/*
	 * PHP functions
	 */
	require_once "./include/common/common-Func.php";
	require_once "./include/monitoring/comments/common-Func.php";
	require_once "./include/monitoring/external_cmd/functions.php";

	switch ($o)	{
		case "as" :
			require_once($path."AddSvcComment.php");
			break;
		case "ds" :
			DeleteComment("SVC",isset($_GET["select"]) ? $_GET["select"] : array());
			require_once($path."viewServiceComment.php");
			break;
		case "vs" :
			require_once($path."viewServiceComment.php");
			break;
		default :
			require_once($path."viewServiceComment.php");
			break;
	}
?>