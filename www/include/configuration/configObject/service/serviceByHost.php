<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	$lcaHost = getLCAHostByID($pearDB);
	$lcaHostStr = getLCAHostStr($lcaHost["LcaHost"]);
	$lcaServiceGroupStr = getLCASGStr($lcaHost["LcaHost"]);
	$lcaHGStr = getLCAHGStr($lcaHost["LcaHostGroup"]);
	
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
		case "s" : enableServiceInDB($service_id); require_once($path."listServiceByHost.php"); break; #Activate a service
		case "u" : disableServiceInDB($service_id); require_once($path."listServiceByHost.php"); break; #Desactivate a service
		case "m" : multipleServiceInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listServiceByHost.php"); break; #Duplicate n services
		case "d" : deleteServiceInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listServiceByHost.php"); break; #Delete n services
		default : require_once($path."listServiceByHost.php"); break;
	}
?>