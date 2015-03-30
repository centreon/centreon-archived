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
        $options = $di->get('options');

        /* Connection to the elastic search server */
        $params['host'] = array();
        $params['host'][] = $options->get('es_host', 'default');
        $esclient = new ESClient($params);

        $queryFilters = ();
        foreach ($filters as $key => $value) {
            if (in_array($key, $listFullsearch)) {
                $queryFilters[] = array(
                    "term" => array(
                        "message" => $value
                    )
                );
            } elseif (in_array($key, $timeField)) {
                list($timeStart, $timeEnd) = explode(' - ', $value);
                $queryFilters[] = array(
                    "range" => array(
                        "from" => $timeStart,
                        "to" => $timeEnd
                    )
                );
            }
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
            array('@timestamp' => array('order', 'desc')
        );
        $results = $esclient->search($esQuery);
        $data = [];
        foreach ($results['hits']['hits'] as $result) {
            $data[] = array(
                'datetime' => $result['@timestamp'],
                'host_id' => '',
                'host' => '',
                'service_id' => '',
                'service' => '',
                'instance' => '',
                'output' => $result['message'],
                'status' => '',
                'type' => '',
                'msg_type' => ''
            );
        }
        return $data;
    }
}
