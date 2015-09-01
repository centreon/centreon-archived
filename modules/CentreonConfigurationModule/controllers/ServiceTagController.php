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

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;
use CentreonConfiguration\Repository\ServiceTagRepository;

class ServiceTagController extends FormController
{
    protected $objectDisplayName = 'ServiceTag';
    public static $objectName = 'serviceTag';
    protected $objectBaseUrl = '/centreon-configuration/servicetag';
    protected $objectClass = '\CentreonConfiguration\Models\Servicetag';
    protected $repository = '\CentreonConfiguration\Repository\ServiceTagRepository';

    public static $relationMap = array(
        'aclresource_servicetags' => '\CentreonConfiguration\Models\Relation\Aclresource\Servicetag'
    );
    
    /**
     * Get service tags for a specific acl resource
     *
     * @method get
     * @route /aclresource/[i:id]/service/tag
     */
    public function serviceTagsForAclResourceAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');

        $requestParam = $this->getParams('named');
        $finalServiceTagList = ServiceTagRepository::getServiceTagsByAclResourceId($requestParam['id']);

        $router->response()->json($finalServiceTagList);
    }
}
