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

    require_once("@CENTREON_ETC@/centreon.conf.php");

	if (isset($_POST["sid"])){

		$path = "$centreon_path/www";

		require_once("$path/class/centreon.class.php");
		require_once("$path/class/centreonSession.class.php");

		session_id($_POST["sid"]);

		CentreonSession::start();

		$centreon = $_SESSION['centreon'];

		if (isset($_POST["limit"]) && isset($_POST["url"]))
			$centreon->historyLimit[$_POST["url"]] = $_POST["limit"];

		if (isset($_POST["page"]) && isset($_POST["url"]))
			$centreon->historyPage[$_POST["url"]] = $_POST["page"];

		if (isset($_POST["search"]) && isset($_POST["url"]))
			$centreon->historySearchService[$_POST["url"]] = addslashes($_POST["search"]);

		if (isset($_POST["search_host"]) && isset($_POST["url"]))
			$centreon->historySearch[$_POST["url"]] = addslashes($_POST["search_host"]);

		if (isset($_POST["search_output"]) && isset($_POST["url"]))
			$centreon->historySearchOutput[$_POST["url"]] = addslashes($_POST["search_output"]);
	} else {
		echo "Can't find SID !";
	}
?>