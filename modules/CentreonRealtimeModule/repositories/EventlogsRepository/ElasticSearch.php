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

namespace CentreonRealtime\Repository\EventlogsRepository;

use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Utils\Status;
use Centreon\Internal\Di;
use Elasticsearch\Client as ESClient;

/**
 * Storage elastic search for Eventlogs
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 */
class ElasticSearch extends Storage
{
    /**
     * Return the list of events
     *
     * @param $fromTime string The start time in date format Y-m-d H:i:s
     * @param $order string The order for getting events : DESC or ASC
     * @param $limit int The number of event to get
     * @param $filters array The list of fitlers for event
     * @return array
     */
    public static function getEventLogs($fromTime = null, $order = 'DESC', $limit = null, $filters = array())
    {
        $listFullsearch = array('output');
        $timeField = array('period');

        $di = Di::getDefault();
        $options = $di->get('config');

        /* Connection to the elastic search server */
        $params['hosts'] = array();
        $params['hosts'][] = $options->get('default', 'es_host');
        $esclient = new ESClient($params);

        $queryFilters = array();
        if (false === is_null($limit)) {
            $queryFilters["filter"] = array(
                "limit" => array(
                    "value" => $limit
                )
            );
        }
        $queryQueries = array();
        if (false === is_null($fromTime)) {
            $queryQueries[] = array(
                "range" => array(
                    "@timestamp" => array(
                        "to" => $fromTime
                    )
                )
            );
        }
        foreach ($filters as $key => $value) {
            if (in_array($key, $listFullsearch)) {
                $queryQueries[] = array(
                    "term" => array(
                        "message" => $value
                    )
                );
            } elseif (in_array($key, $timeField)) {
                list($timeStart, $timeEnd) = explode(' - ', $value);
                $queryQueries[] = array(
                    "range" => array(
                        "@timestamp" => array(
                            "from" => $timeStart,
                            "to" => $timeEnd
                        )
                    )
                );
            } elseif ($key == 'host') {
                $queryQueries[] = array(
                    "or" => array(
                        array(
                            "term" => array(
                                "host" => $value
                            )
                        ),
                        array(
                            "term" => array(
                                "centreon_hostname" => $value
                            )
                        )
                    )
                );
            } elseif ($key == 'service') {
                $queryQueries[] = array(
                    "term" => array(
                        "centreon_service" => $value
                    )
                );
            } elseif ($key == 'eventtype') {
                $queryQueries[] = self::queryEventtype($value);
            } elseif ($key == 'status') {
                $queryQueries[] = array(
                    "or" => array(
                        "term" => array(
                            "centreon_status" => strtoupper(Status::numToString($value, Status::TYPE_SERVICE))
                        ),
                        "term" => array(
                            "centreon_status" => strtoupper(Status::numToString($value, Status::TYPE_HOST))
                        ),
                        "term" => array(
                            "centreon_status" => strtoupper(Status::numToString($value, Status::TYPE_EVENT))
                        )
                    )
                );
            }
        }
        /* Add filter for only centreon-engine message */
        $queryQueries[] = array(
            'exists' => array(
                'field' => 'centreon_hostname'
            )
        );
        if (count($queryQueries) > 0) {
            $queryFilters["filter"]["and"] = $queryQueries;
        }
        if (count($queryFilters) > 0) {
            $esQuery = array(
                "query" => array(
                    "filtered" => $queryFilters
                )
            );
        } else {
            $esQuery = array(
                "query" => array(
                    "match_all" => array()
                )
            );
        }
        $esQuery['sort'] = array(
            array('@timestamp' => 'desc')
        );
        file_put_contents('/tmp/query_es', json_encode($esQuery, JSON_PRETTY_PRINT));
        $results = $esclient->search(array(
            "body" => $esQuery
        ));
        $data = array();
        foreach ($results['hits']['hits'] as $result) {
            if (isset($result['_source']) && $result['_type'] == 'syslog') {
                $host = '';
                $service = '';
                $status = '';
                $type = '';
                if (isset($result['_source']['centreon_hostname'])) {
                    $host = $result['_source']['centreon_hostname'];
                } else {
                    $host = $result['_source']['host'];
                }
                if (isset($result['_source']['centreon_service'])) {
                    $service = $result['_source']['centreon_service'];
                }
                if (isset($result['_source']['centreon_status']) && isset($result['_source']['centreon_type'])) {
                    $type = $result['_source']['centreon_type'];
                    if (strstr('HOST', substr($type, 4))) {
                        $statusType = Status::TYPE_HOST;
                    } else if (strstr('SERVICE', substr($type, 7))) {
                        $statusType = Status::TYPE_SERVICE;
                    } else {
                        $statusType = Status::TYPE_EVENT;
                    }
                    try {
                        $textStatus = ucfirst(strtolower($result['_source']['centreon_status']));
                        $status = Status::stringToNum($textStatus, $statusType);
                    } catch(\OutOfBoundsException $e) {
                        $status = '';
                    }
                }
                $data[] = array(
                    'datetime' => strtotime($result['_source']['@timestamp']),
                    'host_id' => '',
                    'host' => $host,
                    'service_id' => '',
                    'service' => $service,
                    'instance' => '',
                    'output' => $result['_source']['message'],
                    'status' => $status,
                    'type' => $type,
                    'msg_type' => ''
                );
            }
        }
        return $data;
    }

    /**
     * Get the query for event type
     *
     * @param int $eventtype The event type ID
     * @return array
     */
    protected static function queryEventtype($eventtype)
    {
        switch ($eventtype) {
            case 0:
                $value = "ALERT";
                break;
            case 2:
                $value = "NOTIFICATION";
                break;
            case 6:
                $value = "CURRENT";
                break;
            case 8:
                $value = "INITIAL";
                break;
            case 10:
                $value = "ACKNOWLEDGE_";
                break;
        }
        return array(
            "term" => array(
                "centreon_type" => $value
            )
        );
    }
}
