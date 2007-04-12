<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	isset($_GET["traps_id"]) ? $trapG = $_GET["traps_id"] : $trapG = NULL;
	isset($_POST["traps_id"]) ? $trapP = $_POST["traps_id"] : $trapP = NULL;
	$trapG ? $traps_id = $trapG : $traps_id = $trapP;

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
		case "m" : multipleTrapInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listTraps.php"); break; #Duplicate n Traps
		case "d" : deleteTrapInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listTraps.php"); break; #Delete n Traps
		default : require_once($path."listTraps.php"); break;
	}
?>