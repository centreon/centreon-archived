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

session_start();
DEFINE('STEP_NUMBER', 3);
$_SESSION['step'] = STEP_NUMBER;

require_once '../steps/functions.php';
$template = getTemplate('templates');

require_once __DIR__ . "/../../../config/centreon.config.php";
require_once __DIR__ . "/../../class/centreonDB.class.php";

$title = _('Release notes');

$db = new CentreonDB();
$res = $db->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
$row = $res->fetchRow();
$current = $row['value'];

$_SESSION['CURRENT_VERSION'] = $current;

/*
** To find the final version that we should update to, we will look in
** the www/install/php directory where all PHP update scripts are
** stored. We will extract the target version from the filename and find
** the farthest version to the current version.
*/
$next = '';
if ($handle = opendir('../php')) {
    while (false !== ($file = readdir($handle))) {
        if (preg_match('/Update-([a-zA-Z0-9\-\.]+)\.php/', $file, $matches)) {
            if ((version_compare($current, $matches[1]) < 0) &&
                (empty($next) || (version_compare($next, $matches[1]) < 0))) {
                $next = $matches[1];
            }
        }
    }
    closedir($handle);
}

$majors = preg_match('/^(\d+\.\d+)/', $next, $matches);
if (!isset($matches[1])) {
    $matches[1] = "current";
}
$releaseNoteLink = "https://documentation.centreon.com/" . $matches[1] . '/en/releases/centreon-core.html';

$title = _('Release notes');

$contents = '<p><b>' . _('Everything is ready !') . '</b></p>';
$contents .= '<p>' . _('Your Centreon Platform is about to be upgraded from version ') . $current . _(' to ') . $next . '</p>';
$contents .= '<p>' . _('For further details on changes, please find the complete changelog on ');
$contents .= '<a href="' . $releaseNoteLink . '"target="_blank" style="text-decoration:underline;font-size:11px">documentation.centreon.com</a></p>';

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $contents);
$template->assign('blockPreview', 1);
$template->display('content.tpl');
?>
<script type='text/javascript'>
    var step3_s = 3;
    var step3_t;

    jQuery(function () {
        jQuery('#next').attr('disabled', 'disabled');
        timeout_button();
    });

    function timeout_button() {
        if (step3_t) {
            clearTimeout(step3_t);
        }
        jQuery("#next").val("Next (" + step3_s + ")");
        step3_s--;
        if (step3_s == 0) {
            jQuery("#next").val("Next");
            jQuery("#next").removeAttr('disabled');
        } else {
            step3_t = setTimeout('timeout_button()', 1000);
        }
    }

    function validation() {
        return true;
    }
</script>
