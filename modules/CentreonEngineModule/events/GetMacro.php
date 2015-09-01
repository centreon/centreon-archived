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

namespace CentreonEngine\Events;

use CentreonConfiguration\Events\IGetMacro;

/**
 * This event allows modules to generate extra custom macros for resources
 */
abstract class GetMacro implements IGetMacro
{
    /**
     * multi dimensional array
     * i.e: $macro[$resourceId]['randomMacroName'] = 'randomMacroValue'
     * @var array
     */
    private $macros;

    /**
     * Unique id of poller
     * We don't wanna get unnecessary macros
     *  
     * @var int
     */
    private $pollerId;

    /**
     * @param int $pollerId Unique id of poller
     */
    public function __construct($pollerId)
    {
        $this->pollerId = $pollerId;
        $this->macros = array();
    }

    /**
     * Returns poller id
     *
     * @return int
     */
    public function getPollerId()
    {
        return $this->pollerId;
    }

    /**
     * Set macro
     *
     * @param int $resourceId Unique resource id of the object
     * @param string $macroName
     * @param string $macroValue 
     */
    public function setMacro($resourceId, $macroName, $macroValue)
    {
        if (!isset($this->macros[$resourceId])) {
            $this->macros[$resourceId] = array();
        }
        $macroName = strtoupper($macroName);
        $this->macros[$resourceId][$macroName] = $macroValue;
    }

    /**
     * Returns the whole array
     * 
     * @return array 
     */
    public function getMacroArray()
    {
        return $this->macros;
    }

    /**
     * Returns the macros of a certain resource id
     *
     * @param int $resourceId
     * @return array
     */
    public function getMacro($resourceId)
    {
        if (isset($this->macros[$resourceId])) {
            return $this->macros[$resourceId];
        }
        return array();
    }
}
