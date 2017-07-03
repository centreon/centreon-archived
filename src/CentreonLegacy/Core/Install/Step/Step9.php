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

namespace CentreonLegacy\Core\Install\Step;

class Step9 extends AbstractStep
{
    public function getContent()
    {
        $installDir = __DIR__ . '/../../../../../www/install';
        require_once $installDir . '/steps/functions.php';
        $template = getTemplate($installDir . '/steps/templates');

        $backupDir = __DIR__ . '/../../../../../installDir';
        $contents = '';
        if (!is_dir($backupDir)) {
            $contents .= '<br>Warning : The installation directory cannot be move. ' .
                'Please create the directory ' . $backupDir . ' ' .
                'and give it the rigths to apache user to write.';
        } else {
            $contents = $this->getAdvertisement();

        }

        $template->assign('title', _('Installation finished'));
        $template->assign('step', 9);
        $template->assign('finish', 1);
        $template->assign('blockPreview', 1);
        $template->assign('contents', $contents);
        return $template->fetch('content.tpl');
    }

    private function getAdvertisement()
    {
        $adContent = '';
        if ($sock = fsockopen("www.centreon.com", 80, $num, $error, 5)) {
            $adContent = "http://blog-centreon-wordpress.s3.amazonaws.com/wp-content/uploads/2015/12/custom_view.jpg";
        } elseif (file_exists("../../img/centreon.png")) {
            fclose($sock);
            $adContent = "../../img/centreon.png";
        }

        return $adContent;
    }
}
