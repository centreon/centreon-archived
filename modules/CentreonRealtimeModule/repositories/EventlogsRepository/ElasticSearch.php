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
