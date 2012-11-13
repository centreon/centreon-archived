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

if (!isset($_POST['poller']) || !isset($_POST['comment']) || !isset($_POST['debug']) || !isset($_POST['sid'])) {
    exit;
}

function printDebug($xml)
{
    global $pearDB, $ret, $nagiosCFGPath;

    $DBRESULT_Servers = $pearDB->query("SELECT `nagios_bin` FROM `nagios_server` WHERE `localhost` = '1' ORDER BY ns_activate DESC LIMIT 1");
    $nagios_bin = $DBRESULT_Servers->fetchRow();
    $DBRESULT_Servers->free();
    $msg_debug = array();
    $tab_server = array();
    $DBRESULT_Servers = $pearDB->query("SELECT `name`, `id`, `localhost` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name` ASC");
    while ($tab = $DBRESULT_Servers->fetchRow()) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
            $tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
        }
    }
    foreach ($tab_server as $host) {
        $stdout = shell_exec("sudo ".$nagios_bin["nagios_bin"] . " -v ".$nagiosCFGPath.$host["id"]."/nagiosCFG.DEBUG 2>&1");
        $stdout = htmlentities($stdout, ENT_QUOTES, "UTF-8");
        $msg_debug[$host['id']] = str_replace ("\n", "<br />", $stdout);
        $msg_debug[$host['id']] = str_replace ("Warning:", "<font color='orange'>Warning</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace ("warning: ", "<font color='orange'>Warning</font> ", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace ("Error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace ("error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace ("reading", "Reading", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace ("running", "Running", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace ("Total Warnings: 0", "<font color='green'>Total Warnings: 0</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace ("Total Errors:   0", "<font color='green'>Total Errors: 0</font>", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = str_replace ("<br />License:", " - License:", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = preg_replace('/\[[0-9]+?\] /', '', $msg_debug[$host['id']]);

        $lines = preg_split("/\<br\ \/\>/", $msg_debug[$host['id']]);
        $msg_debug[$host['id']] = "";
        $i = 0;
        foreach ($lines as $line) {
            if (strncmp($line, "Processing object config file", strlen("Processing object config file"))
                && strncmp($line, "Website: http://www.nagios.org", strlen("Website: http://www.nagios.org"))) 
            $msg_debug[$host['id']] .= $line . "<br>";
            $i++;
        }

    }

    $xml->startElement("debug");
    $str = "";
    $returnCode = 0;
    foreach ($msg_debug as $pollerId => $message) {
        $show = "none";
        $toggler = "<label id='togglerp_".$pollerId."'>[ + ]</label><label id='togglerm_".$pollerId."' style='display: none'>[ - ]</label>";
        $pollerNameColor = "green";
        if (preg_match_all("/Total (Errors|Warnings)\:[ ]+([0-9]+)/", $message, $globalMatches, PREG_SET_ORDER)) {
            foreach ($globalMatches as $matches) {
                if ($matches[2] != "0") {
                    $show = "block";
                    $toggler = "<label id='togglerp_".$pollerId."' style='display: none'>[ + ]</label><label id='togglerm_".$pollerId."'>[ - ]</label>";
                    if ($matches[1] == "Errors") {
                        $pollerNameColor = "red";
                        $returnCode = 1;
                    } elseif($matches[1] == "Warnings") {
                        $pollerNameColor = "orange";
                    }
                }
            }
        } else {
            $show = "block";
            $pollerNameColor = "red";
            $toggler = "<label id='togglerp_".$pollerId."' style='display: none'>[ + ]</label><label id='togglerm_".$pollerId."'>[ - ]</label>";
            $returnCode = 1;
        }
        $str .= "<a href='#' onClick=\"toggleDebug('".$pollerId."'); return false;\"/>";
        $str .= $toggler . "</a> ";
        $str .= "<b><font color='$pollerNameColor'>".$tab_server[$pollerId]['name'] . "</font></b><br/>";
        $str .= "<div style='display: $show;' id='debug_".$pollerId."'>".htmlentities($message) . "</div><br/>";
    }
    $xml->text($str);
    $xml->endElement();
    return $returnCode;
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

$path = $centreon_path . "www/include/configuration/configGenerate/";
require_once $path."DB-Func.php";
$nagiosCFGPath = $centreon_path . "filesGeneration/nagiosCFG/";
$centreonBrokerPath = $centreon_path . "filesGeneration/broker/";
$DebugPath = "filesGeneration/nagiosCFG/";

$poller = $_POST['poller'];
$comment = ($_POST['comment'] == "true") ? 1 : 0;
$debug = ($_POST['debug'] == "true") ? 1 : 0;

$ret = array();
$ret['host'] = $poller;
$ret['comment'] = $comment;
$ret['debug'] = $debug;

chdir($centreon_path . "www");
require_once $centreon_path . "www/include/configuration/configGenerate/DB-Func.php";
require_once $centreon_path . "www/class/centreonDB.class.php";
require_once $centreon_path . "www/class/centreonSession.class.php";
require_once $centreon_path . "www/class/centreon.class.php";
require_once $centreon_path . "www/class/centreonXML.class.php";
require_once $centreon_path . "/www/class/centreonConfigCentreonBroker.php";
require_once $centreon_path . '/www/include/configuration/configGenerate/genCentreonBrokerCorrelation.php';

session_start();
if ($_POST['sid'] != session_id()) {
    exit;
}
$oreon = $_SESSION['centreon'];
$centreon = $oreon;
$xml = new CentreonXML();
$pearDB = new CentreonDB();

/*  Set new error handler */
set_error_handler('log_error');

$okMsg = "<b><font color='green'>OK</font></b>";
$nokMsg = "<b><font color='red'>NOK</font></b>";

$xml->startElement("response");
try {
    /*
     * Check dependancies
     */
    $gbArr = manageDependencies();

    /**
     * Declare Poller ID
     */
    global $pollerID;

    /*
     * Request id and host type.
     */
    $DBRESULT_Servers = $pearDB->query("SELECT `id`, `localhost`, `monitoring_engine` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name`");
    while ($tab = $DBRESULT_Servers->fetchRow()){
        if (isset($poller) && ($tab['id'] == $poller || $poller == 0)) {
            $pollerID = $tab['id'];
            unset($DBRESULT2);
            if (isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "SHINKEN" &&
            $tab['localhost']) {
                require $path . "genShinkenBroker.php";
            }
            require $path."genCGICFG.php";
            require $path."genNagiosCFG.php";
            require $path."genNdomod.php";
            require $path."genNdo2db.php";
            require $path."genCentreonBroker.php";
            require $path."genNagiosCFG-DEBUG.php";
            require $path."genResourceCFG.php";
            require $path."genTimeperiods.php";
            require $path."genCommands.php";
            require $path."genConnectors.php";
            require $path."genContacts.php";
            require $path."genContactTemplates.php";
            require $path."genContactGroups.php";
            require $path."genHosts.php";
            require $path."genHostTemplates.php";
            require $path."genHostGroups.php";
            require $path."genServiceTemplates.php";
            require $path."genServices.php";
            require $path."genServiceGroups.php";
            require $path."genEscalations.php";
            require $path."genDependencies.php";
            require $path."centreon_pm.php";

            if ($tab['localhost']) {
                $flag_localhost = $tab['localhost'];
                
                /*
                 * Meta Services Generation
                 */
                if ($files = glob($path . "metaService/*.php")) {
                    foreach ($files as $filename) {
                        include $filename;
                    }
                }
            }

            /*
             * Module Generation
             */
            foreach ($oreon->modules as $key => $value) {
                $addModule = true;
                if (function_exists('zend_loader_enabled') && (zend_loader_file_encoded() == true)) {
                    $module_license_validity = zend_loader_install_license ($centreon_path . "www/modules/".$key."license/merethis_lic.zl", true);
                    if ($module_license_validity == false)
                        $addModule = false;
                }
                
                if ($addModule) {
                    if ($value["gen"] && $files = glob($centreon_path . "www/modules/".$key."/generate_files/*.php")) {
                        foreach ($files as $filename)
                            include $filename;
                    }
                }
            }

            unset($generatedHG);
            unset($generatedSG);
            unset($generatedS);
        }
    }
    /*
     * Generate correlation file
     */
    $brokerObj = new CentreonConfigCentreonBroker($pearDB);
    $correlationPath = $brokerObj->getCorrelationFile();
    $localId = getLocalhostId();
    if (false !== $correlationPath && false !== $localId) {
        $tmpFilename = $centreonBrokerPath . '/' . $localId . '/' . basename($correlationPath);
        generateCentreonBrokerCorrelation($brokerObj, $tmpFilename, $pearDB);
    }


    $statusMsg = $okMsg;
    $statusCode = 0;
    if ($debug) {
        $statusCode = printDebug($xml);
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
        $errmsg = '<p><span style="color: red;">Error</span><span style="margin-left: 5px;">' . $error[1] . '</span></p>';
        $xml->writeElement('errorPhp', $errmsg);
    }/* else {
        $errmsg = '<p><span style="color: orange;">Warning</span><span style="margin-left: 5px;">' . $error[1] . '</span></p>';
    }*/
    
}
$xml->endElement();

$xml->endElement();
header('Content-Type: application/xml');
header('Cache-Control: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
?>