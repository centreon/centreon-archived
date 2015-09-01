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

namespace CentreonBam\Commands;

use CentreonConfiguration\Api\Internal\BasicTagSupport;
/**
 * 
 */
class BusinessActivityCommand extends BasicTagSupport
{
    /**
     *
     * @var type 
     */
    public $objectName = 'businessactivity';
    
    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 
     * @cmdForm /centreon-bam/businessactivity/update required 
     * @cmdParam boolean|false disable required disable
     * @cmdParam none ba-tags optional
     * @cmdParam boolean|false disable required
     */
    public function createAction($params)
    {
        parent::createAction($params);
    }
    
    /**
     * @cmdForm /centreon-bam/businessactivity/update map 
     * @cmdObject string ba the ba
     * 
     */     
    public function showAction($object, $fields = null, $linkedObject = '')
    {
        parent::showAction($object, $fields, $linkedObject);
    }
    
    /**
     * 
     * @cmdObject string ba the ba
     * 
     */     
    public function deleteAction($object)
    {
        parent::deleteAction($object);
    }
    
    /**
     * 
     * @cmdForm /centreon-bam/businessactivity/update optional
     * @cmdObject string ba the ba
     * @cmdParam none ba-tags optional
     * @cmdParam boolean|false disable optional
     * @cmdParam boolean|true enable optional
     */
    public function updateAction($object, $params = null)
    {
        parent::updateAction($object, $params);
    }
    
    
    /**
     * 
     * @cmdObject string ba the ba
     */
    public function listTagAction($object = null) 
    {
        parent::listTagAction($object);
    }
    
    /**
     * 
     * @cmdObject string ba the ba
     * @cmdParam string tag required the ba
     */
    public function addTagAction($object, $params) 
    {
        parent::addTagAction($object, $params['tag']);
    }
    
    /**
     * 
     * @cmdObject string ba the ba
     * @cmdParam string tag required the tag
     */
    public function removeTagAction($object, $params) 
    {
        parent::removeTagAction($object, $params['tag']);
    }
    
    
}
