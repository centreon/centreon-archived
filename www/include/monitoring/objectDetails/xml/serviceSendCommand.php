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
	require_once $centreon_path . "/www/class/centreonExternalCommand.class.php";
	require_once $centreon_path . "/www/class/centreonDB.class.php";
	require_once $centreon_path . "/www/class/centreonHost.class.php";
	require_once $centreon_path . "/www/class/centreonService.class.php";
	require_once $centreon_path . "/www/class/centreonACL.class.php";
	require_once $centreon_path . "/www/class/centreonSession.class.php";
	require_once $centreon_path . "/www/class/centreon.class.php";
	require_once $centreon_path . "/www/class/centreonXML.class.php";

	CentreonSession::start();
	$oreon = $_SESSION["centreon"];
	if (!isset($_SESSION["centreon"]) || !isset($_GET["host_id"]) || !isset($_GET["service_id"]) || !isset($_GET["cmd"]) || !isset($_GET["sid"]) || !isset($_GET["actiontype"]))
		exit();

	$pearDB = new CentreonDB();
	$hostObj = new CentreonHost($pearDB);
	$svcObj = new CentreonService($pearDB);
	$host_id = $_GET["host_id"];
	$svc_id = $_GET["service_id"];
	$poller = $hostObj->getHostPollerId($host_id);
	$cmd = $_GET["cmd"];
	$sid = $_GET["sid"];
	$act_type = $_GET["actiontype"];

	$pearDB = new CentreonDB();

	$DBRESULT = $pearDB->query("SELECT session_id FROM session WHERE session.session_id = '".$sid."'");
	if (!$DBRESULT->numRows())
		exit();
	if (!$oreon->user->access->checkAction($cmd))
		exit();

	$command = new CentreonExternalCommand($oreon);
	$cmd_list = $command->getExternalCommandList();

	$send_cmd = $cmd_list[$cmd][$act_type];

	$hName = str_replace("#S#", "/", $hostObj->getHostName($host_id));
	$hName = str_replace("#BS#", "\\", $hName);
	$svcDesc = str_replace("#S#", "/", $svcObj->getServiceDesc($svc_id));
	$svcDesc = str_replace("#BS#", "\\", $svcDesc);

	$send_cmd .= ";" . $hName . ";" . $svcDesc . ";" . time();
	$command->set_process_command($send_cmd, $poller);
	$act_type ? $return_type = 0 : $return_type = 1;
	$result = $command->write();
	$buffer = new CentreonXML();
	$buffer->startElement("root");
		$buffer->writeElement("result", $result);
		$buffer->writeElement("cmd", $cmd);
		$buffer->writeElement("actiontype", $return_type);
	$buffer->endElement();
	header('Content-type: text/xml; charset=utf-8');
	header('Cache-Control: no-cache, must-revalidate');
	$buffer->output();
?>