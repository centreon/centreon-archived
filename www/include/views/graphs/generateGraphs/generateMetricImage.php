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

	/**
	 * Include config file
	 */
	include "@CENTREON_ETC@/centreon.conf.php";

	require_once "$centreon_path/www/class/centreonGraph.class.php";

	/**
	 * Create XML Request Objects
	 */
	$obj = new CentreonGraph($_GET["session_id"], $_GET["index"], 0, 1);

	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		;
	} else {
		$obj->displayError();
	}

	require_once $centreon_path."www/include/common/common-Func.php";

	/**
	 * Set One curve
	 **/
	$obj->onecurve = true;

	/**
	 * Set metric id
	 */
	if (isset($_GET["metric"])) {
		$obj->setMetricList($_GET["metric"]);
	}

	/**
	 * Set arguments from GET
	 */
	$obj->setRRDOption("start", $obj->checkArgument("start", $_GET, time() - (60*60*48)) );
	$obj->setRRDOption("end",   $obj->checkArgument("end", $_GET, time()) );

 	$obj->GMT->getMyGMTFromSession($obj->session_id, $pearDB);

	/**
	 * Template Management
	 */
 	if (isset($_GET["template_id"])) {
		$obj->setTemplate($_GET["template_id"]);
 	} else {
 		$obj->setTemplate();
 	}

	$obj->init();
	if (isset($_GET["flagperiod"])) {
		$obj->setCommandLineTimeLimit($_GET["flagperiod"]);
	}

	$obj->initCurveList();

	/**
	 * Comment time
	 */
	$obj->setOption("comment_time");

	/**
	 * Create Legende
	 */
	$obj->createLegend();


	/**
	 * Display Images Binary Data
	 */
	$obj->displayImageFlow();
?>
