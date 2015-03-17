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

use CentreonRealtime\Repository\HostdetailRepository;
use Centreon\Internal\Utils\Status;
use Centreon\Internal\Utils\Datetime;
use CentreonRealtime\Events\HostDetailData;
use CentreonRealtime\Repository\HostRepository;
use CentreonConfiguration\Repository\HostRepository as HostConfRepository;
use Centreon\Internal\Di;
use Centreon\Internal\Controller;

/**
 * Display service monitoring states
 *
 * @author Sylvestre Ho
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class HostController extends Controller
{
    protected $datatableObject = '\CentreonRealtime\Internal\HostDatatable';
    
    protected $objectClass = '\CentreonRealtime\Models\Host';
    
    /**
     * Display services
     *
     * @method get
     * @route /host
     * @todo work on ajax refresh
     */
    public function displayHostsAction()
    {
        $router = Di::getDefault()->get('router');
        /* Load css */
        $this->tpl->addCss('jquery.dataTables.min.css')
            ->addCss('dataTables.tableTools.min.css')
            ->addCss('dataTables.colVis.min.css')
            ->addCss('dataTables.colReorder.min.css')
            ->addCss('dataTables.fixedHeader.min.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('centreon.tag.css', 'centreon-administration');

        /* Load js */
        $this->tpl->addJs('jquery.min.js')
            ->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
            ->addJs('dataTables.colReorder.min.js')
            ->addJs('dataTables.fixedHeader.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validation/jquery.validate.min.js')
            ->addJs('jquery.validation/additional-methods.min.js')
            ->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.search.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addJs('bootstrap3-typeahead.js')
            ->addJs('centreon.search.js');

        /* Datatable */
        $this->tpl->assign('moduleName', 'CentreonRealtime');
        $this->tpl->assign('datatableObject', $this->datatableObject);
        $this->tpl->assign('objectName', 'Host');
        $this->tpl->assign('consoleType', 0); // host console
        $this->tpl->assign('objectListUrl', '/centreon-realtime/host/list');
        
        $actions = array();
        $actions[] = array(
            'group' => _('Hosts'),
            'actions' => HostdetailRepository::getMonitoringActions()
        );
        $this->tpl->assign('actions', $actions);
        
        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);

        $this->tpl->display('file:[CentreonRealtimeModule]console.tpl');
    }

    /**
     * The page structure for display
     *
     * @method get
     * @route /host/list
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
     * Host detail page
     *
     * @method get
     * @route /host/[i:id]
     */
    public function hostDetailAction()
    {
        $params = $this->getParams();
        $host = HostdetailRepository::getRealtimeData($params['id']);
        $this->tpl->assign('hostname', $host[0]['host_name']);
        $this->tpl->assign('address', $host[0]['host_address']);
        $this->tpl->assign('host_alias', $host[0]['host_alias']);
        $this->tpl->assign('host_icon', HostConfRepository::getIconImage($host[0]['host_name']));
        $this->tpl->assign('applications', array());
        $this->tpl->assign('routeParams', array(
            'id' => $params['id']
        ));

        $this->tpl->addCss('cal-heatmap.css')
             ->addCss('centreon.status.css');
        $this->tpl->addJs('d3.min.js')
             ->addJs('jquery.sparkline.min.js')
             ->addJs('cal-heatmap.min.js')
             ->addJs('jquery.knob.min.js');

        $this->tpl->display('file:[CentreonRealtimeModule]host_detail.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /host/[i:id]/data
     */
    public function hostDetailDataAction()
    {
        $params = $this->getParams('named');
        $events = Di::getDefault()->get('events');
        
        $success = true;
        $datas = array();
        
        // Get Host Infos
        $datas = HostRepository::getHostShortInfo($params['id']);
        $datas['output'] = nl2br(trim($datas['output']));

        $hostDetailDataEvent = new HostDetailData($params['id'], $datas);

        $events->emit('centreon-realtime.host.detail.data', array($hostDetailDataEvent));

        $this->router->response()->json(
            array(
                'success' => $success,
                'values' => $datas
            )
        );
    }

    /**
     * Host tooltip
     *
     * @method get
     * @route /host/[i:id]/tooltip
     */
    public function hostTooltipAction()
    {
        $params = $this->getParams();
        $rawdata = HostdetailRepository::getRealtimeData($params['id']);
        if (isset($rawdata[0])) {
            $data = $this->transformRawData($rawdata[0]);
            $this->tpl->assign('title', $rawdata[0]['host_name']);
            $this->tpl->assign('state', $rawdata[0]['state']);
            $this->tpl->assign('data', $data);
        } else {
            $this->tpl->assign('error', sprintf(_('No data found for host id:%s'), $params['id']));
        }
        $this->tpl->assign('params', array('host_id' => $params['id']));
        $this->tpl->display('file:[CentreonRealtimeModule]host_tooltip.tpl');
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

        /* Address */
        $data[] = array(
            'label' => _('Address'),
            'value' => $rawdata['host_address']
        );

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
                Status::TYPE_HOST,
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
            'value' => Datetime::format($rawdata['last_check'])
        );

        /* Next check */
        $data[] = array(
            'label' => _('Next check'),
            'value' => Datetime::format($rawdata['next_check'])
        );

        return $data;
    }
}
