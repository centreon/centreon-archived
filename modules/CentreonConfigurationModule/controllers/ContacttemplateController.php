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

class ContacttemplateController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'Contacttemplate';
    protected $objectName = 'contacttemplate';
    protected $objectBaseUrl = '/configuration/contacttemplate';
    protected $objectClass = '\CentreonConfiguration\Models\Contact';
    public static $relationMap = array(
        'contact_contactgroups' => '\Models\Configuraton\Relation\Contact\Contactgroup',
        'contact_hostcommands' => '\CentreonConfiguration\Models\Relation\Contact\Hostcommand',
        'contact_servicecommands' => '\CentreonConfiguration\Models\Relation\Contact\Servicecommand'
    );

    /**
     * List contact templates
     *
     * @method get
     * @route /configuration/contacttemplate
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/contacttemplate/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * 
     * @method get
     * @route /configuration/contacttemplate/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }
    
    /**
     * Create a new contact template
     *
     * @method post
     * @route /configuration/contacttemplate/create
     */
    public function createAction()
    {
        parent::createAction();   
    }

    /**
     * Update a contact template
     *
     *
     * @method put
     * @route /configuration/contacttemplate/update
     */
    public function updateAction()
    {
        parent::updateAction();    
    }
    
    /**
     * Add a contact template
     *
     *
     * @method get
     * @route /configuration/contacttemplate/add
     */
    public function addAction()
    {
        parent::addAction();
    }
    
    /**
     * Update a contact template
     *
     *
     * @method get
     * @route /configuration/contacttemplate/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/contacttemplate/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/contacttemplate/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate contact templates
     *
     * @method POST
     * @route /configuration/contacttemplate/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/contacttemplate/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for contact template
     *
     * @method post
     * @route /configuration/contacttemplate/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }

    /**
     * Get contact template for a specific contact
     *
     * @method get
     * @route /configuration/contacttemplate/[i:id]/contacttemplate
     */
    public function contactTemplateForContactAction()
    {
        parent::getSimpleRelation('contact_template_id', '\CentreonConfiguration\Models\Contact');
    }
   
    /**
     * Get host notification period for a specific contact template
     *
     * @method get
     * @route /configuration/contacttemplate/[i:id]/hostnotifperiod
     */
    public function hostNotifPeriodForContactAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
    }

    /**
     * Get host notification command for a specific contact template
     *
     * @method get
     * @route /configuration/contacttemplate/[i:id]/hostnotifcommand
     */
    public function hostNotifCommandForContactAction()
    {
        parent::getRelations(static::$relationMap['contact_hostcommands']);
    }

    /**
     * Get service notification period for a specific contact template
     *
     * @method get
     * @route /configuration/contacttemplate/[i:id]/servicenotifperiod
     */
    public function serviceNotifPeriodForContactAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
    }

    /**
     * Get service notification command for a specific contact template
     *
     * @method get
     * @route /configuration/contacttemplate/[i:id]/servicenotifcommand
     */
    public function serviceNotifCommandForContactAction()
    {
        parent::getRelations(static::$relationMap['contact_servicecommands']);
    }

    /**
     * Get contact group for a specific contact template
     *
     * @method get
     * @route /configuration/contacttemplate/[i:id]/contactgroup
     */
    public function contactGroupForContactAction()
    {
        parent::getRelations(static::$relationMap['contact_contactgroups']);
    }
}
