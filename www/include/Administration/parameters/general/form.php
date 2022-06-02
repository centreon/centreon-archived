<?php

/*
 * Copyright 2005-2022 Centreon
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

require_once __DIR__ . '/../../../../../bootstrap.php';
require_once __DIR__ . "/../../../../class/centreonGMT.class.php";

const VERTICAL_NOTIFICATION = 1;
const CLOSE_NOTIFICATION = 2;
const CUMULATIVE_NOTIFICATION = 3;

if (!isset($centreon)) {
    exit();
}

// getting the garbage collector value set
define("SESSION_DURATION_LIMIT", (int)(ini_get('session.gc_maxlifetime') / 60));

$transcoKey = array(
    "enable_autologin" => "yes",
    "display_autologin_shortcut" => "yes",
    "enable_gmt" => "yes",
    "strict_hostParent_poller_management" => "yes",
    'display_downtime_chart' => 'yes',
    'display_comment_chart' => 'yes',
    'send_statistics' => 'yes'
);

$dbResult = $pearDB->query("SELECT * FROM `options` WHERE `key` <> 'proxy_password'");
while ($opt = $dbResult->fetch()) {
    if (isset($transcoKey[$opt["key"]])) {
        $gopt[$opt["key"]][$transcoKey[$opt["key"]]] = myDecode($opt["value"]);
    } else {
        $gopt[$opt["key"]] = myDecode($opt["value"]);
    }
}
$gopt['proxy_password'] = CentreonAuth::PWS_OCCULTATION;

/*
 * Style
 */
$attrsText = array("size" => "40");
$attrsText2 = array("size" => "5");
$attrsAdvSelect = null;

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("Modify General Options"));

/*
 * information
 */
$form->addElement('header', 'oreon', _("Centreon information"));
$form->addElement('text', 'oreon_path', _("Directory"), $attrsText);
$form->addElement('text', 'oreon_web_path', _("Centreon Web Directory"), $attrsText);

$form->addElement('text', 'session_expire', _("Sessions Expiration Time"), $attrsText2);

$inheritanceMode = array();
$inheritanceMode[] = $form->createElement(
    'radio',
    'inheritance_mode',
    null,
    _("Vertical Inheritance Only"),
    VERTICAL_NOTIFICATION
);

$inheritanceMode[] = $form->createElement(
    'radio',
    'inheritance_mode',
    null,
    _("Closest Value"),
    CLOSE_NOTIFICATION
);

$inheritanceMode[] = $form->createElement(
    'radio',
    'inheritance_mode',
    null,
    _("Cumulative inheritance"),
    CUMULATIVE_NOTIFICATION
);

$form->addGroup($inheritanceMode, 'inheritance_mode', _("Contacts & Contact groups method calculation"), '&nbsp;');
$form->setDefaults(array('inheritance_mode' => CUMULATIVE_NOTIFICATION));

$limit = array(10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50, 60 => 60, 70 => 70, 80 => 80, 90 => 90, 100 => 100);
$form->addElement('select', 'maxViewMonitoring', _("Limit per page for Monitoring"), $limit);
$form->addElement('text', 'maxGraphPerformances', _("Graph per page for Performances"), $attrsText2);

$form->addElement('text', 'maxViewConfiguration', _("Limit per page (default)"), $attrsText2);
$form->addElement('text', 'AjaxTimeReloadStatistic', _("Refresh Interval for statistics"), $attrsText2);
$form->addElement('text', 'AjaxTimeReloadMonitoring', _("Refresh Interval for monitoring"), $attrsText2);

$form->addElement('text', 'selectPaginationSize', _('Number of elements loaded in select'), $attrsText2);

$CentreonGMT = new CentreonGMT($pearDB);
$GMTList = $CentreonGMT->getGMTList();

$form->addElement('select', 'gmt', _("Timezone"), $GMTList);

$globalSortType = array(
    "host_name" => _("Hosts"),
    "last_state_change" => _("Duration"),
    "service_description" => _("Services"),
    "current_state" => _("Status"),
    "last_check" => _("Last check"),
    "output" => _("Output"),
    "criticality_id" => _("Criticality"),
    "current_attempt" => _("Attempt"),
);

$sortType = array(
    "last_state_change" => _("Duration"),
    "host_name" => _("Hosts"),
    "service_description" => _("Services"),
    "current_state" => _("Status"),
    "last_check" => _("Last check"),
    "plugin_output" => _("Output"),
    "criticality_id" => _("Criticality"),
);

$form->addElement('select', 'global_sort_type', _("Sort by  "), $globalSortType);
$global_sort_order = array("ASC" => _("Ascending"), "DESC" => _("Descending"));

$form->addElement('select', 'global_sort_order', _("Order sort "), $global_sort_order);

$form->addElement('select', 'problem_sort_type', _("Sort problems by"), $sortType);

$sort_order = array("ASC" => _("Ascending"), "DESC" => _("Descending"));
$form->addElement('select', 'problem_sort_order', _("Order sort problems"), $sort_order);

$options1[] = $form->createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup($options1, 'enable_autologin', _("Enable Autologin"), '&nbsp;&nbsp;');

$options2[] = $form->createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup($options2, 'display_autologin_shortcut', _("Display Autologin shortcut"), '&nbsp;&nbsp;');

/*
 * statistics options
 */
