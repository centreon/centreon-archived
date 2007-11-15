<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Sugumaran Mathavarajan - Julien Mathis - Romain Lemerlus

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

	isset($_GET["id"]) ? $mnftrG = $_GET["id"] : $mnftrG = NULL;
	isset($_POST["id"]) ? $mnftrP = $_POST["id"] : $mnftrP = NULL;
	$mnftrG ? $id = $mnftrG : $id = $mnftrP;

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
	$path = "./include/configuration/configObject/traps-manufacturer/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formMnftr.php"); break; #Add a Trap
		case "w" : require_once($path."formMnftr.php"); break; #Watch a Trap
		case "c" : require_once($path."formMnftr.php"); break; #Modify a Trap
		case "m" : multipleMnftrInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listMnftr.php"); break; #Duplicate n Traps
		case "d" : deleteMnftrInDB(isset($select) ? $select : array()); require_once($path."listMnftr.php"); break; #Delete n Traps
		default : require_once($path."listMnftr.php"); break;
	}
?>