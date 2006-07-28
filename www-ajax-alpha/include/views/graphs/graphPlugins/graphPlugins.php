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
	
	isset($_GET["graph_id"]) ? $cG = $_GET["graph_id"] : $cG = NULL;
	isset($_POST["graph_id"]) ? $cP = $_POST["graph_id"] : $cP = NULL;
	$cG ? $graph_id = $cG : $graph_id = $cP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/views/graphs/graphPlugins/";
	
	#PHP functions
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formGraphPlugins.php"); break; #Add a Graph Plugins
		case "w" : require_once($path."formGraphPlugins.php"); break; #Watch aGraph Plugins
		case "c" : require_once($path."formGraphPlugins.php"); break; #Modify a Graph Plugins
		case "s" : enableGraphTemplateInDB($lca_id); require_once($path."listGraphPlugins.php"); break; #Activate a Graph Plugins
		case "u" : disableGraphTemplateInDB($lca_id); require_once($path."listGraphPlugins.php"); break; #Desactivate a Graph Plugins
		case "m" : multipleGraphTemplateInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listGraphPlugins.php"); break; #Duplicate n Graph Plugins
		case "d" : deleteGraphTemplateInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listGraphPlugins.php"); break; #Delete n Graph Plugins
		default : require_once($path."listGraphPlugins.php"); break;
	}
?>