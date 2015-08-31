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

	function get_error($str){
		echo $str."<br />";
		exit(0);
	}

	include_once "@CENTREON_ETC@/centreon.conf.php";
	require_once '../../../class/centreonDB.class.php';

	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");

	if (isset($_GET["sid"])){
		$sid = CentreonDB::escape($_GET["sid"]);
		$res = $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session = $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	isset ($_GET["metric_id"]) ? $mtrcs = htmlentities($_GET["metric_id"], ENT_QUOTES, "UTF-8") : $mtrcs = NULL;
	isset ($_POST["metric_id"]) ? $mtrcs = htmlentities($_POST["metric_id"], ENT_QUOTES, "UTF-8") : $mtrcs = $mtrcs;

	$path = "./include/views/graphs/graphODS/";
	require_once '../../../class/centreonDuration.class.php';
	require_once '../../common/common-Func.php';

	$period = (isset($_POST["period"])) ? htmlentities($_POST["period"], ENT_QUOTES, "UTF-8") : "today";
	$period = (isset($_GET["period"])) ? htmlentities($_GET["period"], ENT_QUOTES, "UTF-8") : $period;

	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$mhost.".csv");

	print "Date;value\n";
	$begin = time() - 26000;

	$res = $pearDB->query("SELECT ctime,value FROM data_bin WHERE id_metric = '".$mtrcs."' AND CTIME >= '".$begin."'");
	while ($data = $res->fetchRow()){
		print $data["ctime"].";".$data["value"].";".date("Y-m-d H:i:s", $data["ctime"])."\n";
	}
	exit();
    
?>