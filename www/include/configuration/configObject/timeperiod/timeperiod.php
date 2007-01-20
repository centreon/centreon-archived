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
	
	isset($_GET["tp_id"]) ? $tpG = $_GET["tp_id"] : $tpG = NULL;
	isset($_POST["tp_id"]) ? $tpP = $_POST["tp_id"] : $tpP = NULL;
	$tpG ? $tp_id = $tpG : $tp_id = $tpP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/configuration/configObject/timeperiod/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formTimeperiod.php"); break; #Add a Timeperiod
		case "w" : require_once($path."formTimeperiod.php"); break; #Watch a Timeperiod
		case "c" : require_once($path."formTimeperiod.php"); break; #Modify a Timeperiod
		case "m" : multipleTimeperiodInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listTimeperiod.php"); break; #Duplicate n Timeperiods
		case "d" : deleteTimeperiodInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listTimeperiod.php"); break; #Delete n Timeperiods
		default : require_once($path."listTimeperiod.php"); break;
	}
?>