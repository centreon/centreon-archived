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
	//require_once("@CENTREON_ETC@/centreon.conf.php");

	require_once $centreon_path . "www/class/centreonSession.class.php";
	require_once $centreon_path . "www/class/centreon.class.php";
	require_once $centreon_path . "www/class/centreonDB.class.php";
	require_once $centreon_path . "www/class/centreonGMT.class.php";

	session_start();
	$oreon = $_SESSION['centreon'];

	global $oreon, $pearDB;

	/*
	 * Connect to DB
	 */
	$pearDB = new CentreonDB();

	/*
	 * GMT management
	 */
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession($_GET["sid"], $pearDB);

	require_once $centreon_path . "www/include/common/common-Func.php";
	require_once $centreon_path . "www/include/monitoring/common-Func.php";

	if (!isset($oreon))
		exit();

	include_once $centreon_path . "www/include/monitoring/external_cmd/functionsPopup.php";

	if (isset($_GET["select"]) && isset($_GET["sid"])) {
		$is_admin = isUserAdmin(htmlentities($_GET['sid'], ENT_QUOTES, "UTF-8"));
		foreach ($_GET["select"] as $key => $value){
			if (isset($_GET["cmd"])) {
				switch ($_GET["cmd"]) {
					case 70:	massiveServiceAck($key); break;
					case 72:	massiveHostAck($key); break;
					case 74:	massiveServiceDowntime($key); break;
					case 75:	massiveHostDowntime($key); break;
				}
			}
		}
	}
?>
