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
