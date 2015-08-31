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

if (!isset($_POST['poller']) || !isset($_POST['sid'])) {
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

    chdir($centreon_path . "www");
    $nagiosCFGPath = "$centreon_path/filesGeneration/nagiosCFG/";
    $centreonBrokerPath = "$centreon_path/filesGeneration/broker/";
    require_once $centreon_path . "www/include/configuration/configGenerate/DB-Func.php";
    require_once $centreon_path . "www/class/centreonDB.class.php";
    require_once $centreon_path . "www/class/centreonSession.class.php";
    require_once $centreon_path . "www/class/centreon.class.php";
    require_once $centreon_path . "www/class/centreonXML.class.php";
    require_once $centreon_path . "www/class/centreonACL.class.php";
    require_once $centreon_path . "www/class/centreonUser.class.php";
    require_once $centreon_path . "www/class/centreonConfigCentreonBroker.php";

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

    /*
     * Copying image in logos directory
     */
    if (isset($oreon->optGen["nagios_path_img"]) && $oreon->optGen["nagios_path_img"]) {
        $DBRESULT_imgs = $pearDB->query("SELECT `dir_alias`, `img_path` FROM `view_img`, `view_img_dir`, `view_img_dir_relation` WHERE dir_dir_parent_id = dir_id AND img_img_id = img_id");
        while ($images = $DBRESULT_imgs->fetchrow()){
            if (!is_dir($oreon->optGen["nagios_path_img"]."/".$images["dir_alias"])) {
                $mkdirResult = @mkdir($oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]);
            }
            if (file_exists($centreon_path."www/img/media/".$images["dir_alias"]."/".$images["img_path"]))  {
                $copyResult = @copy($centreon_path."www/img/media/".$images["dir_alias"]."/".$images["img_path"], $oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]."/".$images["img_path"]);
            }
        }
    }

    /*
     * Copy correlation file
     */
    $brokerObj = new CentreonConfigCentreonBroker($pearDB);
    $correlationPath = $brokerObj->getCorrelationFile();
    $localId = getLocalhostId();
    if (false !== $correlationPath && false !== $localId) {
        $tmpFilename = $centreonBrokerPath . '/' . $localId . '/correlation_*.xml';
	/* Purge file */
	$listRemovesFiles = glob(dirname($correlationPath) . '/correlation_*.xml');
	foreach ($listRemovesFiles as $file) {
	    @unlink($file);
	}
	/* Copy file */
	$listFiles = glob($tmpFilename);
    $listFiles[] = $correlationPath;
	foreach ($listFiles as $file) {
            @copy($file, dirname($correlationPath));
	}
    }


    $tab_server = array();
    $tabs = $oreon->user->access->getPollerAclConf(array('fields'     => array('name', 'id', 'localhost'),
                                                         'order'      => array('name'),
                                                         'conditions' => array('ns_activate' => '1'),
                                                         'keys'       => array('id')));
    foreach ($tabs as $tab) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
            $tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
        }
    }

    foreach ($tab_server as $host) {
        if (isset($poller) && ($poller == 0 || $poller == $host['id'])) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                /*
                 * Check if monitoring engine's configuration directory existss
                 */
                if (!is_dir($oreon->Nagioscfg["cfg_dir"])) {
                    throw new Exception(sprintf(_("Could not find configuration directory '%s' for monitoring engine '%s'. Please check it's path or create it"), $oreon->Nagioscfg["cfg_dir"], $host['name']));
                }
                /*
                 * Copy monitoring engine's configuration files
                 */
                foreach (glob($nagiosCFGPath . $host["id"] . "/*.cfg") as $filename) {
                    $succeded = @copy($filename, rtrim($oreon->Nagioscfg["cfg_dir"], "/").'/'.basename($filename));
                    if (!$succeded) {
                        throw new Exception(sprintf(_("Could not write to file '%s' for monitoring engine '%s'. Please add writing permissions for the webserver's user"), basename($filename), $host['name']));
                    } else {
                        @chmod(rtrim($oreon->Nagioscfg["cfg_dir"], "/").'/'.basename($filename), 0664);
                    }
                }
                /*
                 * Centreon Broker configuration
                 */
                $listBrokerFile = glob($centreonBrokerPath . $host['id'] . "/*.xml");
                if (count($listBrokerFile) > 0) {
                    $centreonBrokerDirCfg = getCentreonBrokerDirCfg($host['id']);
                    if (!is_null($centreonBrokerDirCfg)) {
                        if (!is_dir($centreonBrokerDirCfg)) {
                            if (!mkdir($centreonBrokerDirCfg, 0755)) {
                                throw new Exception(sprintf(_("Centreon Broker's configuration directory '%s' does not exist and could not be created for monitoring engine '%s'. Please check it's path or create it"), $centreonBrokerDirCfg, $host['name']));
                            }
                        }
                        foreach ($listBrokerFile as $fileCfg) {
                            $succeded = @copy($fileCfg, rtrim($centreonBrokerDirCfg, "/") . '/' . basename($fileCfg));
                            if (!$succeded) {
                                throw new Exception(sprintf(_("Could not write to Centreon Broker's configuration file '%s' for monitoring engine '%s'. Please add writing permissions for the webserver's user"), basename($fileCfg), $host['name']));
                            } else {
                                @chmod(rtrim($centreonBrokerDirCfg, "/") . '/' . basename($fileCfg), 0664);
                            }
                        }
                    }
                }
            } else {
                passthru("echo 'SENDCFGFILE:".$host['id']."' >> $centcore_pipe", $return);
                if ($return) {
                    throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                }
                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if (count(glob($centreonBrokerPath . $host['id'] . "/*.xml")) > 0) {
                    passthru("echo 'SENDCBCFG:".$host['id']."' >> $centcore_pipe", $return);
                    if ($return) {
                        throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                    }
                }
                $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>All configuration will be send to ".$host['name']." by centcore in several minutes.");
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
