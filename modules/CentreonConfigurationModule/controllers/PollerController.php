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

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;

use CentreonConfiguration\Models\Poller as PollerModel;
use CentreonConfiguration\Models\Node as NodeModel;
use CentreonConfiguration\Repository\PollerRepository;
use CentreonConfiguration\Internal\PollerTemplateManager;
use Centreon\Internal\Form;
use Centreon\Internal\Exception;
use Centreon\Controllers\FormController;
use Centreon\Internal\Module\Informations;

class PollerController extends FormController
{
    protected $objectDisplayName = 'Poller';
    public static $objectName = 'poller';
    protected $objectBaseUrl = '/centreon-configuration/poller';
    protected $datatableObject = '\CentreonConfiguration\Internal\PollerDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Poller';
    protected $repository = '\CentreonConfiguration\Repository\PollerRepository';
    public static $relationMap = array();

    public static $isDisableable = true;
    public static $enableDisableFieldName = 'enable';

    /**
     * List pollerss
     *
     * @method get
     * @route /poller
     */
    public function listAction()
    {
        $tpl = Di::getDefault()->get('template');
        $tpl->addJs('poller-generate.js', 'bottom', 'centreon-configuration');
        $tpl->addJs('poller-template.js', 'bottom', 'centreon-configuration');
        parent::listAction();
    }
    
    /**
     * Update a poller
     *
     * @method post
     * @route /poller/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a poller
     *
     * @method get
     * @route /poller/add
     */
    public function addAction()
    {
        parent::addAction();
    }

    /**
     * Create a new poller
     *
     * @method post
     * @route /poller/add
     */
    public function createAction()
    {
        parent::createAction();
    }

    /**
     * Update a poller
     *
     * @method get
     * @route /poller/[i:id]
     */
    public function editAction()
    {
        $params = $this->getParams();
        $poller = PollerModel::get($params['id']);
        $node = NodeModel::get($poller['node_id']);

        $this->tpl->addJs('poller-template.js', 'bottom', 'centreon-configuration');

        parent::editAction(array(), array('ip_address' => $node['ip_address']));
    }

    /**
     * Display wizard for applying configuration
     *
     * @method get
     * @route /poller/applycfg
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
     * @route /poller/templates
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
     * @route /poller/[i:id]/template
     */
    public function getPollerDefaultTemplateAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $params = $this->getParams();
        $returnData = array();
        
        if ($params['id'] == 0) {
            PollerRepository::getPollerTemplates();
            $defaultPoller = array_slice($di->get('pollerTemplate'), 0, 1, true);
            $p = key($defaultPoller);
            
            $returnData['id'] = $p;
            $returnData['text'] = ucfirst($p);
        } else {
            $poller = PollerModel::get($params['id']);
            $returnData['id'] = $poller['tmpl_name'];
            $returnData['text'] = ucfirst($poller['tmpl_name']);
        }
        $router->response()->json($returnData);
    }
    
    /**
     * Get default template for a poller
     *
     * @method post
     * @route /poller/[i:id]/templates/form
     */
    public function getFormForTemplateAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $params = $this->getParams();
        
        PollerRepository::getPollerTemplates();
        $pollerTemplateList = $di->get('pollerTemplate');
        
        $myLiteTemplate = unserialize($pollerTemplateList[$params['name']]);
        $myTemplate = $myLiteTemplate->toFullTemplate();

        $pollerId = null;
        if (isset($params['id'])) {
            $pollerId = $params['id'];
        }
        
        $router->response()->json($myTemplate->genForm($pollerId));
    }
}
