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

if (!isset($centreon)) {
    exit();
}

$select = array();
if (isset($_GET['select'])) {
    foreach ($_GET['select'] as $key => $value) {
        if ($cmd == '72') {
            $tmp = preg_split("/\;/", urlencode($key));
            $select[] = $tmp[0];
        } else {
            $select[] = urlencode($key);
        }
    }
}

$path = _CENTREON_PATH_ . "/www/include/monitoring/external_cmd/popup/";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTplForPopup($path, $tpl, './templates/', _CENTREON_PATH_);

require_once _CENTREON_PATH_ . "www/include/monitoring/common-Func.php";

/*
 * Fetch default values for form
 */
$user_params = get_user_param($centreon->user->user_id, $pearDB);

if (!isset($user_params["ack_sticky"])) {
    $user_params["ack_sticky"] = 1;
}
if (!isset($user_params["ack_notify"])) {
    $user_params["ack_notify"] = 0;
}
if (!isset($user_params["ack_persistent"])) {
    $user_params["ack_persistent"] = 1;
}
if (!isset($user_params["ack_services"])) {
    $user_params["ack_services"] = 1;
}
if (!isset($user_params["force_check"])) {
    $user_params["force_check"] = 1;
}
/*
$sticky = $user_params["ack_sticky"];
$notify = $user_params["ack_notify"];
$persistent = $user_params["ack_persistent"];
$force_check = $user_params["force_check"];
$ack_services = $user_params["ack_services"];
*/
$form = new HTML_QuickFormCustom('select_form', 'GET', 'main.php');

$form->addElement('header', 'title', _("Acknowledge problems"));

$tpl->assign('authorlabel', _("Alias"));
$tpl->assign('authoralias', $centreon->user->get_alias());

$form->addElement('textarea', 'comment', _("Comment"), array("rows" => "5", "cols" => "85", "id" => "popupComment"));
$form->setDefaults(array("comment" => sprintf(_("Acknowledged by %s"), $centreon->user->alias)));

$chckbox[] = $form->addElement('checkbox', 'persistent', _("Persistent"), "", array("id" => "persistent"));
if (isset($centreon->optGen['monitoring_ack_persistent']) && $centreon->optGen['monitoring_ack_persistent']) {
    $chckbox[0]->setChecked(true);
}

$chckbox2[] = $form->addElement(
    'checkbox',
    'ackhostservice',
    _("Acknowledge services attached to hosts"),
    "",
    array("id" => "ackhostservice")
);
if (isset($centreon->optGen['monitoring_ack_svc']) && $centreon->optGen['monitoring_ack_svc']) {
    $chckbox2[0]->setChecked(true);
}

$chckbox3[] = $form->addElement('checkbox', 'sticky', _("Sticky"), "", array("id" => "sticky"));
if (isset($centreon->optGen['monitoring_ack_sticky']) && $centreon->optGen['monitoring_ack_sticky']) {
    $chckbox3[0]->setChecked(true);
}

$chckbox4[] = $form->addElement('checkbox', 'force_check', _("Force active checks"), "", array("id" => "force_check"));
if (isset($centreon->optGen['monitoring_ack_active_checks']) && $centreon->optGen['monitoring_ack_active_checks']) {
    $chckbox4[0]->setChecked(true);
}

$chckbox5[] = $form->addElement('checkbox', 'notify', _("Notify"), "", array("id" => "notify"));
if (isset($centreon->optGen['monitoring_ack_notify']) && $centreon->optGen['monitoring_ack_notify']) {
    $chckbox5[0]->setChecked(true);
}

$form->addElement('hidden', 'author', $centreon->user->get_alias(), array("id" => "author"));

$form->addRule('comment', _("Comment is required"), 'required', '', 'client');
$form->setJsWarnings(_("Invalid information entered"), _("Please correct these fields"));

$form->addElement(
    'button',
    'submit',
    _("Acknowledge selected problems"),
    array("onClick" => "send_the_command();", "class" => "btc bt_info")
);
$form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');

$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

$tpl->assign('o', $o);
$tpl->assign('p', $p);
$tpl->assign('cmd', $cmd);
$tpl->assign('select', $select);
$tpl->display("massive_ack.ihtml");
