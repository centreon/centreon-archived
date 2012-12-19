<?php
/*
 * Copyright 2005-2012 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
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

    if (!isset($oreon)) {
        exit();
    }

    /*
	 * Path to the configuration dir
	 */
	$path = "./include/configuration/configCentreonBroker/";

	/*
	 * PHP functions
	 */
	require_once "./include/common/common-Func.php";

    require_once "./class/centreonWizard.php";

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path . 'wizard/', $tpl);

    if (!isset($_SESSION['wizard'])) {
        $_SESSION['wizard'] = array();
    }

    if (!isset($_POST['name']) || !isset($_POST['uuid']) || !isset($_POST['step'])) {
        $tpl->assign('strerr', _('Bad arguments for wizard.'));
        $tpl->display("error.ihtml");
        return;
    }
    $name = $_POST['name'];
    $step = $_POST['step'];
    $uuid = $_POST['uuid'];
    $finish = false;
    if (isset($_POST['finish'])) {
        $finish = $_POST['finish'];
    }

    if (false === isset($_SESSION['wizard'][$name][$uuid])) {
        $tpl->assign('strerr', _('The wizard is not correctly initialized.'));
        $tpl->display("error.ihtml");
        return;
    }
    $wizard = unserialize($_SESSION['wizard'][$name][$uuid]);
    if (false === $wizard->testUuid($uuid)) {
        $tpl->assign('strerr', _('The wizard is corrupted.'));
        $tpl->display("error.ihtml");
        return;
    }

    if (isset($_POST['values'])) {
        file_put_contents('/tmp/wizard', var_export($wizard, true));
        $wizard->addValues($step - 1, $_POST['values']);
        file_put_contents('/tmp/wizard', var_export($wizard, true), FILE_APPEND);
        $_SESSION['wizard'][$name][$uuid] = serialize($wizard);
    }

    $lang = array();
    $msgErr = array();
    if ($finish) {
        include $path . 'wizard/save.php';
        if (count($msgErr) > 0) {
            $page = 'error.ihtml';
            $tpl->assign('strerr', _('Error while saving configuration.'));
        } else {
            $page = 'finish.ihtml';
            $lang['configuration_saved'] = _('Configuration saved.');
        }
    } else {
        switch ($step) {
            case 1:
                $lang['welcome'] = _('Welcome to Centreon Broker configuration');
                $lang['steptext'] = _('Choose a configuration template:');
                $lang['central_configuration_without_poller'] = _('Central without poller');
                $lang['central_configuration_with_poller'] = _('Central with pollers');
                $lang['poller_configuration'] = _('Simple poller');
                $page = 'step1.ihtml';
                break;
            case 2:
                include $path . 'wizard/step2.php';
                break;
            default:
                $tpl->assign('strerr', "The step doesn't exist.");
                $page = 'error.ihtml';
                break;
        }
    }
    $tpl->assign('lang', $lang);
    $tpl->assign('msgErr', $msgErr);
    $tpl->display($page);