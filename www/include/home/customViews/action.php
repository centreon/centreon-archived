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
require_once $centreon_path . "www/class/centreonDB.class.php";
require_once $centreon_path . "www/class/centreonCustomView.class.php";
require_once $centreon_path . "www/class/centreonWidget.class.php";
require_once $centreon_path . "www/class/centreonSession.class.php";
require_once $centreon_path . "www/class/centreonUser.class.php";
require_once $centreon_path . "www/class/centreonXML.class.php";
require_once $centreon_path . "www/class/centreonContactgroup.class.php";

session_start();

if (!isset($_POST['action']) || !isset($_SESSION['centreon'])) {
    exit;
}

$centreon = $_SESSION['centreon'];
$action = $_POST['action'];
$db = new CentreonDB();
if (CentreonSession::checkSession(session_id(), $db) == 0) {
    exit;
}

$viewObj = new CentreonCustomView($centreon, $db);
$widgetObj = new CentreonWidget($centreon, $db);
$xml = new CentreonXML();
if (isset($_POST['custom_view_id']) && $_POST['custom_view_id']) {
    $customViewId = $_POST['custom_view_id'];
    $permission = $viewObj->checkPermission($customViewId);
} else {
    $customViewId = "";
}
$xml->startElement('response');
try {
    if ($action == "add") {
        $customViewId = $viewObj->addCustomView($_POST);
        if (isset($_POST['widget_id'])) {
            $widgetObj->udpateViewWidgetRelations($customViewId, $_POST['widget_id']);
        }
    } elseif ($action == "edit" && $customViewId) {
        $viewObj->updateCustomView($_POST);
        if (isset($_POST['widget_id'])) {
            $widgetObj->udpateViewWidgetRelations($customViewId, $_POST['widget_id']);
        }
    } elseif ($action == "share") {
        $viewObj->shareCustomView($_POST);
    } elseif ($action == "remove") {
        $viewObj->removeUserFromView($_POST);
        $xml->writeElement('contact_name', $centreon->user->getContactName($db, $_POST['user_id']));
    } elseif ($action == "removegroup") {
        $cgObj = new CentreonContactgroup($db);
        $viewObj->removeUsergroupFromView($_POST);
        $xml->writeElement('contact_name', $cgObj->getNameFromCgId($_POST['usergroup_id']));
    } elseif ($action == "setPreferences") {
        $widgetObj->updateUserWidgetPreferences($_POST, $permission);
    } elseif ($action == "deleteWidget") {
        $widgetObj->deleteWidgetFromView($_POST);
    } elseif ($action == "position") {
        $widgetObj->updateWidgetPositions($_POST);
    } elseif ($action == "deleteView" && $customViewId) {
        $viewObj->deleteCustomView($customViewId);
    } elseif ($action == "addWidget") {
        $widgetObj->addWidget($_POST);
    } elseif ($action == "setDefault") {
        $viewObj->setDefault($customViewId);
    } elseif ($action == "setRotate" && isset($_POST['timer'])) {
        $_SESSION['rotation_timer'] = $_POST['timer'];
    }
    $xml->writeElement('custom_view_id', $customViewId);
} catch (CentreonCustomViewException $e) {
    $xml->writeElement('error', $e->getMessage());
} catch (CentreonWidgetException $e) {
    $xml->writeElement('error', $e->getMessage());
}
$xml->endElement();
header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
?>