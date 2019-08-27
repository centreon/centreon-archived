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

if (!isset($oreon)) {
    exit();
}

require_once __DIR__ . "/formFunction.php";

$dbResult = $pearDB->query("SELECT * FROM `options`");
while ($opt = $dbResult->fetch()) {
    $gopt[$opt["key"]] = myDecode($opt["value"]);
}
$dbResult->closeCursor();

// Check value for interval_length
if (!isset($gopt["interval_length"])) {
    $gopt["interval_length"] = 60;
}

if (!isset($gopt["nagios_path_img"])) {
    $gopt["nagios_path_img"] = _CENTREON_PATH_ . 'www/img/media/';
}


$attrsText = array("size"=>"40");
$attrsText2 = array("size"=>"5");
$attrsAdvSelect = null;

// Form begin
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("Modify General Options"));

// Nagios information
$form->addElement('header', 'nagios', _("Monitoring Engine information"));
$form->addElement('text', 'nagios_path_img', _("Images Directory"), $attrsText);
$form->addElement('text', 'nagios_path_plugins', _("Plugins Directory"), $attrsText);
$form->addElement('text', 'interval_length', _("Interval Length"), $attrsText2);
$form->addElement('text', 'mailer_path_bin', _("Directory + Mailer Binary"), $attrsText);

// Tactical Overview form
$limitArray = array();
for ($i = 10; $i <= 100; $i += 10) {
    $limitArray[$i] = $i;
}
$form->addElement('select', 'tactical_host_limit', _("Maximum number of hosts to show"), $limitArray);
$form->addElement('select', 'tactical_service_limit', _("Maximum number of services to show"), $limitArray);
$form->addElement('text', 'tactical_refresh_interval', _("Page refresh interval"), $attrsText2);

// Acknowledgement form
$form->addElement('checkbox', 'monitoring_ack_sticky', _("Sticky"));
$form->addElement('checkbox', 'monitoring_ack_notify', _("Notify"));
$form->addElement('checkbox', 'monitoring_ack_persistent', _("Persistent"));
$form->addElement('checkbox', 'monitoring_ack_active_checks', _("Force Active Checks"));
$form->addElement('checkbox', 'monitoring_ack_svc', _("Acknowledge services attached to hosts"));

// Downtime form
$form->addElement('checkbox', 'monitoring_dwt_fixed', _("Fixed"));
$form->addElement('checkbox', 'monitoring_dwt_svc', _("Set downtimes on services attached to hosts"));
$form->addElement('text', 'monitoring_dwt_duration', _("Duration"), $attrsText2);

$scaleChoices = array(
    "s" => _("seconds"),
    "m" => _("minutes"),
    "h" => _("hours"),
    "d" => _("days")
);
$form->addElement('select', 'monitoring_dwt_duration_scale', _("Scale of time"), $scaleChoices);

$form->addElement('hidden', 'gopt_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('nagios_path', 'slash');
$form->applyFilter('nagios_path_img', 'slash');
$form->applyFilter('nagios_path_plugins', 'slash');

$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
$form->registerRule('isNum', 'callback', 'isNum');

$form->addRule('nagios_path_plugins', _("The directory isn't valid"), 'is_valid_path');
$form->addRule('tactical_refresh_interval', _("Refresh interval must be numeric"), 'numeric');

$form->addRule('interval_length', _("This value must be a numerical value."), 'isNum');

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path . "/engine", $tpl);

$form->setDefaults($gopt);

$subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
$dbResult = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    // Update in DB
    updateNagiosConfigData($form->getSubmitValue("gopt_id"));

    // Update in Centreon Object
    $oreon->initOptGen($pearDB);

    $o = null;
    $valid = true;
    $form->freeze();
}
if (!$form->validate() && isset($_POST["gopt_id"])) {
    print("<div class='msg' align='center'>" . _("impossible to validate, one or more field is incorrect") . "</div>");
}

$form->addElement(
    "button",
    "change",
    _("Modify"),
    array("onClick"=>"javascript:window.location.href='?p=" . $p . "&o=engine'", 'class' => 'btc bt_info')
);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('o', $o);
$tpl->assign("genOpt_nagios_version", _("Monitoring Engine"));
$tpl->assign("genOpt_dbLayer", _("Monitoring database layer"));
$tpl->assign("genOpt_nagios_direstory", _("Engine Directories"));
$tpl->assign("tacticalOverviewOptions", _("Tactical Overview"));
$tpl->assign("genOpt_mailer_path", _("Mailer path"));
$tpl->assign("genOpt_monitoring_properties", "Monitoring properties");
$tpl->assign("acknowledgement_default_settings", _("Default acknowledgement settings"));
$tpl->assign("downtime_default_settings", _("Default downtime settings"));
$tpl->assign("seconds", _("seconds"));
$tpl->assign('valid', $valid);

$tpl->display("form.ihtml");
