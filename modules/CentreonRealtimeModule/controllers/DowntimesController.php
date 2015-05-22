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

use Centreon\Internal\Utils\Status;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Di;
use Centreon\Internal\Controller;

/**
 * Display service monitoring states
 *
 * @author kevin duret <kduret@centreon.com>
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class DowntimesController extends Controller
{
    /**
     *
     * @var type 
     */
    protected $datatableObject = '\CentreonRealtime\Internal\DowntimesDatatable';
    
    /**
     *
     * @var type 
     */
    protected $objectClass = '\CentreonRealtime\Models\Downtimes';

    /**
     *
     * @param type $request
     */
    public function __construct($request)
    {
        parent::__construct($request);
    }    
    
    /**
     * Display services
     *
     * @method get
     * @route /downtimes
     * @todo work on ajax refresh
     */
    public function displayDowntimesAction()
    {
        $router = Di::getDefault()->get('router');
        /* Load css */
        $this->tpl->addCss('dataTables.tableTools.min.css')
            ->addCss('dataTables.colVis.min.css')
            ->addCss('dataTables.colReorder.min.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load js */
        $this->tpl->addJs('jquery.min.js')
            ->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
            ->addJs('dataTables.colReorder.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validation/jquery.validate.min.js')
            ->addJs('jquery.validation/additional-methods.min.js')
            ->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.search.js')
            ->addJs('bootstrap3-typeahead.js')
            ->addJs('centreon.search.js')
            ->addJs('centreon-wizard.js');

        /* Datatable */
        $this->tpl->assign('moduleName', 'CentreonRealtime');
        $this->tpl->assign('datatableObject', $this->datatableObject);
        $this->tpl->assign('objectName', 'Downtimes');
        $this->tpl->assign('consoleType', 0); // host console
        $this->tpl->assign('objectListUrl', '/centreon-realtime/downtimes/list');
        
        $actions = array();
        /*$actions[] = array(
            'group' => _('Downtimes'),
            'actions' => HostdetailRepository::getMonitoringActions()
        );*/
        $this->tpl->assign('actions', $actions);
        
        $urls = array();

        $this->tpl->append('jsUrl', $urls, true);

        $this->tpl->display('file:[CentreonMainModule]list.tpl');
    }

    /**
     * The page structure for display
     *
     * @method get
     * @route /downtimes/list
     */
    public function listAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $this->tpl->addJs('moment-with-locales.js')
            ->addJs('moment-timezone-with-data.min.js');
                
        $myDatatable = new $this->datatableObject($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
        
        $router->response()->json($myDataForDatatable);
    }

    /**
     * Downtime tooltip
     *
     * @method get
     * @route /downtimes/[i:id]/tooltip
     */
    public function displayTooltipAction()
    {
        $this->tpl->display('file:[CentreonRealtimeModule]downtime_tooltip.tpl');
    }
}
