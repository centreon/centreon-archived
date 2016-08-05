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
define('STEP_NUMBER', 2);
$_SESSION['step'] = STEP_NUMBER;

require_once '../steps/functions.php';
$template = getTemplate('../steps/templates');

$title = _('Dependency check up');
$requiredLib = explode("\n", file_get_contents('../var/phplib'));
/*
 * PHP Libraries 
 */
$contents = "<table cellpadding='0' cellspacing='0' border='0' width='80%' class='StyleDottedHr' align='center'>";
$contents .= "<tr>
                <th>"._('Module name')."</th>
                <th>"._('File')."</th>
                <th>"._('Status')."</th>
             </tr>";
$allClear = 1;
foreach ($requiredLib as $line) {
    if (!$line) {
        continue;
    }
    $contents .= "<tr>";
    list($name, $lib) = explode(":", $line);
    $contents .= "<td>".$name."</td>";
    $contents .= "<td>".$lib."</td>";
    $contents .= "<td>";
    if (extension_loaded($lib)) {
        $libMessage = '<span style="color:#88b917; font-weight:bold;">'._('Loaded').'</span>';
    } else {
        $libMessage = '<span style="color:#e00b3d; font-weight:bold;">'._('Not loaded').'</span>';
        $allClear = 0;
    }
    $contents .= $libMessage;
    $contents .= "</td>";
    $contents .= "</tr>";
}

/* Test if timezone is set */
if (!ini_get('date.timezone')) {
    $contents .= "<tr>";
    $contents .= "<td>Timezone</td>";
    $contents .= "<td>"._("Set the default timezone in php.ini file") ."</td>";
    $contents .= "<td>";

    $libMessage = '<span style="color:#e00b3d; font-weight:bold;">'._('Not initialized').'</span>';
    $allClear = 0;
    $contents .= $libMessage;
    $contents .= "</td>";
    $contents .= "</tr>";
}
$contents .= "</table>";

/*
 * PEAR Libraries 
 */
//@todo

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $contents);
$template->display('content.tpl');
?>
<script type='text/javascript'>
    var allClear = <?php echo $allClear; ?> 
    /**
     * Validates info
     * 
     * @return bool
     */
    function validation() {
       if (!allClear) {
           return false;
       }
       return true; 
    }
</script>
