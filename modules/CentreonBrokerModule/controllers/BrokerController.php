<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
