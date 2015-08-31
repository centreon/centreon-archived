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

ini_set("display_errors", "Off");
require_once "@CENTREON_ETC@/centreon.conf.php";
define('STATUS_OK', 0);
define('STATUS_NOK', 1);

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
    require_once $centreon_path . "www/class/centreonBroker.class.php";
    require_once $centreon_path . "www/class/centreonACL.class.php";
    require_once $centreon_path . "www/class/centreonUser.class.php";

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
    $tabs = $oreon->user->access->getPollerAclConf(array('fields'     => array('name', 'id', 'localhost'),
                                                         'order'      => array('name'),
                                                         'conditions' => array('ns_activate' => '1'),
                                                         'keys'       => array('id')));
    foreach ($tabs as $tab) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
            $tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
        }
    }

    /*
     * Restart broker
     */
    $brk = new CentreonBroker($pearDB);
    if ($brk->getBroker() == 'broker') {
        $brk->reload();
    }

    foreach ($tab_server as $host) {
    	if ($ret["restart_mode"] == 1) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                $msg_restart[$host["id"]] = shell_exec("sudo " . $nagios_init_script . " reload");
            } else {
                system("echo 'RELOAD:".$host["id"]."' >> $centcore_pipe", $return);
                if ($return) {
                    throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                }
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
                if ($return) {
                    throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                }

                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if ($return != 0) {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A restart signal has been sent to ".$host["name"]."\n");
                } else {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>Cannot send signal to ".$host["name"].". Check $centcore_pipe properties.\n");
                }
            }
        } else if ($ret["restart_mode"] == 4) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                $msg_restart[$host["id"]] = shell_exec("sudo " . $nagios_init_script . " force-reload");
            } else {
                system("echo \"FORCERELOAD:".$host["id"]."\" >> $centcore_pipe", $return);
                if ($return) {
                    throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                }

                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if ($return != 0) {
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A force-reload signal has been sent to ".$host["name"]."\n");
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
    
    /* Find restart / reload action from modules */
    foreach ($oreon->modules as $key => $value) {
        $addModule = true;
        if (function_exists('zend_loader_enabled') && (zend_loader_file_encoded() == true)) {
            $module_license_validity = zend_loader_install_license ($centreon_path . "www/modules/".$key."license/merethis_lic.zl", true);
            if ($module_license_validity == false)
                $addModule = false;
        }
        
        if ($addModule) {
            if ($value["restart"] && $files = glob($centreon_path . "www/modules/".$key."/restart_pollers/*.php")) {
                foreach ($files as $filename)
                    include $filename;
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

header('Content-Type: application/xml');
header('Cache-Control: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
?>
