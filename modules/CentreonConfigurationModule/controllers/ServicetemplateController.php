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

class ServicetemplateController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'ServiceTemplate';
    protected $objectName = 'servicetemplate';
    protected $objectBaseUrl = '/configuration/servicetemplate';
    protected $objectClass = '\CentreonConfiguration\Models\Servicetemplate';
    public static $relationMap = array(
        'service_servicegroups' => '\CentreonConfiguration\Models\Relation\Service\Servicegroup',
        'service_hosts' => '\CentreonConfiguration\Models\Relation\Service\Host',
        'service_categories' => '\CentreonConfiguration\Models\Relation\Service\Servicecategory',
        'service_parents' => '\CentreonConfiguration\Models\Relation\Service\Serviceparent',
        'service_childs' => '\CentreonConfiguration\Models\Relation\Service\Servicechild',
        'service_contacts' => '\CentreonConfiguration\Models\Relation\Service\Contact',
        'service_contactgroups' => '\CentreonConfiguration\Models\Relation\Service\Contactgroup',
        'service_servicetemplates' => '\CentreonConfiguration\Models\Relation\Service\Servicetemplate'
    );

    /**
     * List servicetemplates
     *
     * @method get
     * @route /configuration/servicetemplate
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/servicetemplate/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/servicetemplate/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Create a new servicetemplate
     *
     * @method post
     * @route /configuration/servicetemplate/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a servicetemplate
     *
     *
     * @method put
     * @route /configuration/servicetemplate/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a service template
     *
     *
     * @method get
     * @route /configuration/servicetemplate/add
     */
    public function addAction()
    {
        parent::addAction();
    }
    
    /**
     * Update a service template
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/servicetemplate/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/servicetemplate/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hosts
     *
     * @method POST
     * @route /configuration/servicetemplate/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/servicetemplate/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for servicetemplate
     *
     * @method post
     * @route /configuration/servicetemplate/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Get list of Timeperiods for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/checkperiod
     */
    public function checkPeriodForServiceAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get list of Timeperiods for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/notificationperiod
     */
    public function notificationPeriodForServiceAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get check command for a specific service
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/checkcommand
     */
    public function checkCommandForServiceAction()
    {
        parent::getSimpleRelation('command_command_id', '\CentreonConfiguration\Models\Command');
    }

    /**
     * Get list of Commands for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/eventhandler
     */
    public function eventHandlerForServiceAction()
    {
        parent::getSimpleRelation('command_command_id2', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     * Get list of contacts for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/contact
     */
    public function contactForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_contacts']);
    }
    
    /**
     * Get list of contact groups for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/contactgroup
     */
    public function contactgroupForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_contactgroups']);
    }
    
    /**
     * Get list of contact hosts for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/host
     */
    public function hostForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_hosts']);
    }
    
    /**
     * Get list of service group for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/servicegroup
     */
    public function serviceGroupForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_servicegroups']);
    }

    /**
     * Get list of service categories for a specific service
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/servicecategory
     */
    public function serviceCategoryForServiceAction()
    {
        parent::getRelations(static::$relationMap['service_servicecategories']);
    }
    
    /**
     * Get list of service template for a specific service template
     *
     *
     * @method get
     * @route /configuration/servicetemplate/[i:id]/servicetemplate
     */
    public function serviceTemplateForServiceTemplateAction()
    {
        parent::getSimpleRelation('service_template_model_stm_id', '\CentreonConfiguration\Models\Servicetemplate');
    }
}
