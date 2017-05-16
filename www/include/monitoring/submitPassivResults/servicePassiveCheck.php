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

$o = "svcd";

if (!isset($centreon)) {
    exit();
}

require_once(_CENTREON_PATH_ . "www/class/centreonHost.class.php");
require_once(_CENTREON_PATH_ . "www/class/centreonService.class.php");
include_once(_CENTREON_PATH_ . "www/class/centreonMeta.class.php");
require_once(_CENTREON_PATH_ . "www/class/centreonDB.class.php");

$host_name = isset($_GET["host_name"]) ? $_GET["host_name"] : null;
$service_description = isset($_GET["service_description"]) ? $_GET["service_description"] : null;
$cmd = isset($_GET["cmd"]) ? $_GET["cmd"] : null;
$is_meta = isset($_GET["is_meta"]) && $_GET["is_meta"] == 'true' ? $_GET["is_meta"] : 'false';

$hObj = new CentreonHost($pearDB);
$serviceObj = new CentreonService($pearDB);
$metaObj = new CentreonMeta($pearDB);
$path = "./include/monitoring/submitPassivResults/";

$pearDBndo = new CentreonDB("centstorage");

$host_id = $hObj->getHostId($host_name);
$hostDisplayName = $host_name;
$serviceDisplayName = $service_description;

if ($is_meta == 'true') {
    $metaId = null;
    if (preg_match('/meta_(\d+)/', $service_description, $matches)) {
        $metaId = $matches[1];
    }
    $hostDisplayName = 'Meta';
    $serviceId = $metaObj->getRealServiceId($metaId);
    $serviceParameters = $serviceObj->getParameters($serviceId, array('display_name'));
    $serviceDisplayName = $serviceParameters['display_name'];
}


if (!$is_admin && $host_id) {
    $flag_acl = 0;
    if ($is_meta == 'true') {
        $aclMetaServices = $centreon->user->access->getMetaServices();
        $aclMetaIds = array_keys($aclMetaServices);
        if (in_array($metaId, $aclMetaIds)) {
            $flag_acl = 1;
        }
    } else {
        $serviceTab = $centreon->user->access->getHostServices($pearDBndo, $host_id);
        if (in_array($service_description, $serviceTab)) {
            $flag_acl = 1;
        }
    }
}

if (($is_admin || $flag_acl) && $host_id) {
    #Pear library
    require_once "HTML/QuickForm.php";
    require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

    $form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
    $form->addElement('header', 'title', _("Command Options"));

    $return_code = array("0" => "OK","1" => "WARNING", "3" => "UNKNOWN", "2" => "CRITICAL");

    $form->addElement('select', 'return_code', _("Check result"), $return_code);
    $form->addElement('text', 'output', _("Check output"), array("size"=>"100"));
    $form->addElement('text', 'dataPerform', _("Performance data"), array("size"=>"100"));

    $form->addElement('hidden', 'host_name', $host_name);
    $form->addElement('hidden', 'service_description', $service_description);
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

    $tpl->assign('host_name', $hostDisplayName);
    $tpl->assign('service_description', $serviceDisplayName);
    $tpl->assign('form', $renderer->toArray());
    $tpl->display("servicePassiveCheck.ihtml");
}
