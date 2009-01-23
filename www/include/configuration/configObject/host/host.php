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
 * For information : contact@centreon.com
 */
 
	if (!isset($oreon))
		exit ();
	
	isset($_GET["host_id"]) ? $hG = $_GET["host_id"] : $hG = NULL;
	isset($_POST["host_id"]) ? $hP = $_POST["host_id"] : $hP = NULL;
	$hG ? $host_id = $hG : $host_id = $hP;

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
	global $path;
	$path = "./include/configuration/configObject/host/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	require_once "./include/common/common-Func-ACL.php";
	
	if (isset($_POST["o1"]) && isset($_POST["o2"])){
		if ($_POST["o1"] != "")
			$o = $_POST["o1"];
		if ($_POST["o2"] != "")
			$o = $_POST["o2"];
	}	
	
	switch ($o)	{
		case "a" 	: require_once($path."formHost.php"); break; #Add a host
		case "w" 	: require_once($path."formHost.php"); break; #Watch a host
		case "c" 	: require_once($path."formHost.php"); break; #Modify a host
		case "mc" 	: require_once($path."formHost.php"); break; # Massive Change
		case "s" 	: enableHostInDB($host_id); require_once($path."listHost.php"); break; #Activate a host
		case "ms" 	: enableHostInDB(NULL, isset($select) ? $select : array()); require_once($path."listHost.php"); break;
		case "u" 	: disableHostInDB($host_id); require_once($path."listHost.php"); break; #Desactivate a host
		case "mu" 	: disableHostInDB(NULL, isset($select) ? $select : array()); require_once($path."listHost.php"); break;
		case "m" 	: multipleHostInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listHost.php"); break; #Duplicate n hosts
		case "d" 	: deleteHostInDB(isset($select) ? $select : array()); require_once($path."listHost.php"); break; #Delete n hosts
		default 	: require_once($path."listHost.php"); break;
	}
?>