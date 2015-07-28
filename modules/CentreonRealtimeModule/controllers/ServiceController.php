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

use CentreonRealtime\Repository\ServicedetailRepository;
use CentreonRealtime\Repository\HostdetailRepository;
use Centreon\Internal\Utils\Status;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Hook;
use Centreon\Internal\Controller;
use Centreon\Internal\Di;
use CentreonConfiguration\Repository\ServiceRepository as ServiceRepositoryConfig;
use CentreonRealtime\Repository\IncidentsRepository;

/**
 * Display service monitoring states
 *
 * @author Sylvestre Ho
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class ServiceController extends Controller
{
    protected $datatableObject = '\CentreonRealtime\Internal\ServiceDatatable';
    
    protected $objectClass = '\CentreonRealtime\Models\Service';
    
    /**
     * 
     * @param type $request
     */
    public function __construct($request)
    {
        $confRepository = '\CentreonConfiguration\Repository\ServiceRepository';
        $confRepository::setObjectClass('\CentreonConfiguration\Models\Service');
        parent::__construct($request);
    }
    
    /**
     * Display services
     *
     * @method get
     * @route /service
     * @todo work on ajax refresh
     */
    public function displayServicesAction()
    {
        $tpl = Di::getDefault()->get('template');
        $router = Di::getDefault()->get('router');

        /* Load css */
        $tpl->addCss('dataTables.tableTools.min.css')
            ->addCss('jquery.fileupload.css')
            ->addCss('dataTables.colVis.min.css')
            ->addCss('dataTables.colReorder.min.css')
            ->addCss('centreon.tag.css', 'centreon-administration')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load js */
        $tpl->addJs('jquery.min.js')
            ->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
            ->addJs('dataTables.colReorder.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('dataTables.bootstrap.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validation/jquery.validate.min.js')
            ->addJs('jquery.validation/additional-methods.min.js')
            ->addJs('jquery.qtip.min.js')
            ->addJs('moment-with-locales.js')
            ->addJs('hogan-3.0.0.min.js')
            ->addJs('daterangepicker.js')
            ->addJs('bootstrap3-typeahead.js')
            ->addJs('centreon.search.js')
            ->addJs('centreon.overlay.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addJs('moment-timezone-with-data.min.js')
            ->addJs('centreon-wizard.js');

        /* Datatable */
        $tpl->assign('moduleName', 'CentreonRealtime');
        $tpl->assign('datatableObject', $this->datatableObject);
        $tpl->assign('objectDisplayName', 'Service');
        $tpl->assign('objectName', 'Service');
        $tpl->assign('objectListUrl', '/centreon-realtime/service/list');

        $actions = array();
        $actions[] = array(
            'group' => _('Services'),
            'actions' => ServicedetailRepository::getMonitoringActions()
        );
        $actions[] = array(
            'group' => _('Hosts'),
            'actions' => HostdetailRepository::getMonitoringActions()
        );
        $tpl->assign('actions', $actions);

        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete'),
                'getallGlobal' => $router->getPathFor('/centreon-administration/tag/all'),
                'getallPerso' => $router->getPathFor('/centreon-administration/tag/allPerso'),
                'addMassive' => $router->getPathFor('/centreon-administration/tag/addMassive')
            )
        );
        $tpl->append('jsUrl', $urls, true);
        /* Add javascript and css file for hooks */
        Hook::addStaticFile('displaySvcTooltipGraph');

        $tpl->display('file:[CentreonMainModule]list.tpl');
    }

    /**
     * The page structure for display
     *
     * @method get
     * @route /service/list
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
     * Service detail page
     *
     * @method get
     * @route /service/[i:hid]/[i:id]
     */
    public function serviceDetailAction()
    {
        $tpl = Di::getDefault()->get('template');
        
        $tpl->assign('moduleName', 'CentreonRealtime');
        $tpl->assign('objectName', 'Service');

        $tpl->display('file:[CentreonRealtimeModule]service_detail.tpl');
    }

    /**
     * Service tooltip
     *
     * @method get
     * @route /service/[i:hid]/[i:sid]/tooltip
     */
    public function serviceTooltipAction()
    {
        $params = $this->getParams();
        $rawdata = ServicedetailRepository::getRealtimeData($params['sid']);
        if (isset($rawdata[0])) {
            $data = $this->transformRawData($rawdata[0]);
            $this->tpl->assign('title', $rawdata[0]['host_name'] . ' - ' . $rawdata[0]['service_description']);
	    $this->tpl->assign('host', $rawdata[0]['host_name']);
	    $this->tpl->assign('svc', $rawdata[0]['service_description']);
            $this->tpl->assign('state', $rawdata[0]['state']);
            $this->tpl->assign('data', $data);
        } else {
            $this->tpl->assign('error', sprintf(_('No data found for service id:%s'), $params['id']));
        }
        $this->tpl->assign('params', array('host_id' => $params['hid'], 'svc_id' => $params['sid']));
        $this->tpl->display('file:[CentreonRealtimeModule]service_tooltip.tpl');
    }
    
    
    /**
     * Service slider default menu
     *
     * @method get
     * @route /service/snapshotslide/[i:id]
     */
    public function snapshotslideAction()
    {
        $router = Di::getDefault()->get('router');
        $params = $this->getParams();
        $return = array();
        $service = ServicedetailRepository::getRealtimeData($params['id']);
        $service = $service[0];
        $serviceTemp['name'] = $service['host_name'] . ' ' . $service['service_description'];
        if(!empty($service['acknowledged'])){
            $serviceTemp['acknowledged'] = 1;
        }else{
            $serviceTemp['acknowledged'] = 0;
        }
        
        if(isset($service['state_type']) && $service['state_type'] == "1"){
            $serviceTemp['stateHardSoft'] = "Hard";
        }else if(isset($service['state_type']) && $service['state_type'] == "0"){
            $serviceTemp['stateHardSoft'] = "Soft";
        }
        
        $serviceTemp['last_check'] = Datetime::humanReadable(
                    time() - $service['last_check'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
        $serviceTemp['next_check'] = Datetime::humanReadable(
                    time() - $service['next_check'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
        $serviceTemp['check_period'] = $service['check_period'];
        $serviceTemp['retry_interval'] = $service['retry_interval'];
        $serviceTemp['check_interval'] = $service['check_interval'];
        $serviceTemp['max_check_attempts'] = $service['max_check_attempts'];
        $serviceTemp['active_checks'] = $service['active_checks'];
        $serviceTemp['passive_checks'] = $service['passive_checks'];
        
        
        
        
        
        
        $serviceTemp['icon'] = ServiceRepositoryConfig::getIconImage($params['id']);
        $serviceTemp['url'] = $router->getPathFor('/centreon-realtime/service/'.$params['id']);
        $return['service'] = $serviceTemp;
        $router->response()->json($return);
    }
    
    /**
     * Service slider default menu
     *
     * @method get
     * @route /service/tagslide/[i:id]
     */
    public function slideTagsAction(){
        $router = Di::getDefault()->get('router');
        $params = $this->getParams();
        $tags = \CentreonAdministration\Repository\TagsRepository::getList('service', $params['id']);
        $inheritedTag = array();
        $directTag = array();
        foreach($tags as $tag){
            if(!empty($tags['template_id'])){
                $inheritedTag[] = $tag;
            }else{
                $directTag[] = $tag;
            }
        }
        $return['directTags'] = $directTag;
        $return['inheritedTags'] = $inheritedTag;
        $router->response()->json($return);
        
    }
    
    
    /**
     * Service slider default menu
     *
     * @method get
     * @route /service/incidentslide/[i:id]
     */
    public function slideIncidentsAction(){
        $router = Di::getDefault()->get('router');
        $params = $this->getParams();
        $return = array();
        $incidents = IncidentsRepository::getIncidents(null,'DESC',null,array('i.service_id'=>$params['id']));
        foreach($incidents as $key=>$incident){
            $childIncidents = IncidentsRepository::getChildren($incident['issue_id']);
            foreach($childIncidents as $childIncident){
                $incidents[$key]['child_incidents'][] = $childIncident;
            }
        }
        $return['incidents'] = $incidents;
        $router->response()->json($return);
    }
    
    
    /**
     * Service slider default menu
     *
     * @method get
     * @route /service/slidecommand/[i:id]
     */
    public function slideCommandAction(){
        
        $router = Di::getDefault()->get('router');
        $params = $this->getParams();
        $return = array();
        $service = ServicedetailRepository::getRealtimeData($params['id']);
        $serviceTemp = $service[0];
        $return['command'] = $serviceTemp['command_line'];
        $router->response()->json($return);
    }
    
    
    /**
     * Service slider default menu
     *
     * @method get
     * @route /service/slideoutput/[i:id]
     */
    public function slideOutputAction(){
        
        $router = Di::getDefault()->get('router');
        $params = $this->getParams();
        $return = array();
        $service = ServicedetailRepository::getRealtimeData($params['id']);
        $serviceTemp = $service[0];
        $return['output'] = $serviceTemp['output'];
        $router->response()->json($return);
    }
    
    
    
    /**
     * Service slider default menu
     *
     * @method get
     * @route /service/slideschelduded/[i:id]
     */
    public function slideScheldudedInfosAction(){
        
        $router = Di::getDefault()->get('router');
        $params = $this->getParams();
        $return = array();
        $service = ServicedetailRepository::getRealtimeData($params['id']);
        $serviceTemp = $service[0];
        $return['schelduded']['name'] = $serviceTemp['instance_name'];
        $return['schelduded']['execution_time'] = Datetime::humanReadable(
                    time() - $serviceTemp['last_command_check'],
                    Datetime::PRECISION_FORMAT,
                    2
                );
        $return['schelduded']['latency'] = $serviceTemp['latency'];
        $router->response()->json($return);
    }
    
    
    

    /**
     * Display graph in a tooltip
     *
     * @method get
     * @route /service/[i:hid]/[i:sid]/graph
     */
    public function serviceTooltipGraphAction()
    {
        $params = $this->getParams();
        $this->tpl->assign(
            'params',
            array('svc_id' => $params['sid'])
        );
        $this->tpl->display('file:[CentreonRealtimeModule]service_graph_tooltip.tpl');
    }

    /**
     * Transform raw data
     *
     * @param array $rawdata
     * @return array
     */
    protected function transformRawData($rawdata)
    {
        $data = array();

        /* Instance */
        $data[] = array(
            'label' => _('Poller'),
            'value' => $rawdata['instance_name']
        );

        /* State */
        $data[] = array(
            'label' => _('State'),
            'value' => Status::numToString(
                $rawdata['state'],
                Status::TYPE_SERVICE,
                true
            ) . " (" . ($rawdata['state_type'] ? "HARD" : "SOFT") . ")"
        );

        /* Command line */
        $data[] = array(
            'label' => _('Command line'),
            'value' => chunk_split($rawdata['command_line'], 80, "<br/>")
        );

        /* Output */
        $data[] = array(
            'label' => _('Output'),
            'value' => $rawdata['output']
        );

        /* Perfdata */
        if ($rawdata['perfdata']) {
            $perfdata = explode(' ', $rawdata['perfdata']);
            foreach ($perfdata as &$perf) {
                $perf = preg_replace(
                    "/([a-zA-Z0-9_-]+)(=)(.*)/i",
                    '<span class="btn btn-xs btn-info perfdata">$1</span>
                    <span class="btn btn-xs btn-default perfdata">$3</span>',
                    $perf
                );
            }
            $data[] = array(
                'label' => _('Performance'),
                'value' => implode("<br />", $perfdata)
            );
        }

        /* Acknowledged */
        $data[] = array(
            'label' => _('Acknowledged'),
            'value' => $rawdata['acknowledged'] ? _('Yes') : _('No')
        );

        /* Downtime */
        $data[] = array(
            'label' => _('In downtime'),
            'value' => $rawdata['scheduled_downtime_depth'] ? _('Yes') : _('No')
        );

        /* Latency */
        $data[] = array(
            'label' => _('Latency'),
            'value' => $rawdata['latency'] . ' s'
        );

        /* Check period */
        $data[] = array(
            'label' => _('Check period'),
            'value' => $rawdata['check_period']
        );

        /* Last check */
        $data[] = array(
            'label' => _('Last check'),
            'value' => $rawdata['last_check']
        );

        /* Next check */
        $data[] = array(
            'label' => _('Next check'),
            'value' => $rawdata['next_check']
        );

        return $data;
    }
}
