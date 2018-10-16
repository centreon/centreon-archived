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

require_once "./include/monitoring/common-Func.php";
require_once "./class/centreonDB.class.php";

$pearDBndo = $pearDBO;


isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = null;
isset($_GET["service_description"]) ? $service_description = $_GET["service_description"] : $service_description = null;
isset($_GET["cmd"]) ? $cmd = $_GET["cmd"] : $cmd = null;
isset($_GET["en"]) ? $en = $_GET["en"] : $en = 1;

$path = "./include/monitoring/acknowlegement/";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl, './templates/');

if (!$is_admin) {
    $lcaHostByName["LcaHost"] = $centreon->user->access->getHostsServicesName($pearDBndo);
}

/*
 * HOST LCA
 */
if ($is_admin || (isset($lcaHostByName["LcaHost"][$host_name]))) {
    ## Form begin
    $form = new HTML_QuickFormCustom(
        'select_form',
        'POST',
        "?p=" . $p . "&host_name=" . urlencode($host_name) . "&service_description=" . urlencode($service_description)
    );
    $form->addElement('header', 'title', _("Acknowledge a Service"));

    $tpl->assign('hostlabel', _("Host Name"));
    $tpl->assign('hostname', $host_name);
    $tpl->assign('en', $en);

    $tpl->assign('servicelabel', _("Service"));
    $tpl->assign('servicedescription', $service_description);
    $tpl->assign('authorlabel', _("Alias"));
    $tpl->assign('authoralias', $centreon->user->get_alias());

    $ckbx[] = $form->addElement('checkbox', 'notify', _("notify"));
    if (isset($centreon->optGen['monitoring_ack_notify']) && $centreon->optGen['monitoring_ack_notify']) {
        $ckbx[0]->setChecked(true);
    }

    $ckbx1[] = $form->addElement('checkbox', 'sticky', _("sticky"));
    if (isset($centreon->optGen['monitoring_ack_sticky']) && $centreon->optGen['monitoring_ack_sticky']) {
        $ckbx1[0]->setChecked(true);
    }

    $ckbx2[] = $form->addElement('checkbox', 'persistent', _("persistent"));
    if (isset($centreon->optGen['monitoring_ack_persistent']) && $centreon->optGen['monitoring_ack_persistent']) {
        $ckbx2[0]->setChecked(true);
    }

    $ckbx3[] = $form->addElement('checkbox', 'force_check', _("Force active check"));
    if (isset($centreon->optGen['monitoring_ack_active_checks']) && $centreon->optGen['monitoring_ack_active_checks']) {
        $ckbx3[0]->setChecked(true);
    }

    $form->addElement('hidden', 'host_name', $host_name);
    $form->addElement('hidden', 'service_description', $service_description);
    $form->addElement('hidden', 'author', $centreon->user->get_alias());
    $form->addElement('hidden', 'cmd', $cmd);
    $form->addElement('hidden', 'p', $p);
    $form->addElement('hidden', 'en', $en);

    $form->applyFilter('__ALL__', 'myTrim');

    $textarea = $form->addElement('textarea', 'comment', _("comment"), array("rows" => "8", "cols" => "80"));
    $textarea->setValue(sprintf(_("Acknowledged by %s"), $centreon->user->get_alias()));

    $form->addRule('comment', _("Comment is required"), 'required', '', 'client');
    $form->setJsWarnings(_("Invalid information entered"), _("Please correct these fields"));

    $form->addElement('submit', 'submit', ($en == 1) ? _("Add") : _("Delete"), ($en == 1) ? array("class" => "btc bt_success") : array("class" => "btc bt_danger"));
    $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');

    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());

    $tpl->display("serviceAcknowledge.ihtml");
}
