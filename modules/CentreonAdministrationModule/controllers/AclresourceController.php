<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */


namespace CentreonAdministration\Controllers;

use \Centreon\Internal\Form;

class AclresourceController extends BasicController
{
    protected $objectDisplayName = 'AclResource';
    protected $objectName = 'aclresource';
    protected $objectBaseUrl = '/administration/aclresource';
    protected $objectClass = '\CentreonAdministration\Models\Aclresource';
    public static $relationMap = array(
    );
    protected $datatableObject = '\CentreonAdministration\Internal\AclresourceDatatable';
    public static $isDisableable = true;

    /**
     * List aclresources
     *
     * @method get
     * @route /administration/aclresource
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /administration/aclresource/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Create a new acl resource
     *
     * @method post
     * @route /administration/aclresource/create
     */
    public function createAction()
    {
        parent::createAction();
    }

    /**
     * Update an acl resource
     *
     *
     * @method post
     * @route /administration/aclresource/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a aclresource
     *
     *
     * @method get
     * @route /administration/aclresource/add
     */
    public function addAction()
    {
         $this->tpl->assign('validateUrl', '/administration/aclresource/add');
         parent::addAction();
    }
    
    /**
     * Update a aclresource
     *
     *
     * @method get
     * @route /administration/aclresource/[i:id]/[i:advanced]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Retrieve list of acl resources for a form
     *
     * @method get
     * @route /administration/aclresource/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * Duplicate action for aclresource
     *
     * @method post
     * @route /administration/aclresource/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }
    
    /**
     * Massive Change action for aclresource
     *
     * @method post
     * @route /administration/aclresource/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }
    
    /**
     * MC Field action for aclresource
     *
     * @method post
     * @route /administration/aclresource/mc_fields
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }
    
    /**
     * Delete action for aclresource
     *
     * @method post
     * @route /administration/aclresource/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
}
