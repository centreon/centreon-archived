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
namespace CentreonRealtime\Controllers;

/**
 * Display service monitoring states
 *
 * @author Sylvestre Ho
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class HostController extends \Centreon\Internal\Controller
{
    /**
     * Display services
     *
     * @method get
     * @route /realtime/host
     * @todo work on ajax refresh
     */
    public function displayServicesAction()
    {
        //$tpl = \Centreon\Internal\Di::getDefault()->get('template');

        /* Load css */
        $this->tpl->addCss('dataTables.css')
        	->addCss('dataTables.bootstrap.css')
        	->addCss('dataTables-TableTools.css');

        /* Load js */
        $this->tpl->addJs('jquery.min.js')
        	->addJs('jquery.dataTables.min.js')
        	->addJs('jquery.dataTables.TableTools.min.js')
        	->addJs('bootstrap-dataTables-paging.js')
        	->addJs('jquery.dataTables.columnFilter.js')
        	->addJs('jquery.select2/select2.min.js')
        	->addJs('jquery.validate.min.js')
        	->addJs('additional-methods.min.js');

        /* Datatable */
        $this->tpl->assign('moduleName', 'CentreonRealtime');
        $this->tpl->assign('objectName', 'Host');
        $this->tpl->assign('objectListUrl', '/realtime/host/list');
        $this->tpl->display('file:[CentreonRealtimeModule]console.tpl');
    }

    /**
     * The page structure for display
     *
     * @method get
     * @route /realtime/host/list
     */
    public function listAction()
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        $router->response()->json(
            \Centreon\Internal\Datatable::getDatas(
                'CentreonRealtime',
                'host',
                $this->getParams('get')
            )
        );
    }

    /**
     * Host detail page
     *
     * @method get
     * @route /realtime/host/[i:id]
     */
    public function hostDetailAction()
    {
    }
}
