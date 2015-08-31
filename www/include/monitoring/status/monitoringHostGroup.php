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

	$continue = true;

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$path_hg = "./include/monitoring/status/HostGroups/";

	$pathDetails = "./include/monitoring/objectDetails/";

	include_once("./class/centreonDB.class.php");

	if ($oreon->broker->getBroker() == "ndo") {
		$pearDBndo = new CentreonDB("ndo");
		if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
			print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
			$continue = false;
		}
	}

	if ($continue) {
		switch ($o)	{
			case "hg" 	: require_once($path_hg."hostGroup.php"); break;
			case "hgpb" : require_once($path_hg."hostGroup.php"); break;
			case "hgd" 	: require_once($pathDetails."hostgroupDetails.php"); break;
			default 	: require_once($path_hg."hostGroup.php"); break;
		}
	}
?>