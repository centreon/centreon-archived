<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

ini_set("display_errors", "On");

// Include configuration
require_once realpath(__DIR__ . "/../../../../config/centreon.config.php");

// Include Classes / Methods
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";

// Connect MySQL DB
$pearDB = new CentreonDB();
$pearDBO = new CentreonDB("centstorage");

// Security check
CentreonSession::start(1);
if (!CentreonSession::checkSession(session_id(), $pearDB)) {
    print "Bad Session";
    exit();
}

$centreon = $_SESSION["centreon"];

// Language informations init
$locale = $centreon->user->get_lang();
putenv("LANG=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain("messages", _CENTREON_PATH_ . "/www/locale/");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");


$sid = session_id();
(isset($sid)) ? $sid = $sid : $sid = "-1";
$contact_id = check_session($sid, $pearDB);

$is_admin = isUserAdmin($sid);
$lca = [];
if (isset($sid) && $sid) {
    $access = new CentreonAcl($contact_id, $is_admin);
    $lca = [
        "LcaHost" => $access->getHostsServices($pearDBO, 1),
        "LcaHostGroup" => $access->getHostGroups(),
        "LcaSG" => $access->getServiceGroups()
    ];
}
require_once _CENTREON_PATH_ . "www/include/eventLogs/Request.php";
$requestHandler = new Request();
$requestHandler->setIsAdmin($is_admin);
$requestHandler->setLca($lca);

require_once _CENTREON_PATH_ . "www/include/eventLogs/xml/QueryGenerator.php";
$queryGenerator = new QueryGenerator($pearDBO);
$queryGenerator->setIsAdmin($is_admin);
$queryGenerator->setEngine($requestHandler->getEngine());
$queryGenerator->setOpenid($requestHandler->getOpenid());
$queryGenerator->setOutput($requestHandler->getOutput());
$queryGenerator->setAccess($access);
$queryGenerator->setStart($requestHandler->getStart());
$queryGenerator->setEnd($requestHandler->getEnd());
$queryGenerator->setUp($requestHandler->getUp());
$queryGenerator->setDown($requestHandler->getDown());
$queryGenerator->setUnreachable($requestHandler->getUnreachable());
$queryGenerator->setOk($requestHandler->getOk());
$queryGenerator->setWarning($requestHandler->getWarning());
$queryGenerator->setCritical($requestHandler->getCritical());
$queryGenerator->setUnreachable($requestHandler->getUnreachable());
$queryGenerator->setNotification($requestHandler->getNotification());
$queryGenerator->setAlert($requestHandler->getAlert());
$queryGenerator->setError($requestHandler->getError());
$queryGenerator->setOh($requestHandler->getOh());
$queryGenerator->setHostMsgStatusSet($requestHandler->getHostMsgStatusSet());
$queryGenerator->setSvcMsgStatusSet($requestHandler->getSvcMsgStatusSet());
$queryGenerator->setTabHostIds($requestHandler->getTabHostIds());
$queryGenerator->setSearchHost($requestHandler->getSearchHost());
$queryGenerator->setTabSvc($requestHandler->getTabSvc());
$queryGenerator->setSearchService($requestHandler->getSearchService());
$queryGenerator->setExport($requestHandler->getExport());
$stmt = $queryGenerator->getStatement();
unset($queryGenerator);

$stmt->execute();
$logs = $stmt->fetchAll();
$stmt->closeCursor();
unset($stmt);

$HostCache = [];
$dbResult = $pearDB->query("SELECT host_name, host_address FROM host WHERE host_register = '1'");
while ($h = $dbResult->fetch()) {
    $HostCache[$h["host_name"]] = $h["host_address"];
}
$dbResult->closeCursor();

require_once _CENTREON_PATH_ . "www/include/eventLogs/export/Formatter.php";
$formatter = new Formatter();
$formatter->setHosts($HostCache);
$logHeads = $formatter->getLogHeads();
$formattedLogs = $formatter->formatLogs($logs);
unset($formatter);


require_once _CENTREON_PATH_ . "www/include/eventLogs/export/Presenter.php";

$presenter = new Presenter();
$presenter->setHeads($logHeads);
$presenter->setLogs($formattedLogs);
$presenter->render();
unset($presenter);

