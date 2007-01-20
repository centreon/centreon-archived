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
	
	isset($_GET["dep_id"]) ? $cG = $_GET["dep_id"] : $cG = NULL;
	isset($_POST["dep_id"]) ? $cP = $_POST["dep_id"] : $cP = NULL;
	$cG ? $dep_id = $cG : $dep_id = $cP;
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/configuration/configObject/metaservice_dependency/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formMetaServiceDependency.php"); break; #Add a Meta Service
		case "w" : require_once($path."formMetaServiceDependency.php"); break; #Watch a Meta Service
		case "c" : require_once($path."formMetaServiceDependency.php"); break; #Modify a Meta Service
		case "m" : multipleMetaServiceDependencyInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listMetaServiceDependency.php"); break; #Duplicate n Meta Services
		case "d" : deleteMetaServiceDependencyInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listMetaServiceDependency.php"); break; #Delete n Meta Service
		default : require_once($path."listMetaServiceDependency.php"); break;
	}
?>