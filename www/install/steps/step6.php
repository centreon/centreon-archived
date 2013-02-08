<?php
/*
 * Copyright 2005-2011 MERETHIS
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

session_start();
DEFINE('STEP_NUMBER', 6);
DEFINE('DEFAULT_CONF_NAME', 'centreon');
DEFINE('DEFAULT_STORAGE_NAME', 'centreon_storage');
DEFINE('DEFAULT_UTILS_NAME', 'centreon_status');
DEFINE('DEFAULT_DB_USER', 'centreon');
DEFINE('DEFAULT_PORT', '3306');

$_SESSION['step'] = STEP_NUMBER;

require_once 'functions.php';
$template = getTemplate('./templates');
$title = _('Database information');

$defaults = array('ADDRESS' => '', 
                'DB_PORT' => DEFAULT_PORT,
                'root_password' => '', 
                'CONFIGURATION_DB' => DEFAULT_CONF_NAME, 
                'STORAGE_DB' => DEFAULT_STORAGE_NAME, 
                'UTILS_DB' => DEFAULT_UTILS_NAME,
                'DB_USER' => DEFAULT_DB_USER,
                'DB_PASS' => '',
                'db_pass_confirm' => '');
foreach ($defaults as $k => $v) {
    if (isset($_SESSION[$k])) {
        $defaults[$k] = $_SESSION[$k];
    }
}
$star = "<span style='color:#f91e05'> *</span>";
$contents = " 
    <form id='form_step".STEP_NUMBER."'>
        <table cellpadding='0' cellspacing='0' border='0' width='80%' class='StyleDottedHr' align='center'>
        <thead>
            <tr>
                <th colspan='2'>"._('Database information')."</th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td class='formlabel'>"._('Database Host Address (default: localhost)')."</td>
            <td class='formvalue'>
                <input type='text' name='ADDRESS' value='".$defaults['ADDRESS']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Database Port (default: 3306)')."</td>
            <td class='formvalue'>
                <input type='text' name='DB_PORT' value='".$defaults['DB_PORT']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Root password')."</td>
            <td class='formvalue'>
                <input type='password' name='root_password' value='".$defaults['root_password']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Configuration database name').$star."</td>
            <td class='formvalue'>
                <input type='text' name='CONFIGURATION_DB' value='".$defaults['CONFIGURATION_DB']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Storage database name').$star."</td>
            <td class='formvalue'>
                <input type='text' name='STORAGE_DB' value='".$defaults['STORAGE_DB']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Utils database name').$star."</td>
            <td class='formvalue'>
                <input type='text' name='UTILS_DB' value='".$defaults['UTILS_DB']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Database user name').$star."</td>
            <td class='formvalue'>
                <input type='text' name='DB_USER' value='".$defaults['DB_USER']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Database user password').$star."</td>
            <td class='formvalue'>
                <input type='password' name='DB_PASS' value='".$defaults['DB_PASS']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Confirm user password').$star."</td>
            <td class='formvalue'>
                <input type='password' name='db_pass_confirm' value='".$defaults['db_pass_confirm']."' />
                <label class='field_msg'></label>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
";

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $contents);
$template->display('content.tpl');
?>
<script type='text/javascript'>
    var step = <?php echo STEP_NUMBER;?>;
    
    /**
     * Validates info
     * 
     * @return bool
     */
    function validation() {
        var result = false;
        jQuery('label[class=field_msg]').html('');
        doProcess(false, './steps/process/process_step'+step+'.php', jQuery('#form_step'+step).serialize(), function(data) {
            if (data == 0) {
                result = true;
            } else {
                eval(data);
            }
       });
       return result;
    }
</script>