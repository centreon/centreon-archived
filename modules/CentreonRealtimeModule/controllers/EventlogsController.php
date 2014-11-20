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

/**
 * Display the logs of engine
 *
 * @authors Maximilien Bersoult
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class EventlogsController extends \Centreon\Internal\Controller
{
    /**
     * The page structure for display
     *
     * @method GET
     * @route /realtime/eventlogs
     */
    public function displayAction()
    {
        $di = Di::getDefault();

        $tmpl = $di->get('template');
        $tmpl->addJs('hogan-3.0.0.min.js');
        $tmpl->addJs('moment-with-langs.min.js');
        $tmpl->addJs('daterangepicker.js');
        $tmpl->addJs('jquery.select2/select2.min.js');
        $tmpl->addJs('centreon-table-infinite-scroll.js');
        $tmpl->addCss('select2.css');
        $tmpl->addCss('select2-bootstrap.css');
        $tmpl->addCss('daterangepicker-bs3.css');
        $tmpl->addCss('centreon.status.css');
        $tmpl->display('file:[CentreonRealtimeModule]eventlogs.tpl');
    }

    /**
     * Get the list of event logs
     *
     * @method POST
     * @route /realtime/eventlogs
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
     * @route /realtime/eventlogs/lasthostevents/[i:host_id]/[i:last_nb]
     */
    public function getLastNbEventsOfHostAction()
    {
        $requestParams = $this->getParams('named');
        $hostId = $requestParams['host_id'];
        $lastNb = $requestParams['last_nb'];
        $eventLogs = EventlogsRepository::getEventLogs(
            null,
            'DESC',
            $lastNb
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
     * @route /realtime/eventlogs/refresh
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
            if (isset($log['service_id']) && isset($log['host_id'])) {
                $log['status_text'] = Status::numToString(
                    $log['status'],
                    Status::TYPE_SERVICE,
                    true
                );
            } elseif (!isset($log['service_id'])) {
                $log['status_text'] = Status::numToString(
                    $log['status'],
                    Status::TYPE_HOST,
                    true
                );
            } else {
                $log['status_text'] = Status::numToString(
                    $log['status'],
                    Status::TYPE_EVENT,
                    true
                );
            }

            if ($log['msg_type'] != 1 && $log['msg_type'] != 0) {
                $log['border_color'] = 'centreon-border-info';
            } else {
                $log['border_color'] = 'centreon-border-status-' . $log['status'];
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
