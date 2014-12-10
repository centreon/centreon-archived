<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 */
namespace CentreonMain\Controllers;

/**
 * Validators controller
 *
 * @authors Lionel Assepo
 * @package Centreon
 * @subpackage Controllers
 */
class ImageController extends \Centreon\Internal\Controller
{
    
    /**
     * Add a image
     *
     * @method get
     * @route /media/image/add
     */
    public function addAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $config = $di->get('config');
        $form = new \Centreon\Internal\Form\Wizard(
            rtrim($config->get('global', 'base_url'), '/').'/administration/media/image/add',
            0,
            array('id' => 0)
        );
        echo $form->generate();
    }
    
    /**
     * 
     * @method get
     * @route /image/icon/centreon
     */
    public function centreonIconAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $finalIconList = array();

        // Get User Images
        $dbconn = $di->get('db_centreon');
        $query = 'SELECT binary_id, filename FROM cfg_binaries';
        $stmt = $dbconn->query($query);
        $userImageExist = true;
        while ($row = $stmt->fetch()) {
            
            if ($userImageExist) {
                $finalIconList[] = array(
                    "text" => "User icon",
                );
                $userImageExist = false;
            }
            $filenameExploded = explode('.', $row['filename']);
            $nbOfOccurence = count($filenameExploded);
            $fileFormat = $filenameExploded[$nbOfOccurence-1];
            $filenameLength = strlen($row['filename']);
            $routeAttr = array(
                'image' => substr($row['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                'format' => '.'.$fileFormat
            );
            $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
            $finalIconList[] = array(
                "id" => $row['binary_id'],
                "text" => $row['filename'],
                "theming" => '<img src="'.$imgSrc.'" style="width:20px;height:20px;"> '.$row['filename']
            );
        }
        
        /*$iconList = array(
            'fa-bolt',
            'fa-camera',
            'fa-hdd-o',
            'fa-laptop',
            'fa-gears',
            'fa-mobile-phone',
            'fa-tablet',
            'fa-wrench'
        );
        
        $finalIconList[] = array(
            "text" => "Centreon icon",
        );
        
        foreach ($iconList as $icon) {
            $finalIconList[] = array(
                "id" => md5($icon),
                "text" => substr($icon, 3),
                "theming" => '<i class="fa '.$icon.'"></i> '.substr($icon, 3)
            );
        }*/
        
        $router->response()->json($finalIconList);
    }
}
