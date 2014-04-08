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


namespace Centreon\Controllers;

use \Centreon\Internal\Form;

class AclgroupController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'AclGroup';
    protected $objectName = 'aclgroup';
    protected $objectBaseUrl = '/administration/aclgroup';
    protected $objectClass = '\CentreonConfiguration\Models\Acl\Group';
    public static $relationMap = array(
        'aclgroup_contacts' => '\CentreonConfiguration\Models\Relation\Aclgroup\Contact',
        'aclgroup_contactgroups' => '\CentreonConfiguration\Models\Relation\Aclgroup\Contactgroup',
        'aclgroup_aclresources' => '\CentreonConfiguration\Models\Relation\Aclgroup\Aclresource',
        'aclgroup_aclmenus' => '\CentreonConfiguration\Models\Relation\Aclgroup\Aclmenu',
        'aclgroup_aclactions' => '\CentreonConfiguration\Models\Relation\Aclgroup\Aclaction'
    );
    
    /**
     * List aclgroups
     *
     * @method get
     * @route /administration/aclgroup
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /administration/aclgroup/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Create a new ACL group
     *
     * @method post
     * @route /administration/aclgroup/create
     */
    public function createAction()
    {
        parent::createAction();
    }

    /**
     * Update an ACL group
     *
     *
     * @method post
     * @route /administration/aclgroup/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a aclgroup
     *
     *
     * @method get
     * @route /administration/aclgroup/add
     */
    public function addAction()
    {
        // Init template
        $di = \Centreon\Internal\Di::getDefault();
        $tpl = $di->get('template');
        
        $form = new Form('aclgroupForm');
        $form->addText('name', _('Name'));
        $form->addText('description', _('Description'));
        $form->addTextarea('command_line', _('Commande Line'));
        
        $radios['list'] = array(
          array(
              'name' => 'Enabled',
              'label' => 'Enabled',
              'value' => '1'
          ),
          array(
              'name' => 'Disabled',
              'label' => 'Disabled',
              'value' => '0'
          )
        );
        $form->addRadio('status', _("Status"), 'status', '&nbsp;', $radios);
        
        $form->add('save_form', 'submit', _("Save"), array("onClick" => "validForm();"));
        $tpl->assign('form', $form->toSmarty());
        
        // Display page
        $tpl->display('configuration/aclgroup/edit.tpl');
    }
    
    /**
     * Update a aclgroup
     *
     *
     * @method get
     * @route /administration/aclgroup/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Retrieve list of acl groups for a form
     *
     * @method get
     * @route /administration/aclgroup/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * Duplicate action for aclgroup
     *
     * @method post
     * @route /administration/aclgroup/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }
    
    /**
     * Massive Change action for aclgroup
     *
     * @method post
     * @route /administration/aclgroup/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }
    
    /**
     * MC Field action for aclgroup
     *
     * @method post
     * @route /administration/aclgroup/mc_fields
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }
    
    /**
     * Delete action for aclgroup
     *
     * @method post
     * @route /administration/aclgroup/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }

    /**
     * Contacts for a specific acl group
     *
     * @method get
     * @route /administration/aclgroup/[i:id]/contact
     */
    public function contactForAclgroupAction()
    {
        parent::getRelations(static::$relationMap['aclgroup_contacts']);
    }

    /**
     * Contact groups for a specific acl group
     *
     * @method get
     * @route /administration/aclgroup/[i:id]/contactgroup
     */
    public function contactgroupForAclgroupAction()
    {
        parent::getRelations(static::$relationMap['aclgroup_contactgroups']);
    }

    /**
     * Acl resource for a specific acl group
     *
     * @method get
     * @route /administration/aclgroup/[i:id]/aclresource
     */
    public function aclresourceForAclgroupAction()
    {
        parent::getRelations(static::$relationMap['aclgroup_aclresources']);
    }

    /**
     * Acl menu for a specific acl group
     *
     * @method get
     * @route /administration/aclgroup/[i:id]/aclmenu
     */
    public function aclmenuForAclgroupAction()
    {
        parent::getRelations(static::$relationMap['aclgroup_aclmenus']);
    }

    /**
     * Acl action for a specific acl group
     *
     * @method get
     * @route /administration/aclgroup/[i:id]/aclaction
     */
    public function aclactionForAclgroupAction()
    {
        parent::getRelations(static::$relationMap['aclgroup_aclactions']);
    }
}
