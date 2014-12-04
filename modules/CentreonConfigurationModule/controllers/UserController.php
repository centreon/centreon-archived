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

namespace CentreonConfiguration\Controllers;

use \CentreonConfiguration\Models\Contact;
use \CentreonAdministration\Internal\User;

class UserController extends \CentreonConfiguration\Controllers\BasicController
{
    protected $objectDisplayName = 'User';
    protected $objectName = 'user';
    protected $objectBaseUrl = '/centreon-configuration/user';
    protected $datatableObject = '\CentreonConfiguration\Internal\UserDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Contact';
    protected $repository = '\CentreonConfiguration\Repository\UserRepository';
    public static $relationMap = array(
        'contact_contactgroups' => '\CentreonConfiguration\Models\Relation\Contact\Contactgroup',
        'contact_hostcommands' => '\CentreonConfiguration\Models\Relation\Contact\Hostcommand',
        'contact_servicecommands' => '\CentreonConfiguration\Models\Relation\Contact\Servicecommand',
        'contact_aclgroups' => '\CentreonConfiguration\Models\Relation\Aclgroup\Contact'
    );
    
    public static $isDisableable = true;

    /**
     * List users
     *
     * @method get
     * @route /user
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /user/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * 
     * @method get
     * @route /user/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * Update a user
     *
     *
     * @method post
     * @route /user/update
     */
    public function updateAction()
    {
        parent::updateAction();

        /* Let's see if we need to refresh the user object that is stored in session */
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $userId = $user->getId();
            $givenParameters = $this->getParams('post');
            /* Modified account matches the current user */
            if (isset($givenParameters['object_id']) && $givenParameters['object_id'] == $userId) {
                $_SESSION['user'] = new User($userId); 
            }
        }
    }
    
    /**
     * Add a user
     *
     *
     * @method post
     * @route /user/add
     */
    public function createAction()
    {
        parent::createAction();
    }
    
    /**
     * Add a user
     *
     * @method get
     * @route /user/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/centreon-configuration/user/add');
        parent::addAction();
    }
    
    /**
     * Update a user
     *
     *
     * @method get
     * @route /user/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /user/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /user/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate contact
     *
     * @method POST
     * @route /user/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /user/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for contact
     *
     * @method post
     * @route /user/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Enable action for contact
     * 
     * @method post
     * @route /user/enable
     */
    public function enableAction()
    {
        parent::enableAction('contact_activate');
    }
    
    /**
     * Disable action for contact
     * 
     * @method post
     * @route /user/disable
     */
    public function disableAction()
    {
        parent::disableAction('contact_activate');
    }

    /**
     * Get contact template for a specific contact
     *
     * @method get
     * @route /user/[i:id]/contacttemplate
     */
    public function contactTemplateForContactAction()
    {
        parent::getSimpleRelation('contact_template_id', '\CentreonConfiguration\Models\Contact');
    }
   
    /**
     * Get host notification period for a specific contact
     *
     * @method get
     * @route /user/[i:id]/hostnotifperiod
     */
    public function hostNotifPeriodForContactAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
    }

    /**
     * Get host notification command for a specific contact
     *
     * @method get
     * @route /user/[i:id]/hostnotifcommand
     */
    public function hostNotifCommandForContactAction()
    {
        parent::getRelations(static::$relationMap['contact_hostcommands']);
    }

    /**
     * Get service notification period for a specific contact
     *
     * @method get
     * @route /user/[i:id]/servicenotifperiod
     */
    public function serviceNotifPeriodForContactAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
    }

    /**
     * Get service notification command for a specific contact
     *
     * @method get
     * @route /user/[i:id]/servicenotifcommand
     */
    public function serviceNotifCommandForContactAction()
    {
        parent::getRelations(static::$relationMap['contact_servicecommands']);
    }

    /**
     * Get contact group for a specific contact
     *
     * @method get
     * @route /user/[i:id]/contactgroup
     */
    public function contactGroupForContactAction()
    {
        parent::getRelations(static::$relationMap['contact_contactgroups']);
    }

    /**
     * Get acl group for a specific contact
     *
     * @method get
     * @route /user/[i:id]/aclgroup
     */
    public function aclGroupForContactAction()
    {
        parent::getRelations(static::$relationMap['contact_aclgroups']);
    }
}
