<?
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

	isset($_GET["service_id"]) ? $sG = $_GET["service_id"] : $sG = NULL;
	isset($_POST["service_id"]) ? $sP = $_POST["service_id"] : $sP = NULL;
	$sG ? $service_id = $sG : $service_id = $sP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	$lcaHost = getLCAHostByID($pearDB);
	$lcaHostStr = getLCAHostStr($lcaHost["LcaHost"]);
	$lcaServiceGroupStr = getLCASGStr($lcaHost["LcaHost"]);
	$lcaHGStr = getLCAHGStr($lcaHost["LcaHostGroup"]);
	$isRestreint = HadUserLca($pearDB);

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/configuration/configObject/service/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formService.php"); break; #Add a service
		case "w" : require_once($path."formService.php"); break; #Watch a service
		case "c" : require_once($path."formService.php"); break; #Modify a service
		case "mc" : require_once($path."formService.php"); break; #Massive change
		case "s" : enableServiceInDB($service_id); require_once($path."listServiceByHostGroup.php"); break; #Activate a service
		case "ms" : enableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceByHostGroup.php"); break;
		case "u" : disableServiceInDB($service_id); require_once($path."listServiceByHostGroup.php"); break; #Desactivate a service
		case "mu" : disableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceByHostGroup.php"); break;
		case "m" : multipleServiceInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServiceByHostGroup.php"); break; #Duplicate n services
		case "d" : deleteServiceInDB(isset($select) ? $select : array()); require_once($path."listServiceByHostGroup.php"); break; #Delete n services
		default : require_once($path."listServiceByHostGroup.php"); break;
	}
?>