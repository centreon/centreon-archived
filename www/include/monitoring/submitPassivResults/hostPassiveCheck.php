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

$o = "hd";

if (!isset($centreon)) {
    exit();
}

require_once _CENTREON_PATH_ . "www/class/centreonHost.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";

isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = null;
isset($_GET["cmd"]) ? $cmd = $_GET["cmd"] : $cmd = null;

$hObj = new CentreonHost($pearDB);
$path = "./include/monitoring/submitPassivResults/";

$pearDBndo = new CentreonDB("centstorage");

$aclObj = new CentreonACL($centreon->user->get_id());

if (!$is_admin) {
    $hostTab = explode(',', $centreon->user->access->getHostsString('NAME', $pearDBndo));
    foreach ($hostTab as $value) {
        if ($value == "'".$host_name."'") {
            $flag_acl = 1;
        }
    }
}
$hostTab = array();

if ($is_admin || ($flag_acl && !$is_admin)) {
    $form = new HTML_QuickFormCustom('select_form', 'GET', "?p=".$p);
    $form->addElement('header', 'title', _("Command Options"));

    $hosts = array($host_name=>$host_name);

    $form->addElement('select', 'host_name', _("Host Name"), $hosts, array("onChange" =>"this.form.submit();"));

    $form->addRule('host_name', _("Required Field"), 'required');

    $return_code = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");

    $form->addElement('select', 'return_code', _("Check result"), $return_code);
    $form->addElement('text', 'output', _("Check output"), array("size"=>"100"));
    $form->addElement('text', 'dataPerform', _("Performance data"), array("size"=>"100"));

    $form->addElement('hidden', 'author', $centreon->user->get_alias());
    $form->addElement('hidden', 'cmd', $cmd);
    $form->addElement('hidden', 'p', $p);

    $form->addElement('submit', 'submit', _("Save"), array("class" => "btc bt_success"));
    $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

    # Smarty template Init
    $tpl = new Smarty();
    $tpl = initSmartyTpl($path, $tpl);

    #Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);

    $tpl->assign('form', $renderer->toArray());
    $tpl->display("hostPassiveCheck.ihtml");
}
