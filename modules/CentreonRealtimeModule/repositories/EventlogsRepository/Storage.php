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

/**
 * Abstract class for Eventlogs storage
 *
 * @author Maximilien Bersoutl <mbersoutl@centreon.com>
 * @version 3.0.0
 */
abstract class Storage
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
    abstract public static function getEventLogs($fromTime = null, $order = 'DESC', $limit = null, $filters = array());
}
