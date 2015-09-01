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

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Di;
use CentreonAdministration\Models\Domain;
use Centreon\Models\Image;
use Centreon\Controllers\FormController;

class DomainController extends FormController
{
    protected $objectDisplayName = 'Domain';
    public static $objectName = 'domain';
    protected $objectBaseUrl = '/centreon-administration/domain';
    protected $objectClass = '\CentreonAdministration\Models\Domain';
    protected $repository = '\CentreonAdministration\Repository\DomainRepository';
    
    public static $relationMap = array();
    
    protected $datatableObject = '\CentreonAdministration\Internal\DomainDatatable';
    public static $isDisableable = true;
    
    /**
     * Get list of domain for a specific host
     *
     *
     * @method get
     * @route /domain/[i:id]/icon
     */
    public function iconForDomainAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $finalIconList = array();
        $iconId = Domain::get($requestParam['id'], "icon_id");
        
        if (is_array($iconId) && (count($iconId) > 0)) {
        
            $icon = Image::getIcon($iconId['icon_id']);

            if (count($icon) > 0) {
                $filenameExploded = explode('.', $icon['filename']);
                $nbOfOccurence = count($filenameExploded);
                $fileFormat = $filenameExploded[$nbOfOccurence-1];
                $filenameLength = strlen($icon['filename']);
                $routeAttr = array(
                    'image' => substr($icon['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                    'format' => '.'.$fileFormat
                );
                $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
                $finalIconList = array(
                    "id" => $icon['binary_id'],
                    "text" => $icon['filename'],
                    "theming" => '<img src="'.$imgSrc.'" style="width:20px;height:20px;"> '.$icon['filename']
                );
            }
        
        }
        
        $router->response()->json($finalIconList);
        
    }
}
