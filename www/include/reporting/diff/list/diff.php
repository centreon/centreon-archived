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
	
	isset($_GET["rtdl_id"]) ? $cG = $_GET["rtdl_id"] : $cG = NULL;
	isset($_POST["rtdl_id"]) ? $cP = $_POST["rtdl_id"] : $cP = NULL;
	$cG ? $rtdl_id = $cG : $rtdl_id = $cP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	
	#Path to the configuration dir
	$path = "./include/reporting/diff/list/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formDiff.php"); break; #Add a Diffusion List
		case "w" : require_once($path."formDiff.php"); break; #Watch a Diffusion List
		case "c" : require_once($path."formDiff.php"); break; #Modify a Diffusion List
		case "s" : enableListInDB($rtdl_id); require_once($path."listDiff.php"); break; #Activate a Diffusion List
		case "u" : disableListInDB($rtdl_id); require_once($path."listDiff.php"); break; #Desactivate a Diffusion List
		case "m" : multipleListInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listDiff.php"); break; #Duplicate n Diffusion Lists
		case "d" : deleteListInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listDiff.php"); break; #Delete n Diffusion Lists
		default : require_once($path."listDiff.php"); break;
	}
?>