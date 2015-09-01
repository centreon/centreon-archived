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

namespace CentreonConfiguration\Events;

class NodeEvent
{
    /**
     * Refers to the poller id
     * @var int
     */
    private $pollerId;

    /**
     * Array of output - should be the output of the process after 
     * performing the action
     * The output strings will be displayed on the UI
     * @var array 
     */
    private $output;

    /**
     * Whether or not action is successful.
     * true = successful, false = error
     * @var bool
     */
    private $status;

    /**
     * @param int $pollerId
     */
    public function __construct($pollerId)
    {
        $this->pollerId = $pollerId;
        $this->output = array();
        $this->status = true;
    }

    /**
     * @return int
     */
    public function getPollerId()
    {
        return $this->pollerId;
    }

    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output[] = $output;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
