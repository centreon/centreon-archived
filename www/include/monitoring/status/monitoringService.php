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

	require_once './class/other.class.php';
	include_once("./include/monitoring/common-Func.php");
	include_once("./include/monitoring/external_cmd/cmd.php");

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	if (isset($_GET["cmd"]) && $_GET["cmd"] == 15 && isset($_GET["author"]) && isset($_GET["en"]) && $_GET["en"] == 1){
		if (!isset($_GET["notify"]))
			$_GET["notify"] = 0;
		if (!isset($_GET["persistent"]))
			$_GET["persistent"] = 0;
		acknowledgeService();
	} else if(isset($_GET["cmd"]) && $_GET["cmd"] == 15 && isset($_GET["author"]) && isset($_GET["en"]) && $_GET["en"] == 0)
		acknowledgeServiceDisable();

	if (isset($_GET["cmd"]) && $_GET["cmd"] == 16 && isset($_GET["output"]))
		submitPassiveCheck();

	$DBRESULT =& $pearDB->query("SELECT ndo_activate FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	$ndo = $gopt["ndo_activate"];

	if ($o == "svcSch"){
		$_GET["sort_types"] = "next_check";
		$_GET["order"] = "sort_asc";
	}

	$metaservicepath = "metaService.php";
	
	if (isset($ndo) && !$ndo){
		$path = "./include/monitoring/status/status-log/";
		include("./include/monitoring/status/resume.php");
		$problem = "_problem";
		$metaservicepath = $path."metaService.php";
	} else {
		$problem = "";
		$path = "./include/monitoring/status/status-ndo/";
		$metaservicepath = $path."service.php";
	}

	$pathRoot 		= "./include/monitoring/";
	$pathExternal 	= "./include/monitoring/external_cmd/";
	$pathDetails	= "./include/monitoring/objectDetails/";
	
	include_once("./DBNDOConnect.php");
	
	if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) 
		print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
	else {
		switch ($o)	{
			case "svc" 			: require_once($path."service.php"); 					break;
			/*
			 * View of Service
			 */
			case "svcpb" 		: require_once($path."service".$problem.".php");		break;
			case "svc_warning" 	: require_once($path."service".$problem.".php");		break;
			case "svc_critical" : require_once($path."service".$problem.".php");		break;
			case "svc_unknown" 	: require_once($path."service".$problem.".php");		break;
			case "svc_ok" 		: require_once($path."service".$problem.".php");		break;
			/*
			 * Special Views 
			 */
			case "svcd" 		: require_once($pathDetails."serviceDetails.php"); 		break;
			case "svcak" 		: require_once("./include/monitoring/acknowlegement/serviceAcknowledge.php"); break;
			case "svcpc" 		: require_once("./include/monitoring/submitPassivResults/servicePassiveCheck.php");break;
			/*
			 * View Bu hosts groups
			 */
			case "svcgrid" 		: require_once($path."serviceGrid.php"); 				break;
			case "svcOV" 		: require_once($path."serviceGrid.php");	 			break;
			case "svcSum" 		: require_once($path."serviceSummary.php"); 			break;
			/*
			 * View by Service Groups
			 */
			case "svcgridSG" 	: require_once($path."serviceGridBySG.php"); 			break;
			case "svcOVSG" 		: require_once($path."serviceGridBySG.php"); 		break;
			case "svcSumSG" 	: require_once($path."serviceSummaryBySG.php"); 		break;
			
			case "svcgridHG" 	: require_once($path."serviceGridByHG.php"); 			break;
			case "svcOVHG" 		: require_once($path."serviceGridByHG.php"); 		break;
			case "svcSumHG" 	: require_once($path."serviceSummaryByHG.php"); 		break;
			/*
			 * Meta Services
			 */
			case "meta" 		: require_once($metaservicepath); 				break;
			/*
			 * Scheduling Queue
			 */
			case "svcSch" 		: require_once($path."serviceSchedule.php"); 			break;
			default 			: require_once($path."service.php"); 					break;
		}
	}
?>