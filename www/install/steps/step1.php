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
DEFINE('STEP_NUMBER', 1);
$_SESSION['step'] = STEP_NUMBER;

require_once 'functions.php';
$template = getTemplate('./templates');

$title = _('Welcome to Centreon Setup');

if (is_file('../install.conf.php')) {
    $status = 0;
    $content = sprintf("<p>%s%s</p>",
                  _('This installer will help you setup your database and your monitoring configuration.'),
                  _('The entire process should take around ten minutes.'));
    require_once '../install.conf.php';
    setSessionVariables($conf_centreon);
} else {
    $status = 1;
    $content = sprintf("<p class='required'>%s (install.conf.php)</p>", _('Configuration file not found.'));
}

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $content);
$template->display('content.tpl');
?>
<script type='text/javascript'>
    var status = <?php echo $status;?>;
    /**
     * Validates info
     * 
     * @return bool
     */
    function validation() {
       if (status == 0) {
        return true;
       }
       return false;
    }
</script>