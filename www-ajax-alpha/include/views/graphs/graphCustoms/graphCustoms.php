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
	
	isset($_GET["compo_id"]) ? $cG = $_GET["compo_id"] : $cG = NULL;
	isset($_POST["compo_id"]) ? $cP = $_POST["compo_id"] : $cP = NULL;
	$cG ? $compo_id = $cG : $compo_id = $cP;
	
	isset($_GET["graph_id"]) ? $cG = $_GET["graph_id"] : $cG = NULL;
	isset($_POST["graph_id"]) ? $cP = $_POST["graph_id"] : $cP = NULL;
	$cG ? $graph_id = $cG : $graph_id = $cP;
	
	isset($_GET["host_name"]) ? $cG = $_GET["host_name"] : $cG = NULL;
	isset($_POST["host_name"]) ? $cP = $_POST["host_name"] : $cP = NULL;
	$cG ? $host_name = $cG : $host_name = $cP;
	
	isset($_GET["meta_service"]) ? $cG = $_GET["meta_service"] : $cG = NULL;
	isset($_POST["meta_service"]) ? $cP = $_POST["meta_service"] : $cP = NULL;
	$cG ? $meta_service = $cG : $meta_service = $cP;
	
	isset($_GET["osl"]) ? $cG = $_GET["osl"] : $cG = NULL;
	isset($_POST["osl"]) ? $cP = $_POST["osl"] : $cP = NULL;
	$cG ? $osl = $cG : $osl = $cP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/views/graphs/graphCustoms/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formGraphCustom.php"); break; #Add a Graph Customs
		case "w" : require_once($path."viewGraphCustoms.php"); break; #Watch aGraph Customs
		case "c" : require_once($path."formGraphCustom.php"); break; #Modify a Graph Customs
		case "m" : multipleGraphInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listGraphCustoms.php"); break; #Duplicate n Graph Customs
		case "d" : deleteGraphInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listGraphCustoms.php"); break; #Delete n Graph Customs
		case "cm" : require_once($path."listMetrics.php"); break; #Show Metrics
		case "mm" : require_once($path."listMetrics.php"); break; #Modify a Metric
		case "dm" : deleteMetricInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listMetrics.php"); break; #Delete n Metrics
		case "up" : upMetricInDB($graph_id, $compo_id); require_once($path."listMetrics.php"); break; #Metric Up
		case "down" : downMetricInDB($graph_id, $compo_id); require_once($path."listMetrics.php"); break; #Metric Down
		case "wup" : upMetricInDB($graph_id, $compo_id); require_once($path."viewGraphCustoms.php"); break; #Watch aGraph Customs
		case "wdown" : downMetricInDB($graph_id, $compo_id);require_once($path."viewGraphCustoms.php"); break; #Watch aGraph Customs
		default : require_once($path."listGraphCustoms.php"); break;
	}
?>