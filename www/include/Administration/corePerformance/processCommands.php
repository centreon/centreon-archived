<?php
/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
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
			shell_exec("echo 'START:".$poller."' >> $centcore_pipe", $return);
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