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
 */

namespace CentreonBam\Controllers;

use Centreon\Internal\Utils\Status;
use Centreon\Internal\Utils\Datetime;
use CentreonBam\Repository\BusinessActivityRepository as BaConfRepository;
use Centreon\Internal\Di;
use Centreon\Internal\Controller;

/**
 * Display service monitoring states
 *
 * @author Sylvestre Ho
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class BusinessActivityRealtimeController extends Controller
{
    protected $datatableObject = '\CentreonBam\Internal\BusinessActivityRealtimeDatatable';
    
    protected $objectClass = '\CentreonBam\Models\BusinessActivityRealtime';

    /**
     *
     * @param type $request
     */
    public function __construct($request)
    {
        $confRepository = '\CentreonBam\Repository\BusinessActivityRepository';
        $confRepository::setObjectClass('\CentreonBam\Models\BusinessActivity');
        parent::__construct($request);
    }
    
    /**
     * Display services
     *
     * @method get
     * @route /businessactivity/realtime
     * @todo work on ajax refresh
     */
    public function displayHostsAction()
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
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addJs('bootstrap3-typeahead.js')
            ->addJs('centreon.search.js')
            ->addJs('centreon-wizard.js');

        
        
        /* Datatable */
        $this->tpl->assign('moduleName', 'CentreonBam');
        $this->tpl->assign('datatableObject', $this->datatableObject);
        $this->tpl->assign('objectName', 'BusinessActivity');
        $this->tpl->assign('objectDisplayName', 'Business Activity');
        //$this->tpl->assign('consoleType', 0); // host console
        $this->tpl->assign('objectListUrl', '/centreon-bam/businessactivity/realtime/list');
        /*
        $actions = array();
        
        $tShis->tpl->assign('actions', $actions);
        
         */

        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete'),
                'getallGlobal' => $router->getPathFor('/centreon-administration/tag/all'),
                'getallPerso' => $router->getPathFor('/centreon-administration/tag/allPerso'),
                'addMassive' => $router->getPathFor('/centreon-administration/tag/addMassive')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);

        $this->tpl->display('file:[CentreonMainModule]list.tpl');
    }

    /**
     * The page structure for display
     *
     * @method get
     * @route /businessactivity/realtime/list
     */
    public function listAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $myDatatable = new $this->datatableObject($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
        
        $router->response()->json($myDataForDatatable);
    }

    /**
     * Business activity tooltip
     *
     * @method get
     * @route /businessactivity/realtime/[i:id]/tooltip
     */
    public function displayTooltipAction()
    {
        $this->tpl->display('file:[CentreonBamModule]ba_tooltip.tpl');
    }

    /**
     * Business activity detail page
     *
     * @method get
     * @route /businessactivity/realtime/[i:id]
     */
    public function businessActivityDetailAction()
    {
        $tpl = Di::getDefault()->get('template');

        $tpl->assign('moduleName', 'CentreonBam');
        $tpl->assign('objectName', 'BusinessActivity');

        $tpl->display('file:[CentreonBamModule]ba_detail.tpl');
    }
}
