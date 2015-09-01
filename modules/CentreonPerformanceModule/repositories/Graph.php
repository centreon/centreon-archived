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

namespace CentreonPerformance\Repository;

/**
 * Abstract class for get data for graph
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 */
abstract class Graph
{
    protected $metrics = array();
    protected $step = null;
    protected $nbPoints = null;
    protected $startTime = null;
    protected $endTime = null;

    /**
     * Constructor
     *
     * @param int $startTime The start time
     * @param int $endTime The end time
     */
    protected function __construct($startTime, $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    /**
     * Get the list of values
     *
     * @param int $nbPoints The number of points to get
     * @return array
     */
    public function getValues($nbPoints = 100)
    {
        $storage = Graph\Storage::getStorage();
        $storage->setPeriod($this->startTime, $this->endTime);
        $values = array();
        foreach ($this->metrics as $metric) {
            $metric['data'] = $storage->getValues($metric['id'], $nbPoints);
            if ($metric['is_negative']) {
                array_walk($metric['data'], function(&$value, $key) {
                    $value = $value * -1;
                });
            }
            $values[] = $metric;
        }
        return $values;
    }
}
