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
 * For more information : poller@centreon.com
 *
 */

namespace CentreonConfiguration\Controllers;

use \Centreon\Internal\Di;

use \CentreonConfiguration\Models\Poller as PollerModel;
use \CentreonConfiguration\Repository\PollerRepository;
use CentreonConfiguration\Internal\PollerTemplateManager;

class PollerController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'Poller';
    protected $objectName = 'poller';
    protected $objectBaseUrl = '/configuration/poller';
    protected $datatableObject = '\CentreonConfiguration\Internal\PollerDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Poller';
    protected $repository = '\CentreonConfiguration\Repository\PollerRepository';
    public static $relationMap = array();

    /**
     * List users
     *
     * @method get
     * @route /configuration/poller
     */
    public function listAction()
    {
        $tpl = Di::getDefault()->get('template');
        $tpl->addJs('poller-generate.js', 'bottom', 'centreon-configuration');
        parent::listAction();
    }
    
    /**
     * 
     * @method get
     * @route /configuration/poller/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }
    
    /**
     * 
     * @method get
     * @route /configuration/poller/formlist
     */
    public function formListAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        
        $pollerObj = new PollerModel();
        $filters = array('name' => $requestParams['q'].'%');
        $pollerList = $pollerObj->getList('id, name', -1, 0, null, "ASC", $filters, "AND");
        
        $finalPollerList = array();
        foreach ($pollerList as $poller) {
            $finalPollerList[] = array(
                "id" => $poller['id'],
                "text" => $poller['name']
            );
        }
        
        $router->response()->json($finalPollerList);
    }
    
    /**
     * Update a poller
     *
     * @method post
     * @route /configuration/poller/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a poller
     *
     * @method get
     * @route /configuration/poller/add
     */
    public function addAction()
    {
        $tpl = Di::getDefault()->get('template');
        $tpl->assign('validateUrl', '/configuration/poller/add');
        /* Prepare default information */
        $form = new \Centreon\Internal\Form("add_poller");
        $form->add(array(
            'type' => 'text',
            'label' => 'Poller name',
            'name' => 'poller_name',
            'mandatory' => true
        ));
        $form->add(array(
            'type' => 'text',
            'label' => 'IP Address',
            'name' => 'ip_address',
            'mandatory' => true
        ));
        $selectParams = array(
            'object_type' => 'object',
            'defaultValuesRoute' => '/configuration/poller/templates',
            'listValuesRoute' => '/configuration/poller/[i:id]/template',
            'multiple' => false
        );
        $form->add(array(
            'type' => 'templatepoller',
            'label' => 'Poller Template',
            'name' => 'poller_tmpl',
            'mandatory' => true,
            'attributes' => json_encode($selectParams)
        ));
        $tpl->assign('form', $form->toSmarty());
        $tpl->display('file:[CentreonConfigurationModule]addPoller.tpl');
    }

    /**
     * Create a new poller
     *
     * @method post
     * @route /configuration/poller/add
     */
    public function createAction()
    {
        parent::createAction();
    }

    /**
     * Update a poller
     *
     * @method get
     * @route /configuration/poller/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }
    
    /**
     * Duplicate a poller
     *
     * @method post
     * @route /configuration/poller/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/poller/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }
    
    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/poller/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/poller/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Delete action for poller
     *
     * @method post
     * @route /configuration/poller/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }

    /**
     * Display wizard for applying configuration
     *
     * @method get
     * @route /configuration/poller/applycfg
     */
    public function applyConfAction()
    {
        $tpl = Di::getDefault()->get('template');
        $params = $this->getParams();
        $tpl->display('file:[CentreonConfigurationModule]applycfg.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /configuration/poller/templates
     */
    public function getPollerTemplatesAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        PollerRepository::getPollerTemplates();
        $data = $di->get('pollerTemplate');
        $returnData = array();
        foreach ($data as $id => $file) {
            $returnData[] = array(
                'id' => $id,
                'text' => ucfirst($id)
            );
        }
        $router->response()->json($returnData);
    }

    /**
     * Get default template for a poller
     *
     * @method get
     * @route /configuration/poller/[i:id]/template
     */
    public function getPollerDefaultTemplateAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $router->response()->json(array());
    }
    
    /**
     * Get default template for a poller
     *
     * @method post
     * @route /configuration/poller/templates/form
     */
    public function getFormForTemplateAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $params = $this->getParams();
        
        PollerRepository::getPollerTemplates();
        $pollerTemplateList = $di->get('pollerTemplate');
        
        $myFile = $pollerTemplateList[$params['name']];
        $tplManager = new PollerTemplateManager($myFile);
        
        $router->response()->json($tplManager->generateFormForTemplate());
        
    }
}
