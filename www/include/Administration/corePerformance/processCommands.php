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
	require_once $centreon_path . "/www/class/centreonSession.class.php";
	require_once $centreon_path . "/www/class/centreon.class.php";
	require_once $centreon_path . "/www/class/centreonXML.class.php";

	CentreonSession::start();

	if (!isset($_SESSION["centreon"]) || !isset($_GET["poller"]) || !isset($_GET["cmd"]) || !isset($_GET["sid"]) || !isset($_GET["type"])) {
		exit();
	}

	/*
	 * Centcore pipe path
	 */
	$centcore_pipe = "@CENTREON_VARLIB@/centcore.cmd";
	if ($centcore_pipe == "/centcore.cmd") {
		$centcore_pipe = "/var/lib/centreon/centcore.cmd";
	}

	/*
	 * Get Session informations
	 */
	$oreon = $_SESSION["centreon"];

	$poller =  htmlentities($_GET["poller"], ENT_QUOTES, "UTF-8");
	$cmd =     htmlentities($_GET["cmd"], ENT_QUOTES, "UTF-8");
	$sid =     htmlentities($_GET["sid"], ENT_QUOTES, "UTF-8");
	$type =    htmlentities($_GET["type"], ENT_QUOTES, "UTF-8");

	$pearDB = new CentreonDB();
	$DBRESULT = $pearDB->query("SELECT session_id FROM session WHERE session.session_id = '".$sid."'");
	if (!$DBRESULT->numRows()) {
		exit();
	}

	if (!$oreon->user->access->checkAction($cmd)) {
		exit();
	}

	/*
     * Get Init Script
     */
    $DBRESULT = $pearDB->query("SELECT id, init_script FROM nagios_server WHERE localhost = '1' AND ns_activate = '1'");
    $serveurs = $DBRESULT->fetchrow();
    unset($DBRESULT);
    (isset($serveurs["init_script"])) ? $nagios_init_script = $serveurs["init_script"] : $nagios_init_script = "/etc/init.d/nagios";
    unset($serveurs);

	/*
	 * Init Command Object
	 */
    $command = new CentreonExternalCommand($oreon);

	/*
	 * Check if command is start or not
	 */
	if ($cmd == "global_start") {
	    if (isset($command->localhost_tab[$poller])) {
            shell_exec("sudo " . $nagios_init_script . " start");
		} else {
			shell_exec("echo 'START:".$poller."' >> $centcore_pipe");
		}
	} else {
    	$cmd_tab = $command->getExternalCommandList();
    	$command->set_process_command($cmd_tab[$cmd][$type], $poller);
    	$result = $command->write();
	}

	/*
	 * Start XML
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("root");
	$buffer->writeElement("result", $result);
	$buffer->writeElement("cmd", $cmd);

	$type ? $type = 0 : $type = 1;
	$buffer->writeElement("actiontype", $type);

	$buffer->endElement();

	/*
	 * Send headers
	 */
	header('Content-type: text/xml; charset=utf-8');
	header('Cache-Control: no-cache, must-revalidate');

	$buffer->output();
?>
