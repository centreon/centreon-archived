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

class AclresourceController extends FormController
{
    protected $objectDisplayName = 'Acl Resource';
    public static $objectName = 'aclresource';
    protected $objectBaseUrl = '/centreon-administration/aclresource';
    protected $objectClass = '\CentreonAdministration\Models\Aclresource';
    public static $relationMap = array(
        'aclresource_usergroups' => '\CentreonAdministration\Models\Relation\Aclresource\Usergroup',
        'aclresource_environments' => '\CentreonAdministration\Models\Relation\Aclresource\Environment',
        'aclresource_domains' => '\CentreonAdministration\Models\Relation\Aclresource\Domain',
    );
    protected $datatableObject = '\CentreonAdministration\Internal\AclresourceDatatable';
    protected $repository = '\CentreonAdministration\Repository\AclresourceRepository';

    public static $isDisableable = true;
    public static $enableDisableFieldName = 'status';

    /**
     * Usergroups for a specific acl resource
     *
     * @method get
     * @route /aclresource/[i:id]/usergroup
     */
    public function usergroupForAclresourceAction()
    {
        parent::getRelations(static::$relationMap['aclresource_usergroups']);
    }

    /**
     * Environments for a specific acl resource
     *
     * @method get
     * @route /aclresource/[i:id]/environment
     */
    public function environmentForAclresourceAction()
    {
        parent::getRelations(static::$relationMap['aclresource_environments']);
    }

    /**
     * Domains for a specific acl resource
     *
     * @method get
     * @route /aclresource/[i:id]/domain
     */
    public function domainForAclresourceAction()
    {
        parent::getRelations(static::$relationMap['aclresource_domains']);
    }
}
