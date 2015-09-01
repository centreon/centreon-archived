<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonMain\Controllers;

use Centreon\Internal\Di;
use Centreon\Internal\Controller;
use Centreon\Internal\Form\Generator\Web\Wizard;

/**
 * Validators controller
 *
 * @authors Lionel Assepo
 * @package Centreon
 * @subpackage Controllers
 */
class ImageController extends Controller
{
    
    /**
     * Add a image
     *
     * @method get
     * @route /media/image/add
     */
    public function addAction()
    {
        $di = Di::getDefault();
        $baseUrl = rtrim($di->get('config')->get('global', 'base_url'), '/');
        $form = new Wizard(
            $baseUrl . '/media/image/add',
            array('id' => 0)
        );
        $form->getFormFromDatabase();
        echo $form->generate();
    }
    
    /**
     * 
     * @method get
     * @route /image/icon/centreon
     */
    public function centreonIconAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $givenParameters = $this->getParams('get');
        
        $finalIconList = array();

        // Get User Images
        $dbconn = $di->get('db_centreon');
        $query = 'SELECT binary_id, filename FROM cfg_binaries';
        if (isset($givenParameters['q']) && !empty($givenParameters['q'])) {
            $query .= " WHERE filename like '%" . $dbconn->quote($givenParameters['q']) . "%'";
        }
        $query .= " ORDER BY filename ASC";
        
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
                "theming" => '<img src="'.$imgSrc.'"> '.$row['filename']
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
