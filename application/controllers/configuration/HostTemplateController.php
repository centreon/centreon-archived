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

namespace Controllers\Configuration;

use \Models\Configuration\Host;

class HostTemplateController extends \Centreon\Core\Controller
{

    /**
     * List hosttemplates
     *
     * @method get
     * @route /configuration/hosttemplate
     */
    public function listAction()
    {
        // Init template
        $di = \Centreon\Core\Di::getDefault();
        $tpl = $di->get('template');

        // Load CssFile
        $tpl->addCss('dataTables.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('dataTables-TableTools.css');

        // Load JsFile
        $tpl->addJs('jquery.dataTables.min.js')
            ->addJs('jquery.dataTables.TableTools.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js');
        
        // Display page
        $tpl->assign('objectName', 'Hosttemplate');
        $tpl->assign('objectAddUrl', '/configuration/hosttemplate/add');
        $tpl->assign('objectListUrl', '/configuration/hosttemplate/list');
        $tpl->display('configuration/list.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /configuration/hosttemplate/formlist
     */
    public function formListAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        
        $hostObj = new Host();
        $filters = array('host_name' => $requestParams['q'].'%', 'host_register' => '0');
        $hostList = $hostObj->getList('host_id, host_name', -1, 0, null, "ASC", $filters, "AND");
        
        $finalHostList = array();
        foreach($hostList as $host) {
            $finalHostList[] = array(
                "id" => $host['host_id'],
                "text" => $host['host_name'],
                "theming" => \Centreon\Repository\HostRepository::getIconImage($host['host_name']).' '.$host['host_name']
            );
        }
        
        $router->response()->json($finalHostList);
    }

    /**
     * 
     * @method get
     * @route /configuration/hosttemplate/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'hosttemplate',
            $this->getParams('get')
            )
        );
    }
    
    /**
     * Create a new hosttemplate
     *
     * @method post
     * @route /configuration/hosttemplate/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a hosttemplate
     *
     *
     * @method put
     * @route /configuration/hosttemplate/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a hosttemplate
     *
     *
     * @method get
     * @route /configuration/hosttemplate/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a hosttemplate
     *
     *
     * @method get
     * @route /configuration/hosttemplate/[i:id]
     */
    public function editAction()
    {
        
    }
}
