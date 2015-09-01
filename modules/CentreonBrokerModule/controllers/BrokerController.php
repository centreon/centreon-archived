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

namespace CentreonBroker\Controllers;

use Centreon\Internal\Di;
use Centreon\Internal\Form;
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
    public static $objectName = 'broker';
    protected $objectBaseUrl = '';
    protected $objectClass = '\CentreonBroker\Models\Broker';
    protected $datatableObject = '\CentreonBroker\Internal\BrokerDatatable';
    protected $repository = '\CentreonBroker\Repository\BrokerFormRepository';
    public static $relationMap = array();
    
    /**
     * Update a broker
     *
     *
     * @method post
     * @route /broker/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        $updateSuccessful = true;
        $updateErrorMessage = '';
        
        try {
            $pollerId = $givenParameters['poller_id'];
            unset($givenParameters['poller_id']);
            \CentreonBroker\Repository\BrokerRepository::save($pollerId, $givenParameters);
        } catch (Exception $e) {
            $updateSuccessful = false;
            $updateErrorMessage = $e->getMessage();
            var_dump($e);
        }
        
        $this->router = Di::getDefault()->get('router');
        if ($updateSuccessful) {
            unset($_SESSION['form_token']);
            unset($_SESSION['form_token_time']);
            $this->router->response()->json(array('success' => true));
        } else {
            $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
    
    /**
     * Update a broker
     *
     *
     * @method get
     * @route /[i:id]
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
        $this->tpl->assign('formRedirect', true);
        $this->tpl->assign('formRedirectRoute', '/centreon-configuration/poller/' . $requestParam['id']);
        $this->tpl->assign('formModeUrl', $formModeUrl);
        $this->tpl->assign('formName', 'broker_full_form');
        $this->tpl->assign('validateUrl', $objectFormUpdateUrl);
        $this->tpl->display('file:[CentreonConfigurationModule]edit.tpl');
    }
}
