<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
 
	if (!isset ($oreon))
		exit ();
	
	isset($_GET["service_id"]) ? $sG = $_GET["service_id"] : $sG = NULL;
	isset($_POST["service_id"]) ? $sP = $_POST["service_id"] : $sP = NULL;
	$sG ? $service_id = $sG : $service_id = $sP;
		
	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

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
	$path = "./include/configuration/configObject/service_template_model/";
	$path2 = "./include/configuration/configObject/service/";
	
	#PHP functions
	require_once $path2."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : require_once($path."formServiceTemplateModel.php"); break; #Add a Service Template Model
		case "w" : require_once($path."formServiceTemplateModel.php"); break; #Watch a Service Template Model
		case "c" : require_once($path."formServiceTemplateModel.php"); break; #Modify a Service Template Model
		case "mc" : require_once($path."formServiceTemplateModel.php"); break; #Massive change
		case "s" : enableServiceInDB($service_id); require_once($path."listServiceTemplateModel.php"); break; #Activate a Service Template Model
		case "ms" : enableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break;
		case "u" : disableServiceInDB($service_id); require_once($path."listServiceTemplateModel.php"); break; #Desactivate a Service Template Model
		case "mu" : disableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break;
		case "m" : multipleServiceInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServiceTemplateModel.php"); break; #Duplicate n Service Template Models
		case "d" : deleteServiceInDB(isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break; #Delete n Service Template Models
		default : require_once($path."listServiceTemplateModel.php"); break;
	}
?>