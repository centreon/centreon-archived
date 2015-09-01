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
 * Parameters for events centreon-main.status
 *
 * @author Maximilien Bersoult <mbersoult@merehtis.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonMain
 */
class Status
{
    /**
     * The list of status
     * @var array
     */
    private $status;

    public function __construct(&$status)
    {
        $this->status = &$status;
    }

    /**
     * Add a status to the list of status
     *
     * @param string $statusName The status name
     * @param mixed $statusValue The value a the status
     */
    public function addStatus($statusName, $statusValue)
    {
        $this->status[$statusName] = $statusValue;
    }
    
    public function getStatus($statusName)
    {
        if(isset($this->status[$statusName])){
            return $this->status[$statusName];
        }else{
            return null;
        }
            
    }
}
