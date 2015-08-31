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
	if (!isset($oreon))
		exit();

	if (isset($_POST["limit"]) && $_POST["limit"])
		$limit = $_POST["limit"];
	else if (isset($_GET["limit"]))
		$limit = $_GET["limit"];
	else if (!isset($_POST["limit"]) && !isset($_GET["limit"]) && isset($oreon->historyLimit[$url]))
		$limit = $oreon->historyLimit[$url];
	else {
		if (($p >= 200 && $p < 300) || ($p >= 20000 && $p < 30000)){
			$DBRESULT = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'maxViewMonitoring'");
			$gopt = $DBRESULT->fetchRow();		
			$limit = myDecode($gopt["value"]);
		} else {
			$DBRESULT = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'maxViewConfiguration'");
			$gopt = $DBRESULT->fetchRow();		
			$limit = myDecode($gopt["value"]);
		}
	}

	if (isset($_POST["num"]) && $_POST["num"])
		$num = $_POST["num"];
	else if (isset($_GET["num"]) && $_GET["num"])
		$num = $_GET["num"];
	else if (!isset($_POST["num"]) && !isset($_GET["num"]) && isset($oreon->historyPage[$url]))
		$num = $oreon->historyPage[$url];
	else 
		$num = 0;
	
	global $search;
?>