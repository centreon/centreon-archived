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
 */

namespace CentreonBam\Controllers;

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;
use CentreonBam\Repository\BusinessActivityTagRepository;

class BusinessActivityTagController extends FormController
{
    protected $objectDisplayName = 'BusinessActivityTag';
    public static $objectName = 'businessActivityTag';
    protected $objectBaseUrl = '/centreon-bam/businessactivitytag';
    protected $objectClass = '\CentreonBam\Models\BusinessActivityTag';
    protected $repository = '\CentreonBam\Repository\BusinessActivityTagRepository';

    public static $relationMap = array(
        'aclresource_businessactivitytags' => '\CentreonBam\Models\Relation\Aclresource\BusinessActivityTag'
    );
    
    /**
     * Get business activities for a specific acl resource
     *
     * @method get
     * @route /aclresource/[i:id]/businessactivity/tag
     */
    public function businessActivitiesForAclResourceAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');

        $requestParam = $this->getParams('named');
        $finalBaTagList = BusinessActivityTagRepository::getBusinessActivityTagsByAclResourceId($requestParam['id']);

        $router->response()->json($finalBaTagList);
    }
}
