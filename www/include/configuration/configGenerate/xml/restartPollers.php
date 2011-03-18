<?php

if (!isset($_POST['poller']) || !isset($_POST['mode']) || !isset($_POST['sid'])) {
    exit;
}

try {
    $poller = $_POST['poller'];

    $ret = array();
    $ret['host'] = $poller;
    $ret['restart_mode'] = $_POST['mode'];

    require_once "/etc/centreon/centreon.conf.php";
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

    $centcore_pipe = "/var/lib/centreon/centcore.cmd";
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

    foreach ($tab_server as $host) {
        if ($ret["restart_mode"] == 1) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                $msg_restart[$host["id"]] = shell_exec("sudo " . $nagios_init_script . " reload");
            } else {
                system("echo 'RELOAD:".$host["id"]."' >> $centcore_pipe", $return);
                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if ($return != FALSE) {
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
                if ($return != FALSE) {
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
        $DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$host["id"]."' LIMIT 1");
    }

    foreach ($msg_restart as $key => $str) {
        $msg_restart[$key] = str_replace("\n", "<br>", $str);
    }
    $xml->startElement("response");
    $xml->writeElement("status", "<b><font color='green'>OK</font></b>");
    $xml->endElement();
} catch (Exception $e) {
    $xml->startElement("response");
    $xml->writeElement("status", "<b><font color='red'>NOK</font></b>");
    $xml->writeElement("error", $e->getMessage());
    $xml->endElement();
}
header('Content-Type: application/xml');
header('Cache-Control: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
?>