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

/**
 * 
 */
class UserCommand extends BasicCrudCommand
{
    /**
     *
     * @var type 
     */
    public $objectName = 'user';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @cmdForm /centreon-administration/user/update required
     * @cmdParam boolean|false disable required
     */
    public function createAction($params) {
        parent::createAction($params);
    }
    
    /**
     * @cmdForm /centreon-administration/user/update optional
     * @cmdObject string user the user
     * @cmdParam boolean|false disable optional
     * @cmdParam boolean|true enable optional
     */
    public function updateAction($object, $params = null) {
        parent::updateAction($object, $params);
    }
    
    
    /**
     * @cmdForm /centreon-administration/user/update map
     * @cmdObject string user the user
     */
    public function showAction($object, $fields = null, $linkedObject = '') {
        parent::showAction($object, $fields, $linkedObject);
    }
    
    /**
     * @cmdObject string user the user
     */
    public function deleteAction($object) {
        parent::deleteAction($object);
    }
    
}
