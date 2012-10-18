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
DEFINE('STEP_NUMBER', 8);
$_SESSION['step'] = STEP_NUMBER;

require_once 'functions.php';
$template = getTemplate('./templates');

$title = _('Installation finished');

$contents = '<div>'._('The installation is now finished. To get further information regarding Centreon please visit the following links').':</div>';
$contents .= '
    <ul>
        <li>'._('Official website').': <a href="http://www.centreon.com">www.centreon.com</a></li>
        <li>'._('Forum').': <a href="http://forum.centreon.com">forum.centreon.com</a></li>
        <li>'._('Documentation').': <a href="http://documentation.centreon.com">documentation.centreon.com</a></li>
        <li>'._('Wiki').': <a href="http://doc.centreon.com">doc.centreon.com</a></li>
        <li>'._('Bug Tracker').': <a href="http://forge.centreon.com">forge.centreon.com</a></li>
    </ul>';
$contents .= _('For professional support subscription please contact the <a href="http://support.centreon.com">Centreon Support Center</a>.');

$tmpfname = tempnam("../..", "");
@unlink($tmpfname);
@rename(str_replace('steps', '', getcwd()), realpath("../..")."/".basename($tmpfname) );

session_destroy();

$template->assign('step', STEP_NUMBER);
$template->assign('title', $title);
$template->assign('content', $contents);
$template->assign('finish', 1);
$template->assign('blockPreview', 1);
$template->display('content.tpl');
?>