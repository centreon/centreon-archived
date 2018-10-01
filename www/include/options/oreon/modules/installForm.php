<?php
/*
 * Copyright 2005-2015 Centreon
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

$tpl->assign('headerMenu_title', _('Module Information'));
$tpl->assign('headerMenu_title2', _('Upgrade Information'));
$tpl->assign('headerMenu_rname', _('Real name'));
$tpl->assign('headerMenu_release', _('Release'));
$tpl->assign('headerMenu_release_from', _('Base release'));
$tpl->assign('headerMenu_release_to', _('Final release'));
$tpl->assign('headerMenu_author', _('Author'));
$tpl->assign('headerMenu_infos', _('Additional Information'));
$tpl->assign('headerMenu_isinstalled', _('Installed'));

if (isset($_GET['action']) && $_GET['action'] == 'install_upgrade_all') {
    $installableModules = $moduleInfoObj->getInstallableList();
    $upgradableModules = $moduleInfoObj->getUpgradeableList();
    $installed = [];
    $upgraded = [];

    foreach ($installableModules as $installableModule) {
        $moduleInstaller = $moduleFactory->newInstaller($installableModule['name']);

        if ( !$moduleInstaller->install() ) {
            break;
        }
    }

    foreach ($upgradableModules as $upgradableModule) {
        $moduleUpgrader = $moduleFactory->newUpgrader($upgradableModule['name'], $upgradableModule['id']);

        if ( !$moduleUpgrader->upgrade() ) {
            break;
        }
    }

    $centreon->creatModuleList($pearDB);
    $centreon->user->access->updateTopologyStr();
    $centreon->initHooks();

    $tpl->assign('p', $p);
    $tpl->display('modulesAction.tpl');
    die;
}

$moduleInfo = $moduleInfoObj->getConfiguration($name);

$tpl->assign('module_rname', $moduleInfo['rname']);
$tpl->assign('module_release', $moduleInfo['mod_release']);
$tpl->assign('module_author', $moduleInfo['author']);
$tpl->assign('module_infos', $moduleInfo['infos']);

if (file_exists($moduleInfoObj->getModulePath() . '/infos/infos.txt')) {
    $content = file_get_contents($moduleInfo->getModulePath() . '/infos/infos.txt');
    $content = implode('<br />', $content);
    $tpl->assign('module_infosTxt', $content);
} else {
    $tpl->assign('module_infosTxt', false);
}

$form1 = new HTML_QuickFormCustom('Form', 'post', "?p={$p}");

if ($form1->validate()) {
    $moduleInstaller = $moduleFactory->newInstaller($name);
    $insert_ok = $moduleInstaller->install();

    if ($insert_ok) {
        $tpl->assign('output', _('Module installed and registered'));

        /* Rebuild modules in centreon object */
        $centreon->creatModuleList($pearDB);
        $centreon->user->access->updateTopologyStr();
        $centreon->initHooks();
    } else {
        $tpl->assign('output', _('Unable to install module'));
    }
} elseif ($o == 'i' && !$moduleInfoObj->getInstalledInformation($name)) {
    $form1->addElement('submit', 'install', _('Install Module'), array('class' => 'btc bt_success'));
    $redirect = $form1->addElement('hidden', 'o');
    $redirect->setValue('i');
}

$form1->addElement('submit', 'list', _('Back'), array('class' => 'btc bt_default'));
$hid_name = $form1->addElement('hidden', 'name');
$hid_name->setValue($name);
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form1->accept($renderer);
$tpl->assign('form1', $renderer->toArray());

/**
 * Display form
 */
$tpl->display('installForm.tpl');