$stat = array();
$stat[] = $form->createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup($stat, 'send_statistics', _("Send anonymous statistics"), '&nbsp;&nbsp;');

/*
 * Proxy options
 */
$form->addElement('text', 'proxy_url', _("Proxy URL"), $attrsText);
$form->addElement(
    'button',
    'test_proxy',
    _("Test Internet Connection"),
    array("class" => "btc bt_success", "onClick" => "javascript:checkProxyConf()")
);
$form->addElement('text', 'proxy_port', _("Proxy port"), $attrsText2);
$form->addElement('text', 'proxy_user', _("Proxy user"), $attrsText);
$form->addElement('password', 'proxy_password', _("Proxy password"), $attrsText);

/**
 * Charts options
 */
$displayDowntimeOnChart[] = $form->createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup(
    $displayDowntimeOnChart,
    'display_downtime_chart',
    _("Display downtime and acknowledgment on chart"),
    '&nbsp;&nbsp;'
);
$displayCommentOnChart[] = $form->createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup(
    $displayCommentOnChart,
    'display_comment_chart',
    _("Display comment on chart"),
    '&nbsp;&nbsp;'
);

$options3[] = $form->createElement('checkbox', 'yes', '&nbsp;', '');
$form->addGroup($options3, 'enable_gmt', _("Enable Timezone management"), '&nbsp;&nbsp;');

/*
 * Support Email
 */
$form->addElement('text', 'centreon_support_email', _("Centreon Support Email"), $attrsText);

$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('nagios_path', 'slash');
$form->applyFilter('nagios_path_img', 'slash');
$form->applyFilter('nagios_path_plugins', 'slash');
$form->applyFilter('oreon_path', 'slash');
$form->applyFilter('oreon_web_path', 'slash');
$form->applyFilter('debug_path', 'slash');
$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
$form->addRule('oreon_path', _('Mandatory field'), 'required');
$form->addRule('oreon_path', _("Can't write in directory"), 'is_valid_path');
$form->addRule('oreon_web_path', _('Mandatory field'), 'required');
$form->addRule('AjaxTimeReloadMonitoring', _('Mandatory field'), 'required');
$form->addRule('AjaxTimeReloadMonitoring', _('Must be a number'), 'numeric');
$form->addRule('AjaxTimeReloadStatistic', _('Mandatory field'), 'required');
$form->addRule('AjaxTimeReloadStatistic', _('Must be a number'), 'numeric');
$form->addRule('selectPaginationSize', _('Mandatory field'), 'required');
$form->addRule('selectPaginationSize', _('Must be a number'), 'numeric');
$form->addRule('maxGraphPerformances', _('Mandatory field'), 'required');
$form->addRule('maxGraphPerformances', _('Must be a number'), 'numeric');
$form->addRule('maxViewConfiguration', _('Mandatory field'), 'required');
$form->addRule('maxViewConfiguration', _('Must be a number'), 'numeric');
$form->addRule('maxViewMonitoring', _('Mandatory field'), 'required');
$form->addRule('maxViewMonitoring', _('Must be a number'), 'numeric');
$form->addRule('session_expire', _('Mandatory field'), 'required');
$form->addRule('session_expire', _('Must be a number'), 'numeric');
$form->registerRule('isSessionDurationValid', 'callback', 'isSessionDurationValid');
$form->addRule(
    'session_expire',
    _("This value needs to be an integer lesser than") . " " . SESSION_DURATION_LIMIT . " min",
    'isSessionDurationValid'
);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path . 'general/', $tpl);

$form->setDefaults($gopt);

$subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
$form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

$valid = false;
if ($form->validate()) {
    try {
        /*
        * Update in DB
        */
        updateGeneralConfigData(1);

        /*
        * Update in Oreon Object
        */
        $centreon->initOptGen($pearDB);

        $o = null;
        $valid = true;
        $form->freeze();
    } catch (\InvalidArgumentException $e) {
        print("<div class='msg' align='center'>" . $e->getMessage() . "</div>");
        $valid = false;
    }
}

if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>" . _("Impossible to validate, one or more field is incorrect") . "</div>");
}

$form->addElement(
    "button",
    "change",
    _("Modify"),
    array("onClick" => "javascript:window.location.href='?p=" . $p . "'", 'class' => 'btc bt_info')
);

/*
 * Send variable to template
 */
$tpl->assign('o', $o);
$tpl->assign("sorting", _("Sorting"));
$tpl->assign("notification", _("Notification"));
$tpl->assign("genOpt_max_page_size", _("Maximum page size"));
$tpl->assign("genOpt_expiration_properties", _("Sessions Properties"));
$tpl->assign("time_min", _("minutes"));
$tpl->assign("genOpt_refresh_properties", _("Refresh Properties"));
$tpl->assign("time_sec", _("seconds"));
$tpl->assign("genOpt_global_display", _("Display properties"));
$tpl->assign("genOpt_problem_display", _("Problem display properties"));
$tpl->assign("genOpt_time_zone", _("Time Zone"));
$tpl->assign("genOpt_auth", _("Authentication properties"));
$tpl->assign("support", _("Support Information"));
$tpl->assign('statistics', _("Statistics"));
$tpl->assign('valid', $valid);

/*
 * prepare help texts
 */
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

$tpl->display("form.ihtml");
