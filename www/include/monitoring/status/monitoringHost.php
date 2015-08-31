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
	include_once "./include/monitoring/common-Func.php";
	include_once "./include/monitoring/external_cmd/cmd.php";

	/*
	 * Init Continue Value
	 */
	$continue = true;

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$path = "./include/monitoring/status/Hosts/";
	$path_hg = "./include/monitoring/status/HostGroups/";

	$pathRoot = "./include/monitoring/";
	$pathDetails = "./include/monitoring/objectDetails/";
	$pathTools = "./include/tools/";

	if (!isset($_GET["cmd"]) && isset($_POST["cmd"])) {
		$param = $_POST;
	} else {
		$param = $_GET;
	}

	if (isset($param["cmd"]) && $param["cmd"] == 14 && isset($param["author"]) && isset($param["en"]) && $param["en"] == 1) {
		if (!isset($param["sticky"])) {
			$param["sticky"] = 0;
		}
		if (!isset($param["notify"])) {
			$param["notify"] = 0;
		}
		if (!isset($param["persistent"])) {
			$param["persistent"] = 0;
		}
		if (!isset($param["ackhostservice"])) {
			$param["ackhostservice"] = 0;
		}
		acknowledgeHost($param);
	} else if (isset($param["cmd"]) && $param["cmd"] == 14 && isset($param["author"]) && isset($param["en"]) && $param["en"] == 0) {
		acknowledgeHostDisable();
	}

	if (isset($param["cmd"]) && $param["cmd"] == 16 && isset($param["output"])) {
		submitHostPassiveCheck();
	}

	if ($min){
		switch ($o)	{
			default : require_once($pathTools."tools.php"); break;
		}
	} else {
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

		/*
		 * Now route to pages or Actions
		 */
		if ($continue) {
			switch ($o)	{
				case "h" 	:
					require_once($path."host.php");
					break;
				case "hpb" 	:
					require_once($path."host.php");
					break;
				case "h_unhandled" 	:
					require_once($path."host.php");
					break;
				case "hd" 	:
					require_once($pathDetails."hostDetails.php");
					break;
				case "hpc" 		:
					require_once("./include/monitoring/submitPassivResults/hostPassiveCheck.php");
					break;
				case "hak" 	:
					require_once($pathRoot."acknowlegement/hostAcknowledge.php");
					break;
				default 	:
					require_once($path."host.php");
					break;
			}
		}
	}
?>