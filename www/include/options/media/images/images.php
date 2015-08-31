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
	
	isset($_GET["img_id"]) ? $imgG = $_GET["img_id"] : $imgG = NULL;
	isset($_POST["img_id"]) ? $imgP = $_POST["img_id"] : $imgP = NULL;
	$imgG ? $img_id = $imgG : $img_id = $imgP;

	isset($_GET["dir_id"]) ? $dirG = $_GET["dir_id"] : $dirG = NULL;
	isset($_POST["dir_id"]) ? $dirP = $_POST["dir_id"] : $dirP = NULL;
	$dirG ? $dir_id = $dirG : $dir_id = $dirP;


	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the cities dir
	 */
	$path = "./include/options/media/images/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formImg.php"); break; #Add a img
		case "w" : require_once($path."formImg.php"); break; #Watch a img
		case "ci" : require_once($path."formImg.php"); break; #Modify a img
		case "cd" : require_once($path."formDirectory.php"); break; #Modify a dir
		case "m"  : require_once($path."formDirectory.php"); break; #Move files to a dir
		case "d" : 
			deleteMultImg(isset($select) ? $select : array()); 
			deleteMultDirectory(isset($select) ? $select : array()); 
			require_once($path."listImg.php"); 
			break;
		case "sd" : 
			require_once($path."syncDir.php");
			break;
		default : require_once($path."listImg.php"); break;
	}
?>