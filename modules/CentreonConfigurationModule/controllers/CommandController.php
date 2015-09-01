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
use CentreonConfiguration\Models\Command;
use Centreon\Controllers\FormController;

class CommandController extends FormController
{
    protected $objectDisplayName = 'Command';
    public static $objectName = 'command';
    protected $objectBaseUrl = '/centreon-configuration/command';
    protected $objectClass = '\CentreonConfiguration\Models\Command';
    protected $datatableObject = '\CentreonConfiguration\Internal\CommandDatatable';
    protected $repository = '\CentreonConfiguration\Repository\CommandRepository';   
    public static $relationMap = array();

    /**
     * Connector for a specific command
     *
     * @method get
     * @route /command/[i:id]/connector
     */
    public function connectorForCommandAction()
    {
        parent::getSimpleRelation('connector_id', '\CentreonConfiguration\Models\Connector');
    }

    /**
     * Delete action for command
     *
     * @method get
     * @route /command/[i:id]/arguments
     */
    public function getArgumentsAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        $connObj = new Command();
        
        $currentCommandLine = $connObj->getParameters($requestParam['id'], array('command_line'));
        
        $myArguments = array();
        $myArguments[] = array(
            'name' => 'Test',
            "value" => 'My Value',
            'example' => 'My Example'
        );
        $myArguments[] = array(
            'name' => 'Test',
            "value" => 'My Value',
            'example' => 'My Example'
        );
        $myArguments[] = array(
            'name' => 'Test',
            "value" => 'My Value',
            'example' => 'My Example'
        );
        
        $router->response()->code(200)->json($myArguments);
    }
}
