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
	
	isset($_GET["cg_id"]) ? $cG = $_GET["cg_id"] : $cG = NULL;
	isset($_POST["cg_id"]) ? $cP = $_POST["cg_id"] : $cP = NULL;
	$cG ? $cg_id = $cG : $cg_id = $cP;

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
	$path = "./include/configuration/configObject/contactgroup/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formContactGroup.php"); break; #Add a contactgroup
		case "w" : require_once($path."formContactGroup.php"); break; #Watch a contactgroup
		case "c" : require_once($path."formContactGroup.php"); break; #Modify a contactgroup
		case "s" : enableContactGroupInDB($cg_id); require_once($path."listContactGroup.php"); break; #Activate a contactgroup
		case "u" : disableContactGroupInDB($cg_id); require_once($path."listContactGroup.php"); break; #Desactivate a contactgroup
		case "m" : multipleContactGroupInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listContactGroup.php"); break; #Duplicate n contact grou
		case "d" : deleteContactGroupInDB(isset($select) ? $select : array()); require_once($path."listContactGroup.php"); break; #Delete n contact group
		default : require_once($path."listContactGroup.php"); break;
	}
?>