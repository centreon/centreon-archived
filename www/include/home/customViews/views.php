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
require_once _CENTREON_PATH_ . 'www/class/centreon.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonSession.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonCustomView.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonWidget.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonDB.class.php';
require_once _CENTREON_PATH_ . 'bootstrap.php';

session_start();
session_write_close();

try {
    if (!isset($_SESSION['centreon'])) {
        throw new Exception('No session found');
    }
    $centreon = $_SESSION['centreon'];
    $db = new CentreonDB();
    $locale = $centreon->user->get_lang();
    putenv("LANG=$locale");
    setlocale(LC_ALL, $locale);
    bindtextdomain("messages", _CENTREON_PATH_ . "www/locale/");
    bind_textdomain_codeset("messages", "UTF-8");
    textdomain("messages");

    if (CentreonSession::checkSession(session_id(), $db) === false) {
        throw new Exception('Invalid session');
    }
    $viewObj = new CentreonCustomView($centreon, $db);
    $widgetObj = new CentreonWidget($centreon, $db);

    /**
     * Smarty
     */
    $path = _CENTREON_PATH_ . "www/include/home/customViews/layouts/";
    $template = new Smarty();
    $template = initSmartyTplForPopup($path, $template, "./", _CENTREON_PATH_);

    $viewId = $viewObj->getCurrentView();
    $permission = $viewObj->checkPermission($viewId) ? 1 : 0;
    $ownership = $viewObj->checkOwnership($viewId) ? 1 : 0;
    $widgets = array();
    $columnClass = "column_0";
    $widgetNumber = 0;
    if ($viewId) {
        $columnClass = $viewObj->getLayout($viewId);
        $widgets = $widgetObj->getWidgetsFromViewId($viewId);
        foreach ($widgets as $widgetId => $val) {
            if (isset($widgets[$widgetId]['widget_order']) && $widgets[$widgetId]['widget_order']) {
                $tmp = explode("_", $widgets[$widgetId]['widget_order']);
                $widgets[$widgetId]['column'] = $tmp[0];
            } else {
                $widgets[$widgetId]['column'] = 0;
            }
            if (!$permission && $widgets[$widgetId]['title'] === "") {
                $widgets[$widgetId]['title'] = "&nbsp;";
            }
            $widgetNumber++;
        }
        $template->assign("columnClass", $columnClass);
        $template->assign("jsonWidgets", json_encode($widgets));
        $template->assign("widgets", $widgets);
    }
    $template->assign("permission", $permission);
    $template->assign("widgetNumber", $widgetNumber);
    $template->assign("ownership", $ownership);
    $template->assign("userId", $centreon->user->user_id);
    $template->assign("view_id", $viewId);
    $template->assign(
        "error_msg",
        _("No widget configured in this view. Please add a new widget with the \"Add widget\" button.")
    );
    $template->assign(
        'helpIcon',
        returnSvg("www/img/icons/question_2.svg", "var(--help-tool-tip-icon-fill-color)", 18, 18)
    );
    $template->display($columnClass . ".ihtml");
} catch (CentreonWidgetException $e) {
    echo $e->getMessage() . "<br/>";
} catch (CentreonCustomViewException $e) {
    echo $e->getMessage() . "<br/>";
} catch (Exception $e) {
    echo $e->getMessage() . "<br/>";
}
