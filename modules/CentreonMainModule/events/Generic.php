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

namespace CentreonMain\Events;

/**
 * Parameters for generic events 
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonMain
 */
class Generic
{
    /**
     * The array of input
     *
     * @var array
     */
    private $input = array();

    /**
     * The array for output
     *
     * @var array
     */
    private $output = array();

    /**
     * The contructor
     *
     * @param array $input The assoc array for input information
     */
    public function __construct($input = array())
    {
        $this->input = $input;
    }

    /**
     * Set the values for output
     *
     * @param array $output The assoc array for output values
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /** 
     * Set/add a value un output
     *
     * @param string $key The name of output value
     * @param mixed $value The value
     */
    public function addOutput($key, $value)
    {
        $this->output[$key] = $value;
    }

    /**
     * Get the output values
     *
     * @return array The values
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Get the input values
     *
     * @return array The values
     */
    public function getInput()
    {
        return $this->input;
    }
}
