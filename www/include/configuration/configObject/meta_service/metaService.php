<?php
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	$lcaHost = getLcaHostByID($pearDB);
	$lcaHoststr = getLCAHostStr($lcaHost["LcaHost"]);
	$lcaHostGroupstr = getLcaHGStr($lcaHost["LcaHostGroup"]);
	$lcaHostByName = getLcaHostByName($pearDB);
	$isRestreint = HadUserLca($pearDB);
	
	isset($_GET["meta_id"]) ? $cG = $_GET["meta_id"] : $cG = NULL;
	isset($_POST["meta_id"]) ? $cP = $_POST["meta_id"] : $cP = NULL;
	$cG ? $meta_id = $cG : $meta_id = $cP;
	
	isset($_GET["host_name"]) ? $cG = $_GET["host_name"] : $cG = NULL;
	isset($_POST["host_name"]) ? $cP = $_POST["host_name"] : $cP = NULL;
	$cG ? $host_name = $cG : $host_name = $cP;
	
	isset($_GET["host_id"]) ? $cG = $_GET["host_id"] : $cG = NULL;
	isset($_POST["host_id"]) ? $cP = $_POST["host_id"] : $cP = NULL;
	$cG ? $host_id = $cG : $host_id = $cP;

	isset($_GET["metric_id"]) ? $cG = $_GET["metric_id"] : $cG = NULL;
	isset($_POST["metric_id"]) ? $cP = $_POST["metric_id"] : $cP = NULL;
	$cG ? $metric_id = $cG : $metric_id = $cP;

	isset($_GET["msr_id"]) ? $cG = $_GET["msr_id"] : $cG = NULL;
	isset($_POST["msr_id"]) ? $cP = $_POST["msr_id"] : $cP = NULL;
	$cG ? $msr_id = $cG : $msr_id = $cP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;
			
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir	
	$path = "./include/configuration/configObject/meta_service/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formMetaService.php"); break; #Add an Meta Service
		case "w" : require_once($path."formMetaService.php"); break; #Watch an Meta Service
		case "c" : require_once($path."formMetaService.php"); break; #Modify an Meta Service		
		case "s" : enableMetaServiceInDB($meta_id); require_once($path."listMetaService.php"); break; #Activate a Meta Service
		case "u" : disableMetaServiceInDB($meta_id); require_once($path."listMetaService.php"); break; #Desactivate a Meta Service
		case "d" : deleteMetaServiceInDB(isset($select) ? $select : array()); require_once($path."listMetaService.php"); break; #Delete n Meta Servive		
		case "m" : multipleMetaServiceInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listMetaService.php"); break; #Duplicate n Meta Service
		case "ci" : require_once($path."listMetric.php"); break; #Manage Service of the MS
		case "as" : require_once($path."metric.php"); break; # Add Service to a MS
		case "cs" : require_once($path."metric.php"); break; # Change Service to a MS
		case "ss" : enableMetricInDB($msr_id); require_once($path."listMetric.php"); break; #Activate a Metric
		case "us" : disableMetricInDB($msr_id); require_once($path."listMetric.php"); break; #Desactivate a Metric
		case "ws" : require_once($path."metric.php"); break; # View Service to a MS
		case "ds" : deleteMetricInDB(isset($select) ? $select : array()); require_once($path."listMetric.php"); break; #Delete n Metric		
		default : require_once($path."listMetaService.php"); break;
	}
?>