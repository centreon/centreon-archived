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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($centreon)) {
    exit();
}

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign("headerMenu_title", _("Module Information"));
$tpl->assign("headerMenu_title2", _("Upgrade Information"));
$tpl->assign("headerMenu_rname", _("Real name"));
$tpl->assign("headerMenu_release", _("Release"));
$tpl->assign("headerMenu_release_from", _("Base release"));
$tpl->assign("headerMenu_release_to", _("Final release"));
$tpl->assign("headerMenu_author", _("Author"));
$tpl->assign("headerMenu_infos", _("Additional Information"));
$tpl->assign("headerMenu_isinstalled", _("Installed"));

$moduleFactory = new CentreonLegacy\Core\Module\Factory();
$moduleInfoObj = $moduleFactory->newInformation();

if (is_null($name)) {
    $name = $moduleInfoObj->getNameById($id);
}
$moduleUpgrader = $moduleFactory->newUpgrader($name, $id);

$moduleInfo = $moduleInfoObj->getConfiguration($name);
$moduleInstalledInfo = $moduleInfoObj->getInstalledInformation($name);

$elemArr = array();
$form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
$form->addElement('submit', 'list', _("Back"), array("class" => "btc bt_default"));
$form->addElement('submit', 'upgrade', _("Upgrade"), array("class" => "btc bt_success"));
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue("u");
if (!is_null($id)) {
    $hid_id = $form->addElement('hidden', 'id');
    $hid_id->setValue($id);
}

$upgradeAvailable = false;
if ($moduleInstalledInfo["mod_release"] != $moduleInfo["mod_release"]) {
    $upgradeAvailable = true;
}
if ($form->validate()) {
    $upgrade_ok = $moduleUpgrader->upgrade();
    if ($upgrade_ok) {
        $upgradeAvailable = false;
        $centreon->creatModuleList($pearDB);
        $centreon->user->access->updateTopologyStr();
        $centreon->initHooks();
    }

    if (is_dir(_CENTREON_PATH_ . "www/modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos") && is_file("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos/infos.txt")) {
        $infos_streams = file(_CENTREON_PATH_ . "www/modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos/infos.txt");
        $infos_streams = implode("<br />", $infos_streams);
        $upgrade_infosTxt = $infos_streams;
    } else {
        $upgrade_infosTxt = false;
    }
}

$module = array(
    "upgrade_rname" => $moduleInfo["rname"],
    "upgrade_release_from" => $moduleInstalledInfo["mod_release"],
    "upgrade_release_to" => $moduleInfo["mod_release"],
    "upgrade_author" => $moduleInfo["author"],
    "upgrade_infos" =>$moduleInfo["infos"],
    "upgrade_infosTxt" => $upgrade_infosTxt,
    "upgrade_available" => $upgradeAvailable
);

$tpl->assign("module", $module);
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

/**
 * Display form
 */
$tpl->display("upgradeForm.tpl");