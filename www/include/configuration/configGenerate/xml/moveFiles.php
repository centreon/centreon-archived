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

use Centreon\Domain\Entity\Task;

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");

require_once realpath(__DIR__ . "/../../../../../bootstrap.php");

require_once _CENTREON_PATH_ . '/www/class/centreonSession.class.php';
require_once _CENTREON_PATH_ . "www/include/configuration/configGenerate/DB-Func.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonXML.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonUser.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonConfigCentreonBroker.php";

$pearDB = new CentreonDB();

/* Check Session */
CentreonSession::start(1);
if (!CentreonSession::checkSession(session_id(), $pearDB)) {
    print "Bad Session";
    exit();
}

define('STATUS_OK', 0);
define('STATUS_NOK', 1);

if (!isset($_POST['poller'])) {
    exit;
}

/**
 * List of error from php
 */
global $generatePhpErrors;
global $dependencyInjector;

$generatePhpErrors = [];
$pollers = explode(',', $_POST['poller']);

// Add task to export files if there is a remote
$pollerParams = [];
foreach ($pollers as $pollerId) {
    $pollerParams[':poller_' . $pollerId] = $pollerId;
}
$statementRemotes = $pearDB->prepare('
    SELECT ns.id, ns.ns_ip_address AS ip,
    rs.centreon_path, rs.http_method, rs.http_port, rs.no_check_certificate, rs.no_proxy
    FROM nagios_server AS ns
    JOIN remote_servers AS rs ON rs.ip = ns.ns_ip_address
    WHERE ns.id IN (' . implode(',', array_keys($pollerParams)) . ')
    UNION
    SELECT ns1.id, ns1.ns_ip_address AS ip,
    rs.centreon_path, rs.http_method, rs.http_port, rs.no_check_certificate, rs.no_proxy
    FROM nagios_server AS ns1
    JOIN remote_servers AS rs ON rs.ip = ns1.ns_ip_address
    JOIN nagios_server AS ns2 ON ns1.id = ns2.remote_id
    WHERE ns2.id IN (' . implode(',', array_keys($pollerParams)) . ')
    UNION
    SELECT ns1.id, ns1.ns_ip_address AS ip,
    rs.centreon_path, rs.http_method, rs.http_port, rs.no_check_certificate, rs.no_proxy
    FROM nagios_server AS ns1
    JOIN remote_servers AS rs ON rs.ip = ns1.ns_ip_address
    JOIN rs_poller_relation AS rspr ON rspr.remote_server_id = ns1.id
    WHERE rspr.poller_server_id IN (' . implode(',', array_keys($pollerParams)) . ')
');
foreach ($pollerParams as $key => $value) {
    $statementRemotes->bindValue($key, $value, \PDO::PARAM_INT);
}
$statementRemotes->execute();
$remotesResults = $statementRemotes->fetchAll(PDO::FETCH_ASSOC);

if (!empty($remotesResults)) {
    foreach ($remotesResults as $remote) {
        $linkedStatement = $pearDB->prepare('
            SELECT id
            FROM nagios_server
            WHERE remote_id = :remote_id
            UNION
            SELECT poller_server_id AS id
            FROM rs_poller_relation
            WHERE remote_server_id = :remote_id
        ');
        $linkedStatement->bindValue(':remote_id', $remote['id'], \PDO::PARAM_INT);
        $linkedStatement->execute();
        $linkedResults = $linkedStatement->fetchAll(PDO::FETCH_ASSOC);

        $exportParams = [
            'server' => $remote['id'],
            'remote_ip' => $remote['ip'],
            'centreon_path' => $remote['centreon_path'],
            'http_method' => $remote['http_method'],
            'http_port' => $remote['http_port'] ?: null,
            'no_check_certificate' => $remote['no_check_certificate'],
            'no_proxy' => $remote['no_proxy'],
            'pollers' => []
        ];

        if (!empty($linkedResults)) {
            $exportParams['pollers'] = array_column($linkedResults, 'id');
        } else {
            $exportParams['pollers'] = [$remote['id']];
        }

        $dependencyInjector['centreon.taskservice']->addTask(Task::TYPE_EXPORT, ['params' => $exportParams]);
    }
}

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
    $ret = array();
    $ret['host'] = $pollers;

    chdir(_CENTREON_PATH_ . "www");
    $nagiosCFGPath = _CENTREON_CACHEDIR_ . "/config/engine/";
    $centreonBrokerPath = _CENTREON_CACHEDIR_ . "/config/broker/";

    $centreon = $_SESSION['centreon'];
    $centreon = $centreon;

    /*  Set new error handler */
    set_error_handler('log_error');

    # Centcore pipe path
    $centcore_pipe = _CENTREON_VARLIB_ . "/centcore.cmd";

    $xml = new CentreonXML();

    /*
     * Copying image in logos directory
     */
    if (isset($centreon->optGen["nagios_path_img"]) && $centreon->optGen["nagios_path_img"]) {
        $DBRESULT_imgs = $pearDB->query(
            "SELECT `dir_alias`, `img_path` " .
            "FROM `view_img`, `view_img_dir`, `view_img_dir_relation` " .
            "WHERE dir_dir_parent_id = dir_id AND img_img_id = img_id"
        );
        while ($images = $DBRESULT_imgs->fetchrow()) {
            if (!is_dir($centreon->optGen["nagios_path_img"] . "/" . $images["dir_alias"])) {
                $mkdirResult = @mkdir($centreon->optGen["nagios_path_img"] . "/" . $images["dir_alias"]);
            }
            if (file_exists(_CENTREON_PATH_ . "www/img/media/" . $images["dir_alias"] . "/" . $images["img_path"])) {
                $copyResult = @copy(
                    _CENTREON_PATH_ . "www/img/media/" . $images["dir_alias"] . "/" . $images["img_path"],
                    $centreon->optGen["nagios_path_img"] . "/" . $images["dir_alias"] . "/" . $images["img_path"]
                );
            }
        }
    }

    $tab_server = array();
    $tabs = $centreon->user->access->getPollerAclConf(array(
        'fields' => array('name', 'id', 'localhost'),
        'order' => array('name'),
        'conditions' => array('ns_activate' => '1'),
        'keys' => array('id')
    ));

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
        if (isset($pollers) && ($pollers == 0 || in_array($host['id'], $pollers))) {
            $listBrokerFile = glob($centreonBrokerPath . $host['id'] . "/*.{xml,json,cfg,sql}", GLOB_BRACE);
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                /*
                 * Check if monitoring engine's configuration directory existss
                 */
                if (!is_dir($centreon->Nagioscfg["cfg_dir"])) {
                    throw new Exception(
                        sprintf(
                            _(
                                "Could not find configuration directory '%s' for monitoring engine '%s'.
                                 Please check it's path or create it"
                            ),
                            $centreon->Nagioscfg["cfg_dir"],
                            $host['name']
                        )
                    );
                }
                /*
                 * Copy monitoring engine's configuration files
                 */
                foreach (glob($nagiosCFGPath . $host["id"] . "/*.cfg") as $filename) {
                    $succeded = @copy(
                        $filename,
                        rtrim($centreon->Nagioscfg["cfg_dir"], "/") . '/' . basename($filename)
                    );
                    if (!$succeded) {
                        throw new Exception(
                            sprintf(
                                _(
                                    "Could not write to file '%s' for monitoring engine '%s'. Please add writing
                                     permissions for the webserver's user"
                                ),
                                basename($filename),
                                $host['name']
                            )
                        );
                    } else {
                        @chmod(rtrim($centreon->Nagioscfg["cfg_dir"], "/") . '/' . basename($filename), 0664);
                    }
                }
                /*
                 * Centreon Broker configuration
                 */
                if (count($listBrokerFile) > 0) {
                    $centreonBrokerDirCfg = getCentreonBrokerDirCfg($host['id']);
                    if (!is_null($centreonBrokerDirCfg)) {
                        if (!is_dir($centreonBrokerDirCfg)) {
                            if (!mkdir($centreonBrokerDirCfg, 0755)) {
                                throw new Exception(
                                    sprintf(
                                        _(
                                            "Centreon Broker's configuration directory '%s' does not exist and could not
                                             be created for monitoring engine '%s'. Please check it's path or create it"
                                        ),
                                        $centreonBrokerDirCfg,
                                        $host['name']
                                    )
                                );
                            }
                        }
                        foreach ($listBrokerFile as $fileCfg) {
                            $succeded = @copy($fileCfg, rtrim($centreonBrokerDirCfg, "/") . '/' . basename($fileCfg));
                            if (!$succeded) {
                                throw new Exception(
                                    sprintf(
                                        _(
                                            "Could not write to Centreon Broker's configuration file '%s' for monitoring
                                             engine '%s'. Please add writing permissions for the webserver's user"
                                        ),
                                        basename($fileCfg),
                                        $host['name']
                                    )
                                );
                            } else {
                                @chmod(rtrim($centreonBrokerDirCfg, "/") . '/' . basename($fileCfg), 0664);
                            }
                        }
                    }
                }
            } else {
                passthru("echo 'SENDCFGFILE:" . $host['id'] . "' >> $centcore_pipe", $return);
                if ($return) {
                    throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                }
                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if (count($listBrokerFile) > 0) {
                    passthru("echo 'SENDCBCFG:" . $host['id'] . "' >> $centcore_pipe", $return);
                    if ($return) {
                        throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
                    }
                }
                $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>All configuration will be send to " .
                    $host['name'] . " by centcore in several minutes.");
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
