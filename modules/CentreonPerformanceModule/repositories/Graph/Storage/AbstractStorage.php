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

namespace CentreonPerformance\Repository\Graph\Storage;

/**
 * Abstract class for storage metrics
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 */
abstract class AbstractStorage
{
    protected $startTime = null;
    protected $endTime = null;

    /**
     * Constructor
     *
     * Set the end time at now and the start time at now - 12h
     */
    public function __construct()
    {
        $this->endTime = time();
        $this->startTime = $this->endTime - (12 * 3600);
    }

    /**
     * Method for getting the values for a metric
     *
     * @param int $metricId The metric id
     */
    abstract public function getValues($metricId);

    /**
     * Set the period for getting the metrics
     *
     * @param int $startTime The start time in timestamp format
     * @param int $endTime The end time in timestamp format
     */
    public function setPeriod($startTime, $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }
}
