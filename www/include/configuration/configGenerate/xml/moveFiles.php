<?php

if (!isset($_POST['poller'])) {
    exit;
}

try {
    $poller = $_POST['poller'];

    $ret = array();
    $ret['host'] = $poller;

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

    $oreon = $_SESSION['centreon'];
    $centreon = $oreon;

    $centcore_pipe = "/var/lib/centreon/centcore.cmd";
	if ($centcore_pipe == "/centcore.cmd") {
		$centcore_pipe = "/var/lib/centreon/centcore.cmd";
	}

    $xml = new CentreonXML();
    $pearDB = new CentreonDB();

    /*
     * Copying image in logos directory
     */
    $DBRESULT_imgs = $pearDB->query("SELECT `dir_alias`, `img_path` FROM `view_img`, `view_img_dir`, `view_img_dir_relation` WHERE dir_dir_parent_id = dir_id AND img_img_id = img_id");
    while ($images = $DBRESULT_imgs->fetchrow()){
        if (!is_dir($oreon->optGen["nagios_path_img"]."/".$images["dir_alias"])) {
            mkdir($oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]);
        }
        copy($centreon_path."www/img/media/".$images["dir_alias"]."/".$images["img_path"], $oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]."/".$images["img_path"]);
    }
    $msg_copy = array();

    $tab_server = array();
    $DBRESULT_Servers = $pearDB->query("SELECT `name`, `id`, `localhost` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name` ASC");
    while ($tab = $DBRESULT_Servers->fetchRow()) {
        if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
            $tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
        }
    }

    foreach ($tab_server as $host) {
        if (isset($poller) && ($poller == 0 || $poller == $host['id'])) {
            if (isset($host['localhost']) && $host['localhost'] == 1) {
                $msg_copy[$host["id"]] = "";
                if (!is_dir($oreon->Nagioscfg["cfg_dir"])) {
                    $msg_copy[$host["id"]] .= sprintf(_("Nagios config directory %s does not exist!")."<br>", $oreon->Nagioscfg["cfg_dir"]);
                }
                if (!is_writable($oreon->Nagioscfg["cfg_dir"])) {
                    $msg_copy[$host["id"]] .= sprintf(_("Nagios config directory %s is not writable for webserver's user!")."<br>", $oreon->Nagioscfg["cfg_dir"]);
                }
                foreach (glob($nagiosCFGPath.$host["id"]."/*.cfg") as $filename) {
                    $bool = @copy($filename, $oreon->Nagioscfg["cfg_dir"].basename($filename));
                    $filename = array_pop(explode("/", $filename));
                    if (!$bool) {
                        $msg_copy[$host["id"]] .= display_copying_file($filename, " - "._("movement")." <font color='res'>KO</font>");
                    }
                }
                /*
                 * Centreon Broker
                 */
                $listBrokerFile = glob($centreonBrokerPath . $host['id'] . "/*.xml");
                if (count($listBrokerFile) > 0) {
                    $centreonBrokerDirCfg = getCentreonBrokerDirCfg($host['id']);
                    if (!is_null($centreonBrokerDirCfg)) {
                        if (!is_dir($centreonBrokerDirCfg)) {
                            $msg_copy[$host['id']] .= sprintf(_("Centreon Broker config directory %s does not exists!") . "<br>", $centreonBrokerDirCfg);
                        } elseif (!is_writable($centreonBrokerDirCfg)) {
                            $msg_copy[$host['id']] .= sprintf(_("Centreon Broker config directory %s is not writable for webserver's user!") . "<br>", $centreonBrokerDirCfg);
                        } else {
                            foreach ($listBrokerFile as $fileCfg) {
                                $bool = @copy($fileCfg, $centreonBrokerDirCfg . '/' . basename($fileCfg));
                                $filename = array_pop(explode("/", $fileCfg));
                                if (!$bool) {
                                    $msg_copy[$host["id"]] .= display_copying_file($filename, " - "._("movement")." <font color='res'>KO</font>");
                                }
                            }
                        }
                    }
                }

                if (strlen($msg_copy[$host["id"]])) {
                    $msg_copy[$host["id"]] = "<table border=0 width=300>".$msg_copy[$host["id"]]."</table>";
                } else {
                    $msg_copy[$host["id"]] .= _("<br><b>Centreon : </b>All configuration files copied with success.");
                }
            } else {
                passthru("echo 'SENDCFGFILE:".$host['id']."' >> $centcore_pipe", $return);
                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if (count(glob($centreonBrokerPath . $host['id'] . "/*.xml")) > 0) {
                    passthru("echo 'SENDCBCFG:".$host['id']."' >> $centcore_pipe", $return);
                }
                $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>All configuration will be send to ".$host['name']." by centcore in several minutes.");
            }
        }
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