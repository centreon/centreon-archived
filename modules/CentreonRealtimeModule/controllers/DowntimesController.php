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
        $this->tpl->assign('objectDisplayName', 'Downtimes');
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
