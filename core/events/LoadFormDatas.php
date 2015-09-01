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

namespace Centreon\Events;

/**
 * This event allows modules to load form datas
 */
class LoadFormDatas
{
    private $route;
    private $objectId;
    private $parameters;

    public function __construct($route, $objectId, $parameters = array())
    {
        $this->route = $route;
        $this->objectId = $objectId;
        $this->parameters = $parameters;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function addParameters($params = array())
    {
        foreach ($params as $key => $value) {
            $this->parameters[$key] = $value;
        }
    }
}
