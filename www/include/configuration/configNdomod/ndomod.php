<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset ($oreon))
		exit ();
	
	isset($_GET["id"]) ? $cG = $_GET["id"] : $cG = NULL;
	isset($_POST["id"]) ? $cP = $_POST["id"] : $cP = NULL;
	$cG ? $id = $cG : $id = $cP;

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
	$path = "./include/configuration/configNdomod/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formNdomod.php"); break; #Add Ndomod.cfg
		case "w" : require_once($path."formNdomod.php"); break; #Watch Ndomod.cfg
		case "c" : require_once($path."formNdomod.php"); break; #Modify Ndomod.cfg
		case "s" : enableNdomodInDB($id); require_once($path."listNdomod.php"); break; #Activate a Ndomod CFG
		case "u" : disableNdomodInDB($id); require_once($path."listNdomod.php"); break; #Desactivate a Ndomod CFG
		case "m" : multipleNdomodInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listNdomod.php"); break; #Duplicate n Ndomod CFGs
		case "d" : deleteNdomodInDB(isset($select) ? $select : array()); require_once($path."listNdomod.php"); break; #Delete n Ndomod CFG
		default : require_once($path."listNdomod.php"); break;
	}
?>