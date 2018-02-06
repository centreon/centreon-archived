<?php
/**
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

require_once realpath(dirname(__FILE__) . "/../../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonCustomView.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonUser.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonXML.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonContactgroup.class.php";

session_start();

if (!isset($_POST['action']) || !isset($_SESSION['centreon'])) {
    exit();
}

$centreon = $_SESSION['centreon'];
$action = $_POST['action'];
$db = new CentreonDB();
if (CentreonSession::checkSession(session_id(), $db) == 0) {
    exit();
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
        if (isset($_POST['create_load']['create_load']) && $_POST['create_load']['create_load'] == 'create') {
            $customViewId = $viewObj->addCustomView($_POST);
            if (isset($_POST['widget_id'])) {
                $widgetObj->udpateViewWidgetRelations($customViewId, $_POST['widget_id']);
            }
        } elseif (isset($_POST['create_load']['create_load']) && $_POST['create_load']['create_load'] == 'load') {
            $customViewId = $viewObj->loadCustomView($_POST);
        }
    } elseif ($action == "edit" && $customViewId) {
        $viewObj->updateCustomView($_POST);

        if (isset($_POST['widget_id'])) {
            $widgetObj->udpateViewWidgetRelations($customViewId, $_POST['widget_id']);
        }

        //update share
        if (!isset($_POST['public'])) {
            $_POST['public'] = 0;
        }

        if (!isset($_POST['user_id'])) {
            $_POST['user_id'] = $centreon->user->user_id;
        }
    } elseif ($action == "share") {
        $viewObj->shareCustomView($_POST, $centreon->user->user_id);
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
        $centreon->user->setContactParameters($db, array('widget_view_rotation' => $_POST['timer']));
    } elseif ($action == "defaultEditMode") {
        $_SESSION['customview_edit_mode'] = $_POST['editMode'];
    } elseif ($action == "get_share_info") {
        /* Get share information for a view */
        if (isset($_POST['viewId'])) {
            $viewers = $viewObj->getUsersFromViewId($_POST['viewId']);
            $viewerGroups = $viewObj->getUsergroupsFromViewId($_POST['viewId']);
            $xml->startElement('contacts');
            foreach ($viewers as $viewer) {
                if ($viewer['user_id'] != $centreon->user->user_id) {
                    $xml->startElement('contact');
                    $xml->writeAttribute('locked', $viewer['locked']);
                    $xml->writeAttribute('id', $viewer['user_id']);
                    $xml->text($viewer['contact_name']);
                    $xml->endElement();
                }
            }
            $xml->endElement();
            $xml->startElement('contactgroups');
            foreach ($viewerGroups as $viewerGroup) {
                $xml->startElement('contactgroup');
                $xml->writeAttribute('locked', $viewerGroup['locked']);
                $xml->writeAttribute('id', $viewerGroup['usergroup_id']);
                $xml->text($viewerGroup['cg_name']);
                $xml->endElement();
            }
            $xml->endElement();
        }
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
