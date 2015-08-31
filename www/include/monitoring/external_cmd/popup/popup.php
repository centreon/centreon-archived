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

	require_once "@CENTREON_ETC@/centreon.conf.php";
	//require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path . "www/class/centreonSession.class.php";
	require_once $centreon_path . "www/class/centreon.class.php";
	require_once $centreon_path . "www/class/centreonDB.class.php";
	require_once $centreon_path . "www/class/centreonLang.class.php";
	require_once $centreon_path . "www/include/common/common-Func.php";

	$pearDB = new CentreonDB();

	session_start();
	$oreon = $_SESSION['centreon'];
    

	$centreonLang = new CentreonLang($centreon_path, $oreon);
	$centreonLang->bindLang();

	if (!isset($oreon) || !isset($_GET['o']) || !isset($_GET['cmd']) || !isset($_GET['p'])) {
		exit;
	}

	if (isset($_GET["sid"])){
		$sid = htmlentities($_GET["sid"], ENT_QUOTES, "UTF-8");
		$res = $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session = $res->fetchRow())
			exit;
	} else {
		exit;
	}

	define('SMARTY_DIR', $centreon_path . 'GPL_LIB/Smarty/libs/');

	require_once SMARTY_DIR . "Smarty.class.php";

	$o = htmlentities($_GET['o'], ENT_QUOTES, "UTF-8");
	$p = htmlentities($_GET['p'], ENT_QUOTES, "UTF-8");
	$cmd = htmlentities($_GET['cmd'], ENT_QUOTES, "UTF-8");

	if ($cmd == 70 || $cmd == 72) {
		require_once $centreon_path . 'www/include/monitoring/external_cmd/popup/massive_ack.php';
	} else if ($cmd == 74 || $cmd == 75) {
		require_once $centreon_path . 'www/include/monitoring/external_cmd/popup/massive_downtime.php';
	}
	exit();
?>