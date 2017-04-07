<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

ini_set("display_errors", "Off");

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");

require_once _CENTREON_PATH_ . "www/include/configuration/configGenerate/DB-Func.php";
require_once _CENTREON_PATH_ . 'www/class/config-generate/generate.class.php';
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonContactgroup.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonXML.class.php";
require_once _CENTREON_PATH_ . '/www/class/centreonSession.class.php';

$pearDB = new CentreonDB();

/* Check Session */
CentreonSession::start(1);
if (!CentreonSession::checkSession(session_id(), $pearDB)) {
    print "Bad Session";
    exit();
}
$centreon = $_SESSION['centreon'];

if (!isset($_POST['poller']) || !isset($_POST['debug'])) {
    exit();
}

/**
 * List of error from php
 */
global $generatePhpErrors;
$generatePhpErrors = array();

$path = _CENTREON_PATH_ . "www/include/configuration/configGenerate/";
$nagiosCFGPath = _CENTREON_PATH_ . "filesGeneration/engine/";
$centreonBrokerPath = _CENTREON_PATH_ . "filesGeneration/broker/";
$DebugPath = "filesGeneration/engine/";

chdir(_CENTREON_PATH_ . "www");
$username = 'unknown';
if (isset($centreon->user->name)) {
    $username = $centreon->user->name;
}
$xml = new CentreonXML();
$config_generate = new Generate();

$pollers = explode(',', $_POST['poller']);
$debug = ($_POST['debug'] == "true") ? 1 : 0;
$generate = ($_POST['generate'] == "true") ? 1 : 0;

$ret = array();
$ret['host'] = $pollers;
$ret['debug'] = $debug;

/*  Set new error handler */
set_error_handler('log_error');

$okMsg = "<b><font color='green'>OK</font></b>";
$nokMsg = "<b><font color='red'>NOK</font></b>";

$xml->startElement("response");
try {
    $tabs = array();
    if ($generate) {
        $pearDBO = new CentreonDB('centstorage');
        $tabs = $centreon->user->access->getPollerAclConf(array(
            'fields' => array('id', 'name', 'localhost', 'monitoring_engine'),
            'order' => array('name'),
            'keys' => array('id'),
            'conditions' => array('ns_activate' => '1')
        ));
    }

    # Sync contactgroups to ldap
    $cgObj = new CentreonContactgroup($pearDB);
    $cgObj->syncWithLdap();

    # Generate configuration
    if ($pollers == '0') {
        $config_generate->configPollers($username);
    } else {
        foreach ($pollers as $poller) {
            $config_generate->reset();
            $config_generate->configPollerFromId($poller, $username);
        }
    }

    # Debug configuration
    $statusMsg = $okMsg;
    $statusCode = 0;
    if ($debug) {
        $statusCode = printDebug($xml, $tabs);
    }
    if ($statusCode == 1) {
        $statusMsg = $nokMsg;
    }

    $xml->writeElement("status", $statusMsg);
    $xml->writeElement("statuscode", $statusCode);
} catch (Exception $e) {
    $xml->writeElement("status", $nokMsg);
    $xml->writeElement("statuscode", 1);
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
        $errmsg = '<p>
                        <span style="color: red;">Error</span>
                        <span style="margin-left: 5px;">' . $error[1] . '</span>
                   </p>';
        $xml->writeElement('errorPhp', $errmsg);
    }
}
$xml->endElement();

$xml->endElement();
header('Content-Type: application/xml');
header('Cache-Control: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');

$xml->output();


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

function printDebug($xml, $tabs)
{
    global $pearDB, $ret, $centreon, $nagiosCFGPath;

    $DBRESULT_Servers = $pearDB->query("SELECT `nagios_bin` 
                                        FROM `nagios_server` 
                                        WHERE `localhost` = '1' 
                                        ORDER BY ns_activate DESC LIMIT 1");
    $nagios_bin = $DBRESULT_Servers->fetchRow();
    $DBRESULT_Servers->free();
    $msg_debug = array();

    $tab_server = array();
    foreach ($tabs as $tab) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || in_array($tab['id'], $ret["host"]))) {
            $tab_server[$tab["id"]] = array(
                "id" => $tab["id"],
                "name" => $tab["name"],
                "localhost" => $tab["localhost"]
            );
        }
    }

    foreach ($tab_server as $host) {
        $stdout = shell_exec(
            $nagios_bin["nagios_bin"] . " -v " . $nagiosCFGPath . $host["id"] . "/centengine.DEBUG 2>&1"
        );
        $stdout = htmlspecialchars($stdout, ENT_QUOTES, "UTF-8");
        $msg_debug[$host['id']] = str_replace("\n", "<br />", $stdout);
        $msg_debug[$host['id']] = str_replace(
            "Warning:",
            "<font color='orange'>Warning</font>",
            $msg_debug[$host['id']]
        );
        $msg_debug[$host['id']] = str_replace(
            "warning: ",
            "<font color='orange'>Warning</font> ",
            $msg_debug[$host['id']]
        );
        $msg_debug[$host['id']] = str_replace("Error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace("error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace("reading", "Reading", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace("running", "Running", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace(
            "Total Warnings: 0",
            "<font color='green'>Total Warnings: 0</font>",
            $msg_debug[$host['id']]
        );
        $msg_debug[$host['id']] = str_replace(
            "Total Errors:   0",
            "<font color='green'>Total Errors: 0</font>",
            $msg_debug[$host['id']]
        );
        $msg_debug[$host['id']] = str_replace("<br />License:", " - License:", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = preg_replace('/\[[0-9]+?\] /', '', $msg_debug[$host['id']]);

        $lines = preg_split("/\<br\ \/\>/", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = "";
        $i = 0;
        foreach ($lines as $line) {
            if (strncmp($line, "Processing object config file", strlen("Processing object config file"))
                && strncmp($line, "Website: http://www.nagios.org", strlen("Website: http://www.nagios.org"))
            ) {
                $msg_debug[$host['id']] .= $line . "<br>";
            }
            $i++;
        }
    }

    $xml->startElement("debug");
    $str = "";
    $returnCode = 0;
    foreach ($msg_debug as $pollerId => $message) {
        $show = "none";
        $toggler = "<label id='togglerp_" . $pollerId . "'>[ + ]</label><label id='togglerm_" . $pollerId .
            "' style='display: none'>[ - ]</label>";
        $pollerNameColor = "green";
        if (preg_match_all("/Total (Errors|Warnings)\:[ ]+([0-9]+)/", $message, $globalMatches, PREG_SET_ORDER)) {
            foreach ($globalMatches as $matches) {
                if ($matches[2] != "0") {
                    $show = "block";
                    $toggler = "<label id='togglerp_" . $pollerId .
                        "' style='display: none'>[ + ]</label><label id='togglerm_" . $pollerId . "'>[ - ]</label>";
                    if ($matches[1] == "Errors") {
                        $pollerNameColor = "red";
                        $returnCode = 1;
                    } elseif ($matches[1] == "Warnings") {
                        $pollerNameColor = "orange";
                    }
                }
            }
        } else {
            $show = "block";
            $pollerNameColor = "red";
            $toggler = "<label id='togglerp_" . $pollerId .
                "' style='display: none'>[ + ]</label><label id='togglerm_" . $pollerId . "'>[ - ]</label>";
            $returnCode = 1;
        }
        $str .= "<a href='#' onClick=\"toggleDebug('" . $pollerId . "'); return false;\"/>";
        $str .= $toggler . "</a> ";
        $str .= "<b><font color='$pollerNameColor'>" . $tab_server[$pollerId]['name'] . "</font></b><br/>";
        $str .= "<div style='display: $show;' id='debug_" . $pollerId . "'>" . htmlentities($message) . "</div><br/>";
    }
    $xml->text($str);
    $xml->endElement();
    return $returnCode;
}
