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

namespace CentreonBroker\Controllers;

use Centreon\Controllers\FormController;
use CentreonBroker\Repository\BrokerFormRepository;

/**
 * Description of BrokerController
 *
 * @author lionel
 */
class BrokerController extends FormController
{
    protected $objectDisplayName = 'Broker';
    protected $objectName = 'broker';
    protected $objectBaseUrl = '/centreon-broker/broker';
    protected $objectClass = '\CentreonBroker\Models\Broker';
    protected $datatableObject = '\CentreonBroker\Internal\BrokerDatatable';
    protected $repository = '\CentreonBroker\Repository\BrokerFormRepository';
    public static $relationMap = array();
    
    /**
     * List brokers
     *
     * @method get
     * @route /centreon-broker/broker
     */
    public function listAction()
    {
        parent::listAction();
    }

    /**
     * 
     * @method get
     * @route /centreon-broker/broker/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /centreon-broker/broker/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * Update a broker
     *
     *
     * @method post
     * @route /centreon-broker/broker/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a broker
     *
     *
     * @method post
     * @route /centreon-broker/broker/add
     */
    public function createAction()
    {
        parent::createAction();
    }
    
    /**
     * Update a broker
     *
     *
     * @method get
     * @route /centreon-broker/broker/[i:id]
     */
    public function editAction()
    {
        $requestParam = $this->getParams('named');
        $objectFormUpdateUrl = $this->objectBaseUrl.'/update';
        
        $formModeUrl = $this->router->getPathFor(
            $this->objectBaseUrl.'/[i:id]',
            array(
                'id' => $requestParam['id']
            )
        );
        
        
        $this->tpl->assign('pageTitle', $this->objectDisplayName);
        $this->tpl->assign('form', BrokerFormRepository::getFormForPoller($requestParam['id']));
        $this->tpl->assign('advanced', $requestParam['advanced']);
        $this->tpl->assign('formModeUrl', $formModeUrl);
        $this->tpl->assign('formName', 'broker_form');
        $this->tpl->assign('validateUrl', $objectFormUpdateUrl);
        $this->tpl->display('file:[CentreonConfigurationModule]edit.tpl');
    }
    
    /**
     * Add a broker
     *
     * @method get
     * @route /centreon-broker/broker/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/centreon-broker/broker/add');
        parent::addAction();
    }

    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /centreon-broker/broker/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /centreon-broker/broker/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hosts
     *
     * @method POST
     * @route /centreon-broker/broker/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /centreon-broker/broker/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for hosttemplate
     *
     * @method post
     * @route /centreon-broker/broker/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
}
