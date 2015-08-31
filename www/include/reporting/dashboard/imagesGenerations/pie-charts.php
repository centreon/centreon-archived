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

	include_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."www/class/centreonSession.class.php";
	require_once $centreon_path."www/class/centreon.class.php";

	CentreonSession::start();
	if (!isset($_SESSION['centreon'])) {
	    die();
	}
	$oreon = $_SESSION["centreon"];

	// -----------------------------------------------------
	$value = $_GET["value"];
	foreach ($value as $key => $val)	{
		if ($val)
			if (!isset($oreon->optGen["color_".strtolower($key)])) {
				//$color[] = $oreon->optGen["color_undetermined"];
				$color[] = '#F0F0F0';
				$val = str_replace(",", ".", $val);
				$data[] = $val;
				$legend[] = "";
			} else {
				$color[] = $oreon->optGen["color_".strtolower($key)];
				$val = str_replace(",", ".", $val);
				$data[] = $val;
				$legend[] = "";
			}
	}
	include_once($centreon_path . '/www/lib/ofc-library/open-flash-chart.php' );

	$g = new graph();
	$g->bg_colour = '#F3F6F6';
	//
	// PIE chart, 60% alpha
	//
	$g->pie(60,'#505050','#000000');
	//
	// pass in two arrays, one of data, the other data labels
	//

	$g->pie_values( $data, $legend );
	//
	// Colours for each slice, in this case some of the colours
	// will be re-used (3 colurs for 5 slices means the last two
	// slices will have colours colour[0] and colour[1]):
	//

	$g->pie_slice_colours($color);

	$g->set_tool_tip( '#val#%' );

	if (isset($_GET["service_name"]) && isset($_GET["host_name"]))
		$g->title( utf8_encode($_GET["service_name"]) . " on " . utf8_encode($_GET["host_name"]), '{font-size:15px; color: #424242}' );
	else if (isset($_GET["host_name"]))
		$g->title( utf8_encode($_GET["host_name"]), '{font-size:18px; color: #424242}' );
	header("Cache-Control: cache, must-revalidate");
	header("Pragma: public");
	echo $g->render();

?>