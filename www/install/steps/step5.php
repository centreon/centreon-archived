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
DEFINE('STEP_NUMBER', 5);
$_SESSION['step'] = STEP_NUMBER;

require_once 'functions.php';
$template = getTemplate('./templates');

$title = _('Admin information');

$defaults = array('ADMIN_PASSWORD' => '', 
                'confirm_password' => '', 
                'firstname' => '', 
                'lastname' => '', 
                'email' => '');
foreach ($defaults as $k => $v) {
    if (isset($_SESSION[$k])) {
        $defaults[$k] = $_SESSION[$k];
    }
}
$star = "<span style='color:#e00b3d'> *</span>";
$contents = " 
    <form id='form_step".STEP_NUMBER."'>
        <table cellpadding='0' cellspacing='0' border='0' width='100%' class='StyleDottedHr' align='center'>
        <thead>
            <tr>
                <th colspan='2'>"._('Admin information')."</th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td class='formlabel'>"._('Login')."</td>
            <td class='formvalue'>
                <label>admin</label>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Password').$star."</td>
            <td class='formvalue'>
                <input type='password' name='ADMIN_PASSWORD' value='".$defaults['ADMIN_PASSWORD']."'/>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Confirm password').$star."</td>
            <td class='formvalue'>
                <input type='password' name='confirm_password' value='".$defaults['confirm_password']."'/>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('First name').$star."</td>
            <td class='formvalue'>
                <input type='text' name='firstname' value='".$defaults['firstname']."'/>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Last name').$star."</td>
            <td class='formvalue'>
                <input type='text' name='lastname' value='".$defaults['lastname']."'/>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>"._('Email').$star."</td>
            <td class='formvalue'>
                <input type='text' name='email' value='".$defaults['email']."'/>
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