//foreach ($logs as $log) {
//    echo PHP_EOL;
//    print_r($log);
//}
//exit('Exit at '.__FILE__.' line: '.__LINE__);


// save of the XML flow in $flow
//$csv_flag = 1; //setting the csv_flag variable to change limit in SQL request of getODSXmlLog.php when CSV exporting
//ob_start();
//require_once _CENTREON_PATH_ . "www/include/eventLogs/xml/data.php";
//$flow = ob_get_contents();
//ob_end_clean();
//
//// Send Headers
//header("Content-Type: application/csv-tab-delimited-table");
//header("Content-disposition: filename=EventLogs.csv");
//header("Cache-Control: cache, must-revalidate");
//header("Pragma: public");
//
//// Read flow
//$xml = new SimpleXMLElement($flow);
//if ($engine == "false") {
//    echo _("Begin date") . "; "
//        . _("End date") . ";\n";
//    echo date(_('Y/m/d (H:i:s)'), intval($xml->infos->start)) . ";"
//        . date(_('Y/m/d (H:i:s)'), intval($xml->infos->end)) . "\n";
//    echo "\n";
//
//    echo _("Type") . ";"
//        . _("Notification") . ";"
//        . _("Alert") . ";"
//        . _("error") . "\n";
//    echo ";"
//        . $xml->infos->notification . ";"
//        . $xml->infos->alert . ";"
//        . $xml->infos->error . "\n";
//    echo "\n";
//
//    echo _("Host") . ";"
//        . _("Up") . ";"
//        . _("Down") . ";"
//        . _("Unreachable") . "\n";
//    echo ";"
//        . $xml->infos->up . ";"
//        . $xml->infos->down . ";"
//        . $xml->infos->unreachable . "\n";
//    echo "\n";
//
//    echo _("Service") . ";"
//        . _("Ok") . ";"
//        . _("Warning") . ";"
//        . _("Critical") . ";"
//        . _("Unknown") . "\n";
//    echo ";"
//        . $xml->infos->ok . ";"
//        . $xml->infos->warning . ";"
//        . $xml->infos->critical . ";"
//        . $xml->infos->unknown . "\n";
//    echo "\n";
//
//    echo _("Day") . ";" .
//        _("Time") . ";" .
//        _("Host") . ";" .
//        _("Address") . ";" .
//        _("Service") . ";" .
//        _("Status") . ";" .
//        _("Type") . ";" .
//        _("Retry") . ";" .
//        _("Output") . ";" .
//        _("Contact") . ";" .
//        _("Cmd") . "\n";
//    foreach ($xml->line as $line) {
//        echo date(_('Y/m/d'), (int)$line->date) . ";" .
//            date(_('H:i:s'), (int)$line->time) . ";" .
//            $line->host_name . ";" .
//            $line->address . ";" .
//            $line->service_description . ";" .
//            $line->status . ";" .
//            $line->type . ";" .
//            $line->retry . ";" .
//            $line->output . ";" .
//            $line->contact . ";" .
//            $line->contact_cmd . "\n";
//    }
//} else {
//    echo _("Begin date") . "; "
//        . _("End date") . ";\n";
//    echo date(_('Y/m/d (H:i:s)'), (int)$xml->infos->start) . ";"
//        . date(_('Y/m/d (H:i:s)'), (int)$xml->infos->end) . "\n";
//    echo "\n";
//
//    echo _("Type") . ";"
//        . _("Notification") . ";"
//        . _("Alert") . ";"
//        . _("error") . "\n";
//    echo ";"
//        . $xml->infos->notification . ";"
//        . $xml->infos->alert . ";"
//        . $xml->infos->error . "\n";
//    echo "\n";
//
//    echo _("Day") . ";"
//        . _("Time") . ";"
//        . _("Poller") . ";"
//        . _("Output") . "; " . "\n";
//    foreach ($xml->line as $line) {
//        echo "\"" .
//            date(_('Y/m/d'), (int)$line->date) . "\";\"" .
//            date(_('H:i:s'), (int)$line->time) . "\";\"" .
//            $line->poller . "\";\"" .
//            $line->output . "\";" . "\n";
//    }
//}
