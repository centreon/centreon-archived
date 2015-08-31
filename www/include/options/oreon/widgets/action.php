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

require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path . "www/class/centreon.class.php";
require_once $centreon_path . "www/class/centreonXML.class.php";
require_once $centreon_path . "www/class/centreonSession.class.php";
require_once $centreon_path . "www/class/centreonDB.class.php";
require_once $centreon_path . "www/class/centreonWidget.class.php";

session_start();

$xml = new CentreonXML();
$xml->startElement('response');
try {
    if (!isset($_SESSION['centreon']) ||
        !isset($_REQUEST['action']) ||
        !isset($_REQUEST['directory'])) {
            throw new Exception('Error with request');
    }
    $centreon = $_SESSION['centreon'];
    $action = $_REQUEST['action'];
    $directory = $_REQUEST['directory'];
    $db = new CentreonDB();
    $widgetObj = new CentreonWidget($centreon, $db);
    switch ($action) {
        case 'install' :
            $widgetObj->install($centreon_path."www/widgets/", $directory);
            break;
        case 'uninstall' :
            $widgetObj->uninstall($directory);
            break;
        case 'upgrade' :
            $widgetObj->upgrade($centreon_path."www/widgets/", $directory);
            break;
        default : throw new Exception('Unknown action');
    }
    $xml->writeElement('result', 1);
} catch (Exception $e) {
    $xml->writeElement('error', $e->getMessage());
}
$xml->endElement();
header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
