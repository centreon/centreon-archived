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

use Centreon\Internal\Di;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Utils\Status;
use Centreon\Internal\Controller;
use CentreonRealtime\Repository\IncidentsRepository;

/**
 * Display the logs of engine
 *
 * @authors Maximilien Bersoult
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class IncidentsController extends Controller
{
    /**
     * The page structure for display the list
     *
     * @method GET
     * @route /incident
     */
    public function displayListAction()
    {
        $di = Di::getDefault();

        $tmpl = $di->get('template');
        $tmpl->addJs('hogan-3.0.0.min.js');
        $tmpl->addJs('centreon-table-infinite-scroll.js');
        $tmpl->display('file:[CentreonRealtimeModule]incidents_list.tpl');
    }

    /**
     * Get the list of incidents
     *
     * @method GET
     * @route /incidentScroll
     */
    public function getListIncidentsAction()
    {
        $router = Di::getDefault()->get('router');
        $params = $router->request()->paramsGet();
        $filters = $params->all();

        $fromTime = null;
        if (isset($params['startTime']) && !is_null($params['startTime']) && $params['startTime'] !== '') {
            $fromTime = $params['startTime'];
        }
        if (isset($params['startTime'])) {
            unset($filters['startTime']);
        }
        $listIncidents = IncidentsRepository::getIncidents(
            $fromTime,
            'DESC',
            20,
            $filters
        );

        $data = array();
        $lastDateCount = 0;
        $lastDate = null;
        $firstDate = null;
        foreach ($listIncidents as $incident) {
            if ($lastDate != $incident['start_time']) {
                $lastDate = $incident['start_time'];
                $lastDateCount = 0;
            }
            if (is_null($firstDate)) {
                $firstDate = $incident['start_time'];
            }
            $lastDateCount++;

            /* Convert to human readable the duration */
            $incident['duration'] = Datetime::humanReadable(
                time() - strtotime($incident['start_time']),
                Datetime::PRECISION_FORMAT,
                2
            );
            /* Translate the status */
            if (false === is_null($incident['service_id'])) {
                $incident['object'] = 's';
                $incident['status'] = Status::numToString(
                    $incident['state'],
                    Status::TYPE_SERVICE,
                    true
                );
            } else {
                $incident['object'] = 'h';
                $incident['status'] = Status::numToString(
                    $incident['state'],
                    Status::TYPE_HOST,
                    true
                );
            }
            $data[] = $incident;
        }

        $router->response()->json(
            array(
                'data' => $data,
                'lastTimeEntry' => $lastDate,
                'nbEntryForLastTime' => $lastDateCount,
                'recentTime' => $firstDate
            )
        );
    }

    /**
     * Get extended information for a issue
     *
     * @route /incident/extented_info
     * @method POST
     */
    public function getIncidentExtInfoAction()
    {
        $router = Di::getDefault()->get('router');
        $incidentId = $router->request()->param('id');

        /* Get the list of children */
        $listChildren = IncidentsRepository::getChildren($incidentId);
        $children = array();

        /* Convert format */
        foreach ($listChildren as $child) {
            $fullname = $child['name'];
            if (false === is_null($child['description'])) {
                $fullname .= ' - ' . $child['description'];
            }
            $children[] = array(
                "name" => $fullname,
                "output" => $child['output'],
                "status" => $child['state']
            );
        }
        $children[] = array(
            "name" => "Test1",
            "output" => "Output 1",
            "status" => 2
        );
        $children[] = array(
            "name" => "Test2",
            "output" => "Output 2",
            "status" => 1
        );

        $router->response()->json(
            array(
            "children" => $children
            )
        );
    }

    /**
     * Display the graph map of incident
     *
     * @route /incident/graph/[i:id]
     * @method GET
     */
    public function displayIncidentGraphAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $incidentId = $router->request()->param('id');

        $tmpl = $di->get('template');
        $tmpl->addJs('jquery.jsPlumb-1.6.1-min.js');
        $tmpl->addJs('centreon.incidentsGraph.js');
        $tmpl->addCss('centreon.incidentsGraph.css');
        $tmpl->addCss('centreon.status.css');
        $tmpl->assign('incident_id', $incidentId);

        $tmpl->display('file:[CentreonRealtimeModule]incident_graph.tpl');
    }

    /**
     * Get information for a incident for display graph
     *
     * @route /incident/graph
     * @method POST
     */
    public function getIncidentGraphInfoAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $response = array();

        $action = $router->request()->param('action', null);
        $incidentId = $router->request()->param('incident_id', null);
        if (is_null($incidentId)) {
            $router->response()->code(400);
            return;
        }
        switch ($action) {
            case 'get_info':
                $incident = IncidentsRepository::getIncident($incidentId);
                $fullname = $incident['name'];
                if (false === is_null($incident['description'])) {
                    $fullname .= ' - ' . $incident['description'];
                }
                $response = array(
                    'id' => $incident['issue_id'],
                    'name' => $fullname,
                    'status' => self::getCssStatus($incident['state']),
                    'output' => $incident['output'],
                    'last_update' => Datetime::format($incident['last_state_change']),
                    'has_children' => $incident['nb_children'] > 0 ? true : false,
                    'has_parent' => $incident['nb_parents'] > 0 ? true : false,
                    'parents' => array_map(
                        function ($values) {
                            $parent = array();
                            $parent['id'] = $values['issue_id'];
                            $fullname = $values['name'];
                            if (!is_null($values['description'])) {
                                $fullname .= ' - ' . $values['description'];
                            }
                            $parent['name'] = $fullname;
                            return $parent;
                        },
                        $incident['parents']
                    )
                );
                break;
            case 'getChildren':
                $listChildren = IncidentsRepository::getChildren($incidentId);
                $response = array();
                foreach ($listChildren as $child) {
                    $fullname = $child['name'];
                    if (false === is_null($child['description'])) {
                        $fullname .= ' - ' . $child['description'];
                    }
                    $response[] = array(
                        'id' => $child['issue_id'],
                        'name' => $fullname,
                        'status' => self::getCssStatus($child['state']),
                        'output' => $child['output'],
                        'last_update' => Datetime::format($child['last_state_change']),
                        'has_children' => $child['nb_children'] > 0 ? true : false,
                        'has_parent' => $child['nb_parents'] > 0 ? true : false
                    );
                }
                break;
            case 'get_extended_info':
                $status = IncidentsRepository::getListStatus($incidentId);
                $statusList = array();
                foreach ($status as $tmp) {
                    if (false === is_null($tmp['service_id'])) {
                        $statusType = Status::TYPE_SERVICE;
                    } else {
                        $statusType = Status::TYPE_HOST;
                    }
                    $statusList[] = array(
                        'id' => $tmp['state'],
                        'text' => Status::numToString(
                            $tmp['state'],
                            $statusType,
                            true
                        ),
                        'datetime' => $tmp['start_time']
                    );
                }
                $response = array(
                    'status' => array(
                        array(
                            'id' => 2,
                            'text' => 'Critical',
                            'datetime' => '2014-05-12 01:03:11'
                        ),
                        array(
                            'id' => 2,
                            'text' => 'Critical',
                            'datetime' => '2014-05-12 01:03:11'
                        ),
                        array(
                            'id' => 2,
                            'text' => 'Critical',
                            'datetime' => '2014-05-12 01:03:11'
                        ),
                        array(
                            'id' => 2,
                            'text' => 'Critical',
                            'datetime' => '2014-05-12 01:03:11'
                        )
                    )
                );
                break;
            default:
                $router->response()->code(400);
                return;
        }
        $router->response()->json($response);
    }

    /**
     * Get the status CSS for incident graph
     *
     * @param int $state The state number
     */
    public static function getCssStatus($state)
    {
        $status = "";
        switch ($state) {
            case 0:
                $status = "panel-default";
                break;
            case 1:
                $status = "panel-success";
                break;
            case 2:
                $status = "panel-warning";
                break;
            case 3:
                $status = "panel-warning";
                break;
            case 4:
                $status = "panel-danger";
                break;
        }
        return $status;
    }
}
