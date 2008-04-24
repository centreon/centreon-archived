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

	isset($_GET["traps_id"]) ? $trapG = $_GET["traps_id"] : $trapG = NULL;
	isset($_POST["traps_id"]) ? $trapP = $_POST["traps_id"] : $trapP = NULL;
	$trapG ? $traps_id = $trapG : $traps_id = $trapP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/configuration/configObject/traps/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formTraps.php"); break; #Add a Trap
		case "w" : require_once($path."formTraps.php"); break; #Watch a Trap
		case "c" : require_once($path."formTraps.php"); break; #Modify a Trap
		case "m" : multipleTrapInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listTraps.php"); break; #Duplicate n Traps
		case "d" : deleteTrapInDB(isset($select) ? $select : array()); require_once($path."listTraps.php"); break; #Delete n Traps
		default : require_once($path."listTraps.php"); break;
	}
?>