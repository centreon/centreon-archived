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

	if (!isset($oreon))
		exit();

	isset($_GET["service_id"]) ? $cG = $_GET["service_id"] : $cG = NULL;
	isset($_POST["service_id"]) ? $cP = $_POST["service_id"] : $cP = NULL;
	$cG ? $service_id = $cG : $service_id = $cP;

	isset($_GET["service_description"]) ? $cG = $_GET["service_description"] : $cG = NULL;
	isset($_POST["service_description"]) ? $cP = $_POST["service_description"] : $cP = NULL;
	$cG ? $service_description = $cG : $service_description = $cP;

	isset($_GET["host_name"]) ? $cG = $_GET["host_name"] : $cG = NULL;
	isset($_POST["host_name"]) ? $cP = $_POST["host_name"] : $cP = NULL;
	$cG ? $host_name = $cG : $host_name = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/views/graphs/graphODS/";

	#PHP functions
	require_once "./include/common/common-Func.php";
	require_once("./class/centreonDB.class.php");
	$pearDBO = new CentreonDB("centstorage");

	if (isset($_GET["o"]) && $_GET["o"] == "vs")
		require_once($path."graphODSService.php");
	else if (isset($_GET["o"]) && $_GET["o"] == "vz")
		require_once($path."graphODSServiceZoom.php");
	else if (isset($_GET["o"]) && $_GET["o"] == "gp")
		require_once($path."displayODSGraphProperties.php");
	else if (isset($_GET["o"]) && $_GET["o"] == "cp")
		require_once($path."changeODSGraphProperties.php");
	else if (!isset($_GET["o"]) || $_GET["o"] == "")
		require_once($path."graphODSByHost.php");
		
?>