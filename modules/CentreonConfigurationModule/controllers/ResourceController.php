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

use Centreon\Controllers\FormController;

class ResourceController extends FormController
{
    protected $objectDisplayName = 'Resource';
    public static $objectName = 'resource';
    public static $enableDisableFieldName = 'enabled';
    protected $objectBaseUrl = '/centreon-configuration/resource';
    protected $datatableObject = '\CentreonConfiguration\Internal\ResourceDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Resource';
    protected $repository = '\CentreonConfiguration\Repository\ResourceRepository';    
    public static $isDisableable = true;
    
    public static $relationMap = array(
        'resource_pollers' => '\CentreonConfiguration\Models\Relation\Resource\Poller'
    );

    /**
     * Commands for specific resource
     *
     * @method get
     * @route /resource/[i:id]/poller
     */
    public function commandsForResourceAction()
    {
        parent::getRelations(static::$relationMap['resource_pollers']);
    }
    
    /**
     * Create a new resource
     *
     * @method post
     * @route /resource/add
     */
    public function createAction()
    {
        parent::createAction();
    }
}
