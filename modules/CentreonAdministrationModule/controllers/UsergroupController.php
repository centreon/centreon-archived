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

use Centreon\Internal\Form;
use Centreon\Controllers\FormController;

class UsergroupController extends FormController
{
    protected $objectDisplayName = 'Usergroup';
    public static $objectName = 'usergroup';
    protected $objectBaseUrl = '/centreon-administration/usergroup';
    protected $objectClass = '\CentreonAdministration\Models\Usergroup';
    public static $relationMap = array(
        'usergroup_users' => '\CentreonAdministration\Models\Relation\Usergroup\User',
    );
    protected $datatableObject = '\CentreonAdministration\Internal\UsergroupDatatable';
    protected $repository = '\CentreonAdministration\Repository\UsergroupRepository';

    public static $isDisableable = true;
    public static $enableDisableFieldName = 'status';
    
    /**
     * Users for a specific usergroup
     *
     * @method get
     * @route /usergroup/[i:id]/user
     */
    public function userForAclgroupAction()
    {
        parent::getRelations(static::$relationMap['usergroup_users']);
    }
}
