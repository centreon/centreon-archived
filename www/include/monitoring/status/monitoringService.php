<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	if (!isset($oreon)) {
		exit();
	}

	require_once './class/centreonDuration.class.php';
	include_once("./include/monitoring/common-Func.php");
	include_once("./include/monitoring/external_cmd/cmd.php");

	/*
	 * Init Continue Value
	 */
	$continue = true;

	/*
	 * DB Connect
	 */
	include_once("./class/centreonDB.class.php");

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	if (!isset($_GET["cmd"]) && isset($_POST["cmd"])) {
		$param = $_POST;
	} else {
		$param = $_GET;
	}

	if (isset($param["cmd"]) && $param["cmd"] == 15 && isset($param["author"]) && isset($param["en"]) && $param["en"] == 1){
		if (!isset($param["sticky"])) {
			$param["sticky"] = 0;
		}
		if (!isset($param["notify"])) {
			$param["notify"] = 0;
		}
		if (!isset($param["persistent"])) {
			$param["persistent"] = 0;
		}
		acknowledgeService($param);
	} else if(isset($param["cmd"]) && $param["cmd"] == 15 && isset($param["author"]) && isset($param["en"]) && $param["en"] == 0) {
		acknowledgeServiceDisable();
	}

	if (isset($param["cmd"]) && $param["cmd"] == 16 && isset($param["output"])) {
		submitPassiveCheck();
	}

	if ($o == "svcSch") {
		$param["sort_types"] = "next_check";
		$param["order"] = "sort_asc";
	}

	$path = "./include/monitoring/status/";
	$metaservicepath = $path."service.php";

	$pathRoot 		= "./include/monitoring/";
	$pathExternal 	= "./include/monitoring/external_cmd/";
	$pathDetails	= "./include/monitoring/objectDetails/";

	/*
	 * Special Paths
	 */
	$svc_path 	= $path."Services/";
	$hg_path 	= $path."ServicesHostGroups/";
	$sg_path 	= $path."ServicesServiceGroups/";
	$meta_path 	= $path."Meta/";
	$path_sch 	= $path."Scheduling/";

	if ($centreon->broker->getBroker() != "broker") {
		$pearDBndo = new CentreonDB("ndo");

		/*
		 * Check NDO connection
		 */
		if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
			print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
			$continue = false;
		}

		/*
		 * Check table ACL exists
		 */
		if ($err_msg = table_not_exists("centreon_acl")) {
			print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
			$continue = false;
		}
	}

	if ($continue) {
		switch ($o)	{
			/*
			 * View of Service
			 */
			case "svc" 			:
				require_once($svc_path."service.php");
				break;
			case "svcpb" 		:
				require_once($svc_path."service.php");
				break;
			case "svc_warning" 	:
				require_once($svc_path."service.php");
				break;
			case "svc_critical" :
				require_once($svc_path."service.php");
				break;
			case "svc_unknown" 	:
				require_once($svc_path."service.php");
				break;
			case "svc_ok" 		:
				require_once($svc_path."service.php");
				break;
            case "svc_pending" 		:
				require_once($svc_path."service.php");
				break;
			case "svc_unhandled":
				require_once($svc_path."service.php");
				break;
			/*
			 * Special Views
			 */
			case "svcd" 		:
				require_once($pathDetails."serviceDetails.php");
				break;
			case "svcak" 		:
				require_once("./include/monitoring/acknowlegement/serviceAcknowledge.php");
				break;
			case "svcpc" 		:
				require_once("./include/monitoring/submitPassivResults/servicePassiveCheck.php");
				break;

			case "svcgrid" 		:
				require_once($svc_path."serviceGrid.php");
				break;
			case "svcOV" 		:
				require_once($svc_path."serviceGrid.php");
				break;
			case "svcSum" 		:
				require_once($svc_path."serviceSummary.php");
				break;
			/*
			 * View by Service Groups
			 */
			case "svcgridSG" 	:
				require_once($sg_path."serviceGridBySG.php");
				break;
			case "svcOVSG" 		:
				require_once($sg_path."serviceGridBySG.php");
				break;
			case "svcSumSG" 	:
				require_once($sg_path."serviceSummaryBySG.php");
				break;

			/*
			 * View By hosts groups
			 */
			case "svcgridHG" 	:
				require_once($hg_path."serviceGridByHG.php");
				break;
			case "svcOVHG" 		:
				require_once($hg_path."serviceGridByHG.php");
				break;
			case "svcSumHG" 	:
				require_once($hg_path."serviceSummaryByHG.php");
				break;
			/*
			 * Meta Services
			 */
			case "meta" 		:
				require_once($meta_path."/metaService.php");
				break;
			/*
			 * Scheduling Queue
			 */
			case "svcSch" 		:
				require_once($path_sch."serviceSchedule.php");
				break;
			default 			:
				require_once($svc_path."service.php");
				break;
		}
	}
?>