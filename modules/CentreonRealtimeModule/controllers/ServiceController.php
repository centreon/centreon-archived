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

/**
 * Display service monitoring states
 *
 * @author Sylvestre Ho
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class ServiceController extends \Centreon\Internal\Controller
{
    protected $datatableObject = '\CentreonRealtime\Internal\ServiceDatatable';
    
    protected $objectClass = '\CentreonRealtime\Models\Service';
    
    /**
     * Display services
     *
     * @method get
     * @route /realtime/service
     * @todo work on ajax refresh
     */
    public function displayServicesAction()
    {
        $tpl = \Centreon\Internal\Di::getDefault()->get('template');

        /* Load css */
        $tpl->addCss('dataTables.tableTools.min.css')
            ->addCss('dataTables.colVis.min.css')
            ->addCss('dataTables.colReorder.min.css')
            ->addCss('dataTables.fixedHeader.min.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('jquery.qtip.min.css')
            ->addCss('centreon.qtip.css')
            ->addCss('daterangepicker-bs3.css');

        /* Load js */
        $tpl->addJs('jquery.min.js')
            ->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
            ->addJs('dataTables.colReorder.min.js')
            ->addJs('dataTables.fixedHeader.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('dataTables.bootstrap.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validate.min.js')
            ->addJs('additional-methods.min.js')
            ->addJs('jquery.qtip.min.js')
            ->addJs('moment-with-langs.min.js')
            ->addJs('daterangepicker.js')
            ->addJs('centreon.search.js')
            ->addJs('centreon.overlay.js');

        /* Datatable */
        $tpl->assign('moduleName', 'CentreonRealtime');
        $tpl->assign('datatableObject', $this->datatableObject);
        $tpl->assign('objectName', 'Service');
        $tpl->assign('consoleType', 1); // service console
        $tpl->assign('objectListUrl', '/realtime/service/list');

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

        /* Add javascript and css file for hooks */
        Hook::addStaticFile('displaySvcTooltipGraph');

        $tpl->display('file:[CentreonRealtimeModule]console.tpl');
    }

    /**
     * The page structure for display
     *
     * @method get
     * @route /realtime/service/list
     */
    public function listAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $myDatatable = new $this->datatableObject($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
        
        $router->response()->json($myDataForDatatable);
        
        /*$router = \Centreon\Internal\Di::getDefault()->get('router');
        $router->response()->json(
            \Centreon\Internal\Datatable::getDatas(
                'CentreonRealtime',
                'service',
                $this->getParams('get')
            )
        );*/
    }

    /**
     * Service detail page
     *
     * @method get
     * @route /realtime/service/[i:id]
     */
    public function serviceDetailAction()
    {
        $tpl = \Centreon\Internal\Di::getDefault()->get('template');

        /* Load css */
        $tpl->addCss('dataTables.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('dataTables-TableTools.css')
            ->addCss('jquery.qtip.min.css')
            ->addCss('centreon.qtip.css')
            ->addCss('daterangepicker-bs3.css');

        /* Load js */
        $tpl->addJs('jquery.min.js')
            ->addJs('jquery.dataTables.min.js')
            ->addJs('jquery.dataTables.TableTools.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validate.min.js')
            ->addJs('additional-methods.min.js')
            ->addJs('jquery.qtip.min.js')
            ->addJs('moment-with-langs.min.js')
            ->addJs('daterangepicker.js');

        /* Datatable */
        $tpl->assign('moduleName', 'CentreonRealtime');
        $tpl->assign('objectName', 'Service');

        $tpl->display('file:[CentreonRealtimeModule]service_detail.tpl');
    }

    /**
     * Service tooltip
     *
     * @method get
     * @route /realtime/service/[i:hid]/[i:sid]/tooltip
     */
    public function serviceTooltipAction()
    {
        $params = $this->getParams();
        $rawdata = ServicedetailRepository::getRealtimeData($params['hid'], $params['sid']);
        if (isset($rawdata[0])) {
            $data = $this->transformRawData($rawdata[0]);
            $this->tpl->assign('title', $rawdata[0]['host_name'] . ' - ' . $rawdata[0]['service_description']);
            $this->tpl->assign('state', $rawdata[0]['state']);
            $this->tpl->assign('data', $data);
        } else {
            $this->tpl->assign('error', sprintf(_('No data found for service id:%s'), $params['id']));
        }
        $this->tpl->assign('params', array('host_id' => $params['hid'], 'svc_id' => $params['sid']));
        $this->tpl->display('file:[CentreonRealtimeModule]service_tooltip.tpl');
    }

    /**
     * Display graph in a tooltip
     *
     * @method get
     * @route /realtime/service/[i:hid]/[i:sid]/graph
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
