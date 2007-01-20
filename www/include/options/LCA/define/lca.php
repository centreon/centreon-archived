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
	
	isset($_GET["lca_id"]) ? $cG = $_GET["lca_id"] : $cG = NULL;
	isset($_POST["lca_id"]) ? $cP = $_POST["lca_id"] : $cP = NULL;
	$cG ? $lca_id = $cG : $lca_id = $cP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/options/LCA/define/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formLCA.php"); break; #Add a LCA
		case "w" : require_once($path."formLCA.php"); break; #Watch a LCA
		case "c" : require_once($path."formLCA.php"); break; #Modify a LCA
		case "s" : enableLCAInDB($lca_id); require_once($path."listLCA.php"); break; #Activate a LCA
		case "u" : disableLCAInDB($lca_id); require_once($path."listLCA.php"); break; #Desactivate a LCA
		case "m" : multipleLCAInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listLCA.php"); break; #Duplicate n LCAs
		case "d" : deleteLCAInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listLCA.php"); break; #Delete n LCAs
		default : require_once($path."listLCA.php"); break;
	}
?>