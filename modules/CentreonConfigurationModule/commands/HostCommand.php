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

namespace CentreonConfiguration\Commands;

use CentreonConfiguration\Api\Internal\BasicTagSupport;

/**
 * 
 */
class HostCommand extends BasicTagSupport
{
    /**
     *
     * @var type 
     */
    public $objectName = 'host';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    
    /**
     * 
     * @cmdForm /centreon-configuration/host/update required
     * @cmdParam none host-custommacros optional
     * @cmdParam none host-tags optional
     * @cmdParam boolean|false disable required disable the host
     */
    public function createAction($params) 
    {
        parent::createAction($params);
    }
    
    
    /**
     * 
     * @cmdForm /centreon-configuration/host/update optional
     * @cmdObject string host the host
     * @cmdParam none host-custommacros optional
     * @cmdParam none host-tags optional
     * @cmdParam boolean|false disable optional disable the host
     * @cmdParam boolean|true enable optional enable the host
     */
    public function updateAction($object, $params = null) 
    {
        parent::updateAction($object, $params);
    }
    
    /**
     * @cmdForm /centreon-configuration/host/update map
     * @cmdObject string host the host
     */
    public function showAction($object, $fields = null, $linkedObject = '') 
    {
        parent::showAction($object, $fields, $linkedObject);
    }
    
    /**
     * 
     * @cmdObject string host the host
     */
    public function deleteAction($object) 
    {
        parent::deleteAction($object);
    }
    
    /**
     * 
     * @cmdObject string host the host
     */
    public function listTagAction($object = null) 
    {
        parent::listTagAction($object);
    }
    
    /**
     * 
     * @cmdObject string host the host
     * @cmdParam string tag required the tag
     */
    public function addTagAction($object, $params) 
    {
        parent::addTagAction($object, $params['tag']);
    }
    
    /**
     * 
     * @cmdObject string host the host
     * @cmdParam string tag required the tag
     */
    public function removeTagAction($object, $params) 
    {
        parent::removeTagAction($object, $params['tag']);
    }
    
    /**
     * 
     * @cmdObject string host the host
     * @cmdParam string name required the macro name
     * @cmdParam string value required the macro value
     * @cmdParam boolean|true hidden required is the macro hidden ?
     */
    public function addMacroAction($object, $params) 
    {
        parent::addMacroAction($object, $params);
    }
    
    
    /**
     * 
     * @cmdObject string host the host
     * @cmdObject string macro the macro to update
     * @cmdParam string name optional the macro name
     * @cmdParam string value optional the macro value
     * @cmdParam boolean|true hidden optional is the macro hidden ?
     * @cmdParam boolean|false show optional is the macro showed ?
     */
    public function updateMacroAction($object, $params) {
        parent::updateMacroAction($object, $object['macro'], $params);
    }
    
    /**
     * 
     * @cmdObject string host the host
     */
    public function listMacroAction($object = null) {
        parent::listMacroAction($object);
    }
    
    
    /**
     * 
     * @cmdObject string host the host
     * @cmdObject string macro the macro to update
     */
    public function removeMacroAction($object, $params = null) {
        parent::removeMacroAction($object, $object['macro']);
    }

}
