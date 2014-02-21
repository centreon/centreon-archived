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

use Models\Configuration\Hostgroup;

class HostgroupController extends \Centreon\Core\Controller
{

    /**
     * List hostgroups
     *
     * @method get
     * @route /configuration/hostgroup
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
        $tpl->assign('objectName', 'Hostgroup');
        $tpl->assign('objectAddUrl', '/configuration/hostgroup/add');
        $tpl->assign('objectListUrl', '/configuration/hostgroup/list');
        $tpl->display('configuration/list.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /configuration/hostgroup/formlist
     */
    public function formListAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $requestParams = $this->getParams('get');
        
        $hostgroupObj = new Hostgroup();
        $filters = array('hg_name' => $requestParams['q'].'%');
        $hostgroupList = $hostgroupObj->getList('hg_id, hg_name', -1, 0, null, "ASC", $filters, "AND");
        
        $finalHostgroupList = array();
        foreach($hostgroupList as $hostgroup) {
            $finalHostgroupList[] = array(
                "id" => $hostgroup['hg_id'],
                "text" => $hostgroup['hg_name']
            );
        }
        
        $router->response()->json($finalHostgroupList);
    }

    /**
     * 
     * @method get
     * @route /configuration/hostgroup/list
     */
    public function datatableAction()
    {
        $di = \Centreon\Core\Di::getDefault();
        $router = $di->get('router');
        
        $router->response()->json(\Centreon\Core\Datatable::getDatas(
            'hostgroup',
            $this->getParams('get')
            )
        );
    }
    
    /**
     * Create a new hostgroup
     *
     * @method post
     * @route /configuration/hostgroup/create
     */
    public function createAction()
    {
        
    }

    /**
     * Update a hostgroup
     *
     *
     * @method put
     * @route /configuration/hostgroup/update
     */
    public function updateAction()
    {
        
    }
    
    /**
     * Add a hostgroup
     *
     *
     * @method get
     * @route /configuration/hostgroup/add
     */
    public function addAction()
    {
        
    }
    
    /**
     * Update a hostgroup
     *
     *
     * @method get
     * @route /configuration/hostgroup/[i:id]
     */
    public function editAction()
    {
        
    }
}
