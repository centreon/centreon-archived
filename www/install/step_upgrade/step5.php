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

session_start();
define('STEP_NUMBER', 5);

$_SESSION['step'] = STEP_NUMBER;

require_once '../steps/functions.php';
require_once __DIR__ . '/../../../bootstrap.php';
$db = $dependencyInjector['configuration_db'];

/**
 * @var $db CentreonDB
 */
$res = $db->query("SELECT `value` FROM `options` WHERE `key` = 'send_statistics'");
$stat = $res->fetch();
$template = getTemplate('templates');

/* If CEIP is disabled and if it's a major version of Centreon ask again */
$aVersion = explode('.', $_SESSION['CURRENT_VERSION']);
if ((int)$stat['value'] != 1 && (int)$aVersion[2] === 0) {
    $stat = false;
}

$title = _('Upgrade finished');

if (false === is_dir(_CENTREON_VARLIB_ . '/installs')) {
    $contents .= '<br>Warning : The installation directory cannot be moved. Please create the directory '
        . _CENTREON_VARLIB_ . '/installs and give apache user write permissions.';
    $moveable = false;
} else {
    $moveable = true;
    $contents = sprintf(
        _('Congratulations, you have successfully upgraded to Centreon version <b>%s</b>.'),
        $_SESSION['CURRENT_VERSION']
    );
}

if ($stat === false) {
    $contents .= '<br/> <hr> <br/> <form id=\'form_step5\'>
                    <table cellpadding=\'0\' cellspacing=\'0\' border=\'0\' class=\'StyleDottedHr\' align=\'center\'>
                        <tbody>
                        <tr>
                            <td class=\'formValue\'>
                                <div class=\'md-checkbox md-checkbox-inline\' style=\'display:none;\'>
                                    <input id=\'send_statistics\' value=\'1\' name=\'send_statistics\' type=\'checkbox\' checked=\'checked\'/>
                                    <label class=\'empty-label\' for=\'send_statistics\'></label>
                                </div>
                            </td>
                            <td class=\'formlabel\'>
                                <p style="text-align:justify">Centreon uses a telemetry system and a Centreon Customer Experience
                                Improvement Program whereby anonymous information about the usage of this server
                                may be sent to Centreon. This information will solely be used to improve the
                                software user experience. You will be able to opt-out at any time about CEIP program
                                through administration menu.
                                    Refer to
                                    <a target="_blank" style="text-decoration: underline" 
                                    href="http://ceip.centreon.com/">ceip.centreon.com</a>
                                    for further details.
                                </p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>';
}

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $contents);
$template->assign('finish', 1);
$template->assign('blockPreview', 1);
$template->display('content.tpl');

if ($moveable) {
    ?>
    <script>
        /**
         * Validates info
         *
         * @return bool
         */
        function validation() {
            jQuery.ajax({
                type: 'POST',
                url: './step_upgrade/process/process_step5.php',
                data: jQuery('input[name="send_statistics"]').serialize(),
                success: () => {
                    javascript:self.location = "../index.html"
                }
            })
        }
    </script>
    <?php
}
