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
	
	# LCA 
	$lcaHostByName = getLcaHostByName($pearDB);
		
	isset($_GET["host_id"]) ? $hG = $_GET["host_id"] : $hG = NULL;
	isset($_POST["host_id"]) ? $hP = $_POST["host_id"] : $hP = NULL;
	$hG ? $host_id = $hG : $host_id = $hP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	global $path;
	$path = "./include/configuration/configObject/host/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formHost.php"); break; #Add a host
		case "w" : require_once($path."formHost.php"); break; #Watch a host
		case "c" : require_once($path."formHost.php"); break; #Modify a host
		case "s" : enableHostInDB($host_id); require_once($path."listHost.php"); break; #Activate a host
		case "u" : disableHostInDB($host_id); require_once($path."listHost.php"); break; #Desactivate a host
		case "m" : multipleHostInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listHost.php"); break; #Duplicate n hosts
		case "d" : deleteHostInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listHost.php"); break; #Delete n hosts
		default : require_once($path."listHost.php"); break;
	}
?>