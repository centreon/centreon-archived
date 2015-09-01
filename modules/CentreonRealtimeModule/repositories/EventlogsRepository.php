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

namespace CentreonRealtime\Repository;

use Centreon\Internal\Di;
use CentreonRealtime\Repository\EventlogsRepository\Database as EventLogDatabase;
use CentreonRealtime\Repository\EventlogsRepository\ElasticSearch;

/**
 * Factory for Eventlogs
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 */
class EventlogsRepository
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
        $di = Di::getDefault();

        /* If time is a timestamp, convert it */
        if (!is_null($fromTime) && is_numeric($fromTime)) {
            $fromTime = date('Y-m-d H:i:s', $fromTime);
        }

        /* Get configuration */
        $storageType = $di->get('config')->get('default', 'eventlogs', 'database');
        if ($storageType == 'database') {
            return EventLogDatabase::getEventLogs(
                $fromTime,
                $order,
                $limit,
                $filters
            );
        } elseif ($storageType == 'elasticsearch') {
            return ElasticSearch::getEventLogs(
                $fromTime,
                $order,
                $limit,
                $filters
            );
        }
        throw new \Exception("The eventlogs storage does not exists");
    }
}
