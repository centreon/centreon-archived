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

namespace CentreonEngine\Controllers;

use Centreon\Internal\Form;
use Centreon\Internal\Di;
use Centreon\Controllers\FormController;

/**
 * Description of EngineController
 *
 * @author lionel
 */
class EngineController extends FormController
{
    public static $moduleShortName = 'centreon-engine';
    protected $objectDisplayName = 'Engine';
    public static $objectName = 'engine';
    protected $objectBaseUrl = '/centreon-engine';
    protected $objectClass = '\CentreonEngine\Models\Engine';
    protected $datatableObject = '\CentreonEngine\Internal\EngineDatatable';
    protected $repository = '\CentreonEngine\Repository\EngineRepository';
    public static $moduleName = 'CentreonEngine';
    public static $relationMap = array();
    
    /**
     * Update a engine
     *
     *
     * @method post
     * @route /update
     */
    public function updateAction()
    {
        $givenParameters = clone $this->getParams('post');

        try {
            $repository = $this->repository;
            $repository::save($givenParameters['object_id'], $givenParameters, 'form', $this->getUri());
            
            unset($_SESSION['form_token']);
            unset($_SESSION['form_token_time']);
            $this->router->response()->json(array('success' => true));
        } catch (\Centreon\Internal\Exception $e) {
            $updateErrorMessage = $e->getMessage();
            $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
    
    /**
     * Update a engine
     *
     *
     * @method get
     * @route /[i:id]
     */
    public function editAction()
    {
        $requestParam = $this->getParams('named');
        $additionnalParams = array(
            'formRedirect'=> true,
            'formRedirectRoute' => '/centreon-configuration/poller/' . $requestParam['id']
        );
        parent::editAction($additionnalParams);
    }
    
    /**
     *
     * @method get
     * @route /engine/[i:id]/globalhosteventhandler
     */
    public function globalHostEventHandlerForEngineAction()
    {
        parent::getSimpleRelation('global_host_event_handler', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     *
     * @method get
     * @route /engine/[i:id]/globalserviceeventhandler
     */
    public function globalServiceEventHandlerForEngineAction()
    {
        parent::getSimpleRelation('global_service_event_handler', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     *
     * @method get
     * @route /engine/[i:id]/ochpcommand
     */
    public function ochpCommandForEngineAction()
    {
        parent::getSimpleRelation('ochp_command', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     *
     * @method get
     * @route /engine/[i:id]/ocspcommand
     */
    public function ocspCommandForEngineAction()
    {
        parent::getSimpleRelation('ocsp_command', '\CentreonConfiguration\Models\Command');
    }
}
