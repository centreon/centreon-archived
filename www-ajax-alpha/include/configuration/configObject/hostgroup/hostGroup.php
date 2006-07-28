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
	
	isset($_GET["hg_id"]) ? $hG = $_GET["hg_id"] : $hG = NULL;
	isset($_POST["hg_id"]) ? $hP = $_POST["hg_id"] : $hP = NULL;
	$hG ? $hg_id = $hG : $hg_id = $hP;
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/configuration/configObject/hostgroup/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formHostGroup.php"); break; #Add a Hostgroup
		case "w" : require_once($path."formHostGroup.php"); break; #Watch a Hostgroup
		case "c" : require_once($path."formHostGroup.php"); break; #Modify a Hostgroup
		case "s" : enableHostGroupInDB($hg_id); require_once($path."listHostGroup.php"); break; #Activate a Hostgroup
		case "u" : disableHostGroupInDB($hg_id); require_once($path."listHostGroup.php"); break; #Desactivate a Hostgroup
		case "m" : multipleHostGroupInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listHostGroup.php"); break; #Duplicate n Host grou
		case "d" : deleteHostGroupInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listHostGroup.php"); break; #Delete n Host group
		default : require_once($path."listHostGroup.php"); break;
	}
?>