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

use Centreon\Internal\Di;
use Centreon\Internal\Utils\Status;
use CentreonRealtime\Repository\EventlogsRepository;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Repository\ServiceRepository;
use Centreon\Internal\Controller;
use Centreon\Internal\Module\Informations as Module;

/**
 * Display the logs of engine
 *
 * @authors Maximilien Bersoult
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class EventlogsController extends Controller
{
    /**
     * 
     * @param type $request
     */
    public function __construct($request)
    {
        $hostConfRepository = '\CentreonConfiguration\Repository\HostRepository';
        $hostConfRepository::setObjectClass('\CentreonConfiguration\Models\Host');
        $serviceConfRepository = '\CentreonConfiguration\Repository\ServiceRepository';
        $serviceConfRepository::setObjectClass('\CentreonConfiguration\Models\Service');
        parent::__construct($request);
    }
    
    /**
     * The page structure for display
     *
     * @method GET
     * @route /eventlogs
     */
    public function displayAction()
    {
        $di = Di::getDefault();

        $tmpl = $di->get('template');
        $tmpl->addJs('hogan-3.0.0.min.js');
        //$tmpl->addJs('moment-with-langs.min.js');
        $tmpl->addJs('daterangepicker.js');
        $tmpl->addJs('jquery.select2/select2.min.js');
        $tmpl->addJs('centreon.search.js');
        $tmpl->addJs('centreon-infinite-scroll.js');
        $tmpl->addCss('select2.css');
        $tmpl->addCss('select2-bootstrap.css');
        $tmpl->addCss('daterangepicker-bs3.css');
        $tmpl->addCss('centreon.status.css');
        if (Module::isModuleReachable('centreon-performance')) {
            $tmpl->addJs('d3.min.js');
            $tmpl->addJs('c3.min.js');
            $tmpl->addJs('centreon.graph.js', 'bottom', 'centreon-performance');
            $tmpl->addCss('c3.css');
        }
        /* Prepare field for search */
        $searchField = array(
            'header' => array(
                'columnSearch' => array(
                    'host' => array(
                        'main' => true,
                        'title' => _("Host name"),
                        'type' => 'text',
                        'searchLabel' => 'host',
                        'colIndex' => 1
                    ),
                    'service' => array(
                        'main' => true,
                        'title' => _("Service"),
                        'type' => 'text',
                        'searchLabel' => 'service',
                        'colIndex' => 2
                    ),
                    'output' => array(
                        'main' => false,
                        'title' => _("Message"),
                        'type' => 'text',
                        'searchLabel' => 'output',
                        'colIndex' => 3
                    ),
                    'status' => array(
                        'main' => false,
                        'title' => _("Status"),
                        'type' => 'select',
                        'searchLabel' => 'status',
                        'colIndex' => 3,
                        'additionnalParams' => array(
                            'Ok' => 0,
                            'Warning' => 1,
                            'Critical' => 2
                        )
                    ),
                    'eventtype' => array(
                        'main' => false,
                        'title' => _("Event Type"),
                        'type' => 'select',
                        'searchLabel' => 'eventtype',
                        'colIndex' => 4,
                        'additionnalParams' => array(
                            'Alert' => 0,
                            'Current State' => 6,
                            'Initial State' => 8,
                            'Notification' => 2,
                            'Acknowledgement' => 10
                        )
                    )
                )
            )
        );

        $tmpl->assign('datatableParameters', $searchField);
        $tmpl->display('file:[CentreonRealtimeModule]eventlogs.tpl');
    }

    /**
     * Get the list of event logs
     *
     * @method POST
     * @route /eventlogs
     */
    public function getListEventAction()
    {
        $router = Di::getDefault()->get('router');
        $params = $router->request()->paramsPost();
        $filters = $params->all();

        $fromTime = null;
        if (isset($params['startTime']) && !is_null($params['startTime']) && $params['startTime'] !== '') {
            $fromTime = $params['startTime'];
        }
        if (isset($params['startTime'])) {
            unset($filters['startTime']);
        }
        $listEvents = EventlogsRepository::getEventLogs(
            $fromTime,
            'DESC',
            20,
            $filters
        );
        $listEvents = $this->convertListEventLogs($listEvents);
        /* Purge data */
        if (isset($_SESSION['eventlogs_lasttime'])
            && count($listEvents['data']) > 0
            && $_SESSION['eventlogs_lasttime'][0] == $listEvents['data'][0]['datetime']) {
            for ($i = 0; $i < $_SESSION['eventlogs_lasttime'][1]; $i++) {
                array_shift($listEvents['data']);
            }
        }

        /* Save new information for last time entry */
        $_SESSION['eventlogs_lasttime'] = array(
            $listEvents['lastTimeEntry'],
            $listEvents['nbEntryForLastTime']
        );
        $router->response()->json($listEvents);
    }

    /**
     * Return list of event logs of a host
     * That includes the services too
     *
     * @method GET
     * @route /eventlogs/lasthostevents/[i:host_id]/[i:last_nb]
     */
    public function getLastNbEventsOfHostAction()
    {
        $router = Di::getDefault()->get('router');

        $requestParams = $this->getParams('named');
        $hostId = $requestParams['host_id'];
        $lastNb = $requestParams['last_nb'];
        $eventLogs = EventlogsRepository::getEventLogs(
            null,
            'DESC',
            $lastNb,
            array(
                'host_id' => $hostId
            )
        );
        $router->response()->json($eventLogs);
    }

    /**
     * Get new events
     *
     * @method POST
     * @route /eventlogs/refresh
     */
    public function refreshNewEventLogsAction()
    {
        $router = Di::getDefault()->get('router');
        $params = $router->request()->paramsPost();
        $filters = $params->all();
        if (isset($params['startTime'])) {
            unset($filters['startTime']);
        }
        $listEvents = EventlogsRepository::getEventLogs(
            $params['startTime'],
            'ASC',
            null,
            $filters
        );
        $router->response()->json(
            $this->convertListEventLogs($listEvents)
        );
    }

    /**
     * Convert the list of events for web output
     *
     * @param array $listEvents The list of events
     * @return array The datas for infinite scroll
     */
    private function convertListEventLogs($listEvents)
    {
        /* Convert data for output */
        $lastDateCount = 0;
        $lastDate = null;
        $firstDate = null;
        $data = array();
        foreach ($listEvents as $log) {
            if ($lastDate != $log['datetime']) {
                $lastDate = $log['datetime'];
                $lastDateCount = 0;
            }
            if (is_null($firstDate)) {
                $firstDate = $log['datetime'];
            }
            $lastDateCount++;
            if (false === is_null($log['host_id'])) {
                $log['host_logo'] = HostRepository::getIconImage($log['host']);
            } else {
                $log['host_logo'] = '';
            }
            if (false === is_null($log['service_id'])) {
                $log['service_logo'] = ServiceRepository::getIconImage(
                    $log['service']
                );
            } else {
                $log['service_logo'] = '';
            }
            
            if ($log['type'] != '') {
                if ($log['type'] == 1) {
                    $log['type'] = 'HARD';
                } else {
                    $log['type'] = 'SOFT';
                }
            } else {
                $log['type'] = "&nbsp;";
            }
            
            /* Translate the status id */
            if (isset($log['service_id']) && isset($log['host_id']) && $log['status'] !== '') {
                $log['status_text'] = Status::numToString(
                    $log['status'],
                    Status::TYPE_SERVICE,
                    true
                );
            } elseif (!isset($log['service_id']) && $log['status'] !== '') {
                $log['status_text'] = Status::numToString(
                    $log['status'],
                    Status::TYPE_HOST,
                    true
                );
            } else if ($log['status'] !== '') {
                $log['status_text'] = Status::numToString(
                    $log['status'],
                    Status::TYPE_EVENT,
                    true
                );
            }

            if ($log['msg_type'] != 1 && $log['msg_type'] != 0) {
                $log['status_css'] = '';
                $log['border_color'] = 'centreon-border-info';
            } elseif ($log['msg_type'] == 1) {
                $log['status_css'] = 'centreon-status-h-' . $log['status'];
                $log['border_color'] = 'centreon-border-status-h-' . $log['status'];
            } else {
                $log['status_css'] = 'centreon-status-s-' . $log['status'];
                $log['border_color'] = 'centreon-border-status-s-' . $log['status'];
            }
            /* For test */
            $log['object_name'] = "";
            $object_name = array();
            if (isset($log['host']) && false === is_null($log['host'])) {
                $object_name[] = $log['host'];
            }
            if (isset($log['service']) && false === is_null($log['service'])) {
                $object_name[] = $log['service'];
            }
            $log['object_name'] = join(' - ', $object_name);
            $log['logo'] = '';
            if (isset($log['service_logo'])) {
                $log['logo'] = $log['service_logo'];
            } else if (isset($log['host_logo'])) {
                $log['logo'] = $log['host_logo'];
            }
            if (isset($log['output'])) {
                $log['description'] = $log['output'];
            }
            
            $data[] = $log;
        }

        return  array(
            'data' => $data,
            'lastTimeEntry' => $lastDate,
            'nbEntryForLastTime' => $lastDateCount,
            'recentTime' => $firstDate,
            'facets' => array()
        );
    }
}
