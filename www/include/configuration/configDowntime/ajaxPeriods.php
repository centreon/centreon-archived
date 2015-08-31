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

	header('Content-Type: application/json');
	header('Cache-Control: no-cache');

	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path . "/www/class/centreonDB.class.php";
	require_once $centreon_path . "/www/class/centreonDowntime.class.php";

	$pearDB = new CentreonDB();

	if (isset($_GET['dt_id'])) {
		$id = $_GET['dt_id'];
	} else {
		$id = 0;
	}

	$path = $centreon_path . "/www/include/configuration/configDowntime/";

	$downtime = new CentreonDowntime($pearDB);

	require_once $path . 'json.php';
    if ($id == 0) {
        $periods = array();
    } else {
        $periods = $downtime->getPeriods($id);
    }
	$json = new Services_JSON();
	print $json->encode($periods);
?>