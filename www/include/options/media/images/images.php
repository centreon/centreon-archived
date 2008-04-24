<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@oreon-project.org
 */
	if (!isset ($oreon))
		exit ();
	
	isset($_GET["img_id"]) ? $imgG = $_GET["img_id"] : $imgG = NULL;
	isset($_POST["img_id"]) ? $imgP = $_POST["img_id"] : $imgP = NULL;
	$imgG ? $img_id = $imgG : $img_id = $imgP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the cities dir
	$path = "./include/options/media/images/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formImg.php"); break; #Add a img
		case "w" : require_once($path."formImg.php"); break; #Watch a img
		case "c" : require_once($path."formImg.php"); break; #Modify a img
		case "d" : deleteImgInDB(isset($select) ? $select : array()); require_once($path."listImg.php"); break; #Delete n imgs
		default : require_once($path."listImg.php"); break;
	}
?>