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

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path."www/class/centreonDB.class.php");
	include_once($centreon_path."www/class/centreonXML.class.php");

	$pearDB = new CentreonDB();
	$pearDBO = new CentreonDB("centstorage");

	if (isset($_GET["sid"])){
		$sid = CentreonDB::escape($_GET["sid"]);
		$res = $pearDB->query("SELECT * FROM session WHERE session_id = '" . $sid . "'");
		if (!$session = $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	isset($_GET["index"]) ? $index = htmlentities($_GET["index"], ENT_QUOTES, "UTF-8") : $index = NULL;
	isset($_POST["index"]) ? $index = htmlentities($_POST["index"], ENT_QUOTES, "UTF-8") : $index = $index;

	$path = "./include/views/graphs/graphODS/";

	$period = (isset($_POST["period"])) ? htmlentities($_POST["period"], ENT_QUOTES, "UTF-8") : "today";
	$period = (isset($_GET["period"])) ? htmlentities($_GET["period"], ENT_QUOTES, "UTF-8") : $period;

	$DBRESULT = $pearDBO->query("SELECT host_name, service_description FROM index_data WHERE id = '$index'");
	while ($res = $DBRESULT->fetchRow()){
		$hName = $res["host_name"];
		$sName = $res["service_description"];
	}

	header("Content-Type: application/xml");
	if (isset($hName) && isset($sName))
		header("Content-disposition: filename=".$hName."_".$sName.".xml");
	else
		header("Content-disposition: filename=".$index.".xml");

	$DBRESULT = $pearDBO->query("SELECT metric_id FROM metrics, index_data WHERE metrics.index_id = index_data.id AND id = '$index'");
	while ($index_data = $DBRESULT->fetchRow()) {
		$DBRESULT2 = $pearDBO->query("SELECT ctime,value FROM data_bin WHERE id_metric = '".$index_data["metric_id"]."' AND ctime >= '".htmlentities($_GET["start"], ENT_QUOTES, "UTF-8")."' AND ctime < '".htmlentities($_GET["end"], ENT_QUOTES, "UTF-8")."'");
		while ($data = $DBRESULT2->fetchRow()) {
			if (!isset($datas[$data["ctime"]]))
				$datas[$data["ctime"]] = array();
			$datas[$data["ctime"]][$index_data["metric_id"]] = $data["value"];
		}
	}

	$buffer = new CentreonXML();
	$buffer->startElement("root");
	$buffer->startElement("datas");
	foreach ($datas as $key => $tab){
		$buffer->startElement("data");
		$buffer->writeAttribute("no", $key);
		foreach($tab as $value)
			$buffer->writeElement("metric", $value);
		$buffer->endElement();
	}
	$buffer->endElement();
	$buffer->endElement();
	$buffer->output();
	exit();
?>