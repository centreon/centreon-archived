<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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
		acknowledgeService($lang);
	} else if(isset($_GET["cmd"]) && $_GET["cmd"] == 15 && isset($_GET["author"]) && isset($_GET["en"]) && $_GET["en"] == 0)
		acknowledgeServiceDisable($lang);

	if(isset($_GET["cmd"]) && $_GET["cmd"] == 16 && isset($_GET["output"]))
		submitPassiveCheck($lang);
	
	$ndo = 0;
	
	if (isset($ndo) && !$ndo)
		$path = "./include/monitoring/status/status-log/";
		include("./include/monitoring/status/resume.php");
	}
	else
		$path = "./include/monitoring/status/status-ndo/";
		
	$pathRoot = "./include/monitoring/";
	$pathExternal = "./include/monitoring/external_cmd/";
	$pathDetails = "./include/monitoring/objectDetails/";

	switch ($o)	{
		case "svc" 			: require_once($path."service.php"); 					break; 
		
		case "svcpb" 		: require_once($path."service_problem.php");			break;
		case "svc_warning" 	: require_once($path."service_problem.php");			break;
		case "svc_critical" : require_once($path."service_problem.php");			break;
		case "svc_unknown" 	: require_once($path."service_problem.php");			break;
		case "svc_ok" 		: require_once($path."service_problem.php");			break;
		
		case "svcd" 		: require_once($pathDetails."serviceDetails.php"); 		break; 
		case "svcak" 		: require_once($pathExternal."serviceAcknowledge.php"); break; 
		case "svcpc" 		: require_once($pathExternal."servicePassiveCheck.php");break; 
		
		case "svcgrid" 		: require_once($path."serviceGrid.php"); 				break; 
		case "svcOV" 		: require_once($path."serviceOverview.php"); 			break; 
		case "svcSum" 		: require_once($path."serviceSummary.php"); 			break; 
		
		case "svcgridSG" 	: require_once($path."serviceGridBySG.php"); 			break; 
		case "svcOVSG" 		: require_once($path."serviceOverviewBySG.php"); 		break; 
		case "svcSumSG" 	: require_once($path."serviceSummaryBySG.php"); 		break; 
		
		case "svcgridHG" 	: require_once($path."serviceGridByHG.php"); 			break; 
		case "svcOVHG" 		: require_once($path."serviceOverviewByHG.php"); 		break; 
		case "svcSumHG" 	: require_once($path."serviceSummaryByHG.php"); 		break; 
		
		case "meta" 		: require_once($path."metaService.php"); 				break;
		case "svcSch" 		: require_once($path."serviceSchedule.php"); 			break; 
		default 			: require_once($path."service.php"); 					break;
	}
?>