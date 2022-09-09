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

use App\Kernel;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Core\Domain\Engine\Model\EngineCommandGenerator;

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once realpath(__DIR__ . "/../../../../../config/bootstrap.php");
require_once _CENTREON_PATH_ . '/www/class/centreonSession.class.php';
require_once _CENTREON_PATH_ . "www/include/configuration/configGenerate/DB-Func.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonXML.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonBroker.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonUser.class.php";

if (!defined('STATUS_OK')) {
    define('STATUS_OK', 0);
}
if (!defined('STATUS_NOK')) {
    define('STATUS_NOK', 1);
}

$pearDB = new CentreonDB();
$xml = new CentreonXML();

$okMsg = "<b><font color='green'>OK</font></b>";
$nokMsg = "<b><font color='red'>NOK</font></b>";

$kernel = new Kernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();
if ($container == null) {
    throw new Exception(_('Unable to load the Symfony container'));
}
if (isset($_SERVER['HTTP_X_AUTH_TOKEN'])) {
    $contactService = $container->get(ContactServiceInterface::class);
    $contact = $contactService->findByAuthenticationToken($_SERVER['HTTP_X_AUTH_TOKEN']);

    if ($contact === null) {
        $xml->startElement("response");
        $xml->writeElement("status", $nokMsg);
        $xml->writeElement("statuscode", STATUS_NOK);
        $xml->writeElement("error", 'Contact not found');
        $xml->endElement();

        if (!headers_sent()) {
            header('Content-Type: application/xml');
            header('Cache-Control: no-cache');
            header('Expires: 0');
            header('Cache-Control: no-cache, must-revalidate');
        }

        $xml->output();
        exit();
    }
    $centreon = new Centreon([
        'contact_id' => $contact->getId(),
        'contact_name' => $contact->getName(),
        'contact_alias' => $contact->getAlias(),
        'contact_email' => $contact->getEmail(),
        'contact_admin' => $contact->isAdmin(),
        'contact_lang' => null,
        'contact_passwd' => null,
        'contact_autologin_key' => null,
        'contact_location' => null,
        'reach_api' => $contact->hasAccessToApiConfiguration(),
        'reach_api_rt' => $contact->hasAccessToApiRealTime(),
        'show_deprecated_pages' => false
    ]);
} else {
    /* Check Session */
    CentreonSession::start(1);
    if (!CentreonSession::checkSession(session_id(), $pearDB)) {
        print "Bad Session";
        exit();
    }

    $centreon = $_SESSION['centreon'];
}


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
$log_error = function ($errno, $errstr, $errfile, $errline) {
    global $generatePhpErrors;
    if (!(error_reporting() && $errno)) {
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
};

try {
    $pollers = explode(',', $_POST['poller']);

    $ret = array();
    $ret['host'] = $pollers;
    $ret['restart_mode'] = $_POST['mode'];

    chdir(_CENTREON_PATH_ . "www");
    $nagiosCFGPath = _CENTREON_CACHEDIR_ . "/config/engine/";
    $centreonBrokerPath = _CENTREON_CACHEDIR_ . "/config/broker/";

    /*  Set new error handler */
    set_error_handler($log_error);

    if (defined('_CENTREON_VARLIB_')) {
        $centcore_pipe = _CENTREON_VARLIB_ . "/centcore.cmd";
    } else {
        $centcore_pipe = "/var/lib/centreon/centcore.cmd";
    }

    $stdout = "";
    if (!isset($msg_restart)) {
        $msg_restart = array();
    }

    $tabs = $centreon->user->access->getPollerAclConf([
        'fields' => [
            'name',
            'id',
            'engine_restart_command',
            'engine_reload_command',
            'broker_reload_command'
        ],
        'order' => array('name'),
        'conditions' => array('ns_activate' => '1'),
        'keys' => array('id')
    ]);
    foreach ($tabs as $tab) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || in_array($tab['id'], $ret["host"]))) {
            $poller[$tab["id"]] = array(
                "id" => $tab["id"],
                "name" => $tab["name"],
                'engine_restart_command' => $tab['engine_restart_command'],
                'engine_reload_command' => $tab['engine_reload_command'],
                'broker_reload_command' => $tab['broker_reload_command']
            );
        }
    }

    /*
     * Restart broker
     */
    $brk = new CentreonBroker($pearDB);
    $brk->reload();
    /**
     * @var EngineCommandGenerator $commandGenerator
     */
    $commandGenerator = $container->get(EngineCommandGenerator::class);
    foreach ($poller as $host) {
        if ($ret["restart_mode"] == 1) {
            if ($fh = @fopen($centcore_pipe, 'a+')) {
                $reloadCommand = ($commandGenerator !== null)
                    ? $commandGenerator->getEngineCommand('RELOAD')
                    : 'RELOAD';
                fwrite($fh, $reloadCommand . ':' . $host["id"] . "\n");
                fclose($fh);
            } else {
                throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
            }

            // Manage Error Message
            if (!isset($msg_restart[$host["id"]])) {
                $msg_restart[$host["id"]] = "";
            }
            $msg_restart[$host["id"]] .= _(
                "<br><b>Centreon : </b>A reload signal has been sent to "
                . $host["name"] . "\n"
            );
        } elseif ($ret["restart_mode"] == 2) {
            if ($fh = @fopen($centcore_pipe, 'a+')) {
                $restartCommand = ($commandGenerator !== null)
                    ? $commandGenerator->getEngineCommand('RESTART')
                    : 'RESTART';
                fwrite($fh, $restartCommand . ':' . $host["id"] . "\n");
                fclose($fh);
            } else {
                throw new Exception(_("Could not write into centcore.cmd. Please check file permissions."));
            }

            // Manage error Message
            if (!isset($msg_restart[$host["id"]])) {
                $msg_restart[$host["id"]] = "";
            }
            $msg_restart[$host["id"]] .= _(
                "<br><b>Centreon : </b>A restart signal has been sent to " . $host["name"] . "\n"
            );
        }
        $DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '"
            . time() . "', `updated` = '0' WHERE `id` = '" . $host["id"] . "'");
    }

    foreach ($msg_restart as $key => $str) {
        $msg_restart[$key] = str_replace("\n", "<br>", $str);
    }

    /* Find restart / reload action from modules */
    foreach ($centreon->modules as $key => $value) {
        if (
            $value["restart"]
            && $files = glob(_CENTREON_PATH_ . "www/modules/" . $key . "/restart_pollers/*.php")
        ) {
            foreach ($files as $filename) {
                include $filename;
            }
        }
    }

    $xml->startElement("response");
    $xml->writeElement("status", $okMsg);
    $xml->writeElement("statuscode", STATUS_OK);
} catch (Exception $e) {
    $xml->startElement("response");
    $xml->writeElement("status", $nokMsg);
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
if (!headers_sent()) {
    header('Content-Type: application/xml');
    header('Cache-Control: no-cache');
    header('Expires: 0');
    header('Cache-Control: no-cache, must-revalidate');
}

// Send Data
$xml->output();
