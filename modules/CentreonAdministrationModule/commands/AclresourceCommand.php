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

namespace CentreonAdministration\Commands;

use Centreon\Api\Internal\BasicCrudCommand;
use Centreon\Internal\Di;
use CentreonAdministration\Events\aclTagsEvent;
/**
 * 
 */
class AclresourceCommand extends BasicCrudCommand
{
    /**
     *
     * @var type 
     */
    public $objectName = 'aclresource';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @cmdForm /centreon-administration/aclresource/update required
     * @cmdParam boolean|true all-hosts required all host 
     * @cmdParam boolean|true all-bas required all bas 
     */ 
    public function createAction($params) {
        
        $events = Di::getDefault()->get('events');
        $aclTagsEvent = new aclTagsEvent($params);
        $events->emit('centreon-administration.acl.tag', array($aclTagsEvent));
        $params = $aclTagsEvent->getParams();
        parent::createAction($params);
    }
    
    /**
     * @cmdForm /centreon-administration/aclresource/update map
     * @cmdObject string aclresource the acl resource
     */
    public function showAction($object, $fields = null, $linkedObject = '') 
    {
        parent::showAction($object, $fields, $linkedObject);
    }
    
    /**
     * 
     * @cmdForm /centreon-administration/aclresource/update optional
     * @cmdObject string aclresource the acl resource
     * @cmdParam boolean|true all-hosts optional all host 
     * @cmdParam boolean|true all-bas optional all bas 
     * @cmdParam boolean|false no-hosts optional no host 
     * @cmdParam boolean|false no-bas optional no bas 
     */
    public function updateAction($object, $params) 
    {
        $events = Di::getDefault()->get('events');
        $aclTagsEvent = new aclTagsEvent($params);
        $events->emit('centreon-administration.acl.tag', array($aclTagsEvent));
        $params = $aclTagsEvent->getParams();
        parent::updateAction($object, $params);
    }
    
    /**
     * @cmdObject string aclresource the acl resource
     */
    public function deleteAction($object) 
    {
        parent::deleteAction($object);
    }
    
}
