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

	$lcaHost = getLCAHostByID($pearDB);
	$lcaHostStr = getLCAHostStr($lcaHost["LcaHost"]);
	$lcaServiceGroupStr = getLCASGStr($lcaHost["LcaHost"]);
	$lcaHGStr = getLCAHGStr($lcaHost["LcaHostGroup"]);
	
		
	isset($_GET["dep_id"]) ? $cG = $_GET["dep_id"] : $cG = NULL;
	isset($_POST["dep_id"]) ? $cP = $_POST["dep_id"] : $cP = NULL;
	$cG ? $dep_id = $cG : $dep_id = $cP;
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/configuration/configObject/servicegroup_dependency/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formServiceGroupDependency.php"); break; #Add a Dependency
		case "w" : require_once($path."formServiceGroupDependency.php"); break; #Watch a Dependency
		case "c" : require_once($path."formServiceGroupDependency.php"); break; #Modify a Dependency
		case "m" : multipleServiceGroupDependencyInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listServiceGroupDependency.php"); break; #Duplicate n Dependencys
		case "d" : deleteServiceGroupDependencyInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listServiceGroupDependency.php"); break; #Delete n Dependency
		default : require_once($path."listServiceGroupDependency.php"); break;
	}
?>