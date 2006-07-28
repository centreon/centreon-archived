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
	
	isset($_GET["resource_id"]) ? $resourceG = $_GET["resource_id"] : $resourceG = NULL;
	isset($_POST["resource_id"]) ? $resourceP = $_POST["resource_id"] : $resourceP = NULL;
	$resourceG ? $resource_id = $resourceG : $resource_id = $resourceP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/configuration/configResources/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formResources.php"); break; #Add a Resource
		case "w" : require_once($path."formResources.php"); break; #Watch a Resource
		case "c" : require_once($path."formResources.php"); break; #Modify a Resource
		case "s" : enableResourceInDB($resource_id); require_once($path."listResources.php"); break; #Activate a Resource
		case "u" : disableResourceInDB($resource_id); require_once($path."listResources.php"); break; #Desactivate a Resource
		case "m" : multipleResourceInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listResources.php"); break; #Duplicate n Resources
		case "d" : deleteResourceInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listResources.php"); break; #Delete n Resources
		default : require_once($path."listResources.php"); break;
	}
?>