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
 * SVN : $URL:$
 * SVN : $Id:$
 *
 */

ini_set("display_errors", "Off");
require_once "@CENTREON_ETC@/centreon.conf.php";

if (!isset($_POST['poller']) || !isset($_POST['mode']) || !isset($_POST['sid'])) {
    exit;
}

/**
 * List of error from php
 */
global $generatePhpErrors;
$generatePhpErrors = array();

/**
 * The error handler for get error from PHP
 *
 * @see set_error_handler
 */
function log_error($errno, $errstr, $errfile, $errline)
{
    global $generatePhpErrors;
    if (!(error_reporting() & $errno)) {
        return;
    }

    switch ($errno) {
        case E_ERROR:
        case E_USER_ERROR:
        case E_CORE_ERROR:
            $generatePhpErrors[] = array('error', $errstr);
            break;
        case E_WARNING:
        case E_USER_WARNING:
        case E_CORE_WARNING:
            $generatePhpErrors[] = array('warning', $errstr);
            break;
    }
    return true;
}

try {
    $poller = $_POST['poller'];

    $ret = array();
    $ret['host'] = $poller;
    $ret['restart_mode'] = $_POST['mode'];

    chdir($centreon_path . "www");
    $nagiosCFGPath = "$centreon_path/filesGeneration/nagiosCFG/";
    $centreonBrokerPath = "$centreon_path/filesGeneration/broker/";
    require_once $centreon_path . "www/include/configuration/configGenerate/DB-Func.php";
    require_once $centreon_path . "www/class/centreonDB.class.php";
    require_once $centreon_path . "www/class/centreonSession.class.php";
    require_once $centreon_path . "www/class/centreon.class.php";
    require_once $centreon_path . "www/class/centreonXML.class.php";

    session_start();
    if ($_POST['sid'] != session_id()) {
        exit;
    }
    $oreon = $_SESSION['centreon'];
    $centreon = $oreon;

    /*  Set new error handler */
    set_error_handler('log_error');

    $centcore_pipe = "@CENTREON_VARLIB@/centcore.cmd";
	if ($centcore_pipe == "/centcore.cmd") {
		$centcore_pipe = "/var/lib/centreon/centcore.cmd";
	}

    $xml = new CentreonXML();
    $pearDB = new CentreonDB();

    $stdout = "";
    if (!isset($msg_restart)) {
        $msg_restart = array();
    }

    /*
     * Get Init Script
     */
    $DBRESULT = $pearDB->query("SELECT id, init_script FROM nagios_server WHERE localhost = '1' AND ns_activate = '1'");
    $serveurs = $DBRESULT->fetchrow();
    unset($DBRESULT);
    (isset($serveurs["init_script"])) ? $nagios_init_script = $serveurs["init_script"] : $nagios_init_script = "/etc/init.d/nagios";
    unset($serveurs);

    $tab_server = array();
    $DBRESULT_Servers = $pearDB->query("SELECT `name`, `id`, `localhost` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name` ASC");
    while ($tab = $DBRESULT_Servers->fetchRow()) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
            $tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
        }
    }

    /*
     * Get broker init script
     */
    $DBRESULTN = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'broker_correlator_script'");
    $data = $DBRESULTN->fetchRow();
    if (isset($data['value']) && trim($data['value']) != '') {
      	/*
         * Restart
         */
        shell_exec("sudo " . $data['value'] . " reload");
    }
    $DBRESULTN->free();
    unset($data);

    foreach ($tab_server as $host) {
    	if ($ret["restart_mode"] == 1) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                $msg_restart[$host["id"]] = shell_exec("sudo " . $nagios_init_script . " reload");
            } else {
                system("echo 'RELOAD:".$host["id"]."' >> $centcore_pipe", $return);
                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if ($return != 0) {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A reload signal has been sent to ".$host["name"]."\n");
                } else {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>Cannot send signal to ".$host["name"].". Check $centcore_pipe properties.\n");
                }
            }
        } else if ($ret["restart_mode"] == 2) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                $msg_restart[$host["id"]] = shell_exec("sudo " . $nagios_init_script . " restart");
            } else {
                system("echo \"RESTART:".$host["id"]."\" >> $centcore_pipe", $return);

                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if ($return != 0) {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A restart signal has been sent to ".$host["name"]."\n");
                } else {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>Cannot send signal to ".$host["name"].". Check $centcore_pipe properties.\n");
                }
            }
        } else if ($ret["restart_mode"] == 3) {
            /*
             * Require external function files.
             */
            require_once "./include/monitoring/external_cmd/functions.php";
            write_command(" RESTART_PROGRAM", $host["id"]);
            if (!isset($msg_restart[$host["id"]])) {
                $msg_restart[$host["id"]] = "";
            }
            $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A restart signal has been sent to ".$host["name"]."\n");
        }
        $DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$host["id"]."'");
    }

    foreach ($msg_restart as $key => $str) {
        $msg_restart[$key] = str_replace("\n", "<br>", $str);
    }
    $xml->startElement("response");
    $xml->writeElement("status", "<b><font color='green'>OK</font></b>");
} catch (Exception $e) {
    $xml->startElement("response");
    $xml->writeElement("status", "<b><font color='red'>NOK</font></b>");
    $xml->writeElement("error", $e->getMessage());
}
/* Restore default error handler */
restore_error_handler();

/*
 * Add error form php
 */
$xml->startElement('errorsPhp');
foreach ($generatePhpErrors as $error) {
    if ($error[0] == 'error') {
        $errmsg = '<span style="color: red;">Error</span><span style="margin-left: 5px;">' . $error[1] . '</span>';
    } else {
        $errmsg = '<span style="color: orange;">Warning</span><span style="margin-left: 5px;">' . $error[1] . '</span>';
    }
    $xml->writeElement('errorPhp', $errmsg);
}
$xml->endElement();

$xml->endElement();

header('Content-Type: application/xml');
header('Cache-Control: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
?>