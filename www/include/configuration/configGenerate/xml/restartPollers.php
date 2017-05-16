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

require_once _CENTREON_PATH_ . '/www/class/centreonSession.class.php';
require_once _CENTREON_PATH_ . "www/include/configuration/configGenerate/DB-Func.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonXML.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonBroker.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonUser.class.php";

$pearDB = new CentreonDB();

/* Check Session */
CentreonSession::start(1);
if (!CentreonSession::checkSession(session_id(), $pearDB)) {
    print "Bad Session";
    exit();
}

define('STATUS_OK', 0);
define('STATUS_NOK', 1);

if (!isset($_POST['poller']) || !isset($_POST['mode'])) {
    exit();
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
    $pollers = explode(',', $_POST['poller']);

    $ret = array();
    $ret['host'] = $pollers;
    $ret['restart_mode'] = $_POST['mode'];

    chdir(_CENTREON_PATH_ . "www");
    $nagiosCFGPath = _CENTREON_PATH_ . "/filesGeneration/engine/";
    $centreonBrokerPath = _CENTREON_PATH_ . "/filesGeneration/broker/";

    $centreon = $_SESSION['centreon'];

    /*  Set new error handler */
    set_error_handler('log_error');

    if (defined('_CENTREON_VARLIB_')) {
        $centcore_pipe = _CENTREON_VARLIB_ . "/centcore.cmd";
    } else {
        $centcore_pipe = "/var/lib/centreon/centcore.cmd";
    }

    $xml = new CentreonXML();

    $stdout = "";
    if (!isset($msg_restart)) {
        $msg_restart = array();
    }

    $tabs = $centreon->user->access->getPollerAclConf(array(
        'fields' => array('name', 'id', 'localhost', 'init_script'),
        'order' => array('name'),
        'conditions' => array('ns_activate' => '1'),
        'keys' => array('id')
    ));
    foreach ($tabs as $tab) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || in_array($tab['id'], $ret["host"]))) {
            $poller[$tab["id"]] = array(
                "id" => $tab["id"],
                "name" => $tab["name"],
                "localhost" => $tab["localhost"],
                'init_script' => $tab['init_script']
            );
        }
    }

    /*
     * Restart broker
     */
    $brk = new CentreonBroker($pearDB);
    $brk->reload();

    foreach ($poller as $host) {
        if ($ret["restart_mode"] == 1) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                $msg_restart[$host["id"]] = shell_exec("sudo service " . $host['init_script'] . " reload");
            } else {
                if ($fh = @fopen($centcore_pipe, 'a+')) {
                    fwrite($fh, "RELOAD:" . $host["id"] . "\n");
                    fclose($fh);
                } else {
                    throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                }

                // Manage Error Message
                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if ($return != 0) {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A reload signal has been sent to "
                        . $host["name"] . "\n");
                } else {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>Cannot send signal to "
                        . $host["name"] . ". Check $centcore_pipe properties.\n");
                }
            }
        } elseif ($ret["restart_mode"] == 2) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                $msg_restart[$host["id"]] = shell_exec("sudo service " . $host['init_script'] . " restart");
            } else {
                if ($fh = @fopen($centcore_pipe, 'a+')) {
                    fwrite($fh, "RESTART:" . $host["id"] . "\n");
                    fclose($fh);
                } else {
                    throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                }

                // Manage error Message
                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if ($return != 0) {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A restart signal has been sent to "
                        . $host["name"] . "\n");
                } else {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>Cannot send signal to "
                        . $host["name"] . ". Check $centcore_pipe properties.\n");
                }
            }
        }
        $DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '"
            . time() . "' WHERE `id` = '" . $host["id"] . "'");
    }

    foreach ($msg_restart as $key => $str) {
        $msg_restart[$key] = str_replace("\n", "<br>", $str);
    }

    /* Find restart / reload action from modules */
    foreach ($centreon->modules as $key => $value) {
        $addModule = true;
        if (function_exists('zend_loader_enabled') && (zend_loader_file_encoded() == true)) {
            $module_license_validity = zend_loader_install_license(
                _CENTREON_PATH_ . "www/modules/" . $key . "license/merethis_lic.zl",
                true
            );
            if ($module_license_validity == false) {
                $addModule = false;
            }
        }

        if ($addModule) {
            if ($value["restart"]
                && $files = glob(_CENTREON_PATH_ . "www/modules/" . $key . "/restart_pollers/*.php")
            ) {
                foreach ($files as $filename) {
                    include $filename;
                }
            }
        }
    }

    $xml->startElement("response");
    $xml->writeElement("status", "<b><font color='green'>OK</font></b>");
    $xml->writeElement("statuscode", STATUS_OK);
} catch (Exception $e) {
    $xml->startElement("response");
    $xml->writeElement("status", "<b><font color='red'>NOK</font></b>");
    $xml->writeElement("statuscode", STATUS_NOK);
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

// Headers
header('Content-Type: application/xml');
header('Cache-Control: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');

// Send Data
$xml->output();