<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace CentreonClapi\Validator;

use CentreonClapi\CentreonHost;
use CentreonClapi\CentreonRtAcknowledgement;
use CentreonClapi\CentreonRtDowntime;
use CentreonClapi\CentreonService;

/**
 * This class is used to validate inputs for RT clapi endpoints.
 *
 * @see CentreonRtAcknowledgement
 * @see CentreonRtDowntime
 */
class RtValidator
{
    /**
     * @var CentreonHost
     */
    private $hostObject;

    /**
     * @var CentreonService
     */
    private $serviceObject;

    public function __construct(CentreonHost $hostObject, CentreonService $serviceObject)
    {
        $this->hostObject = $hostObject;
        $this->serviceObject = $serviceObject;
    }

    /**
     * Data storage perform an insensitive case search. We want to enforce the strictness here.
     * This also automatically checks the existence of the object.
     *
     * @param string $host
     * @param string $service
     * @return bool
     */
    public function isServiceNameValid(string $host, string $service): bool
    {
        $hostIdAndServiceId = $this->serviceObject->getHostAndServiceId($host, $service);
        if ([] === $hostIdAndServiceId) {
            return false;
        }

        $realHostName = $this->hostObject->getHostName($hostIdAndServiceId[0]);
        $realServiceName = $this->serviceObject->getObjectName($hostIdAndServiceId[1]);

        return $realHostName === $host
            && $realServiceName === $service
            && $this->serviceObject->serviceExists($host, $service);
    }

    /**
     * Data storage perform an insensitive case search. We want to enforce the strictness here.
     * This also automatically checks the existence of the object.
     *
     * @param string $name
     * @return bool
     */
    public function isHostNameValid(string $name): bool
    {
        $hostId = $this->hostObject->getHostID($name);
        $realHostName = $this->hostObject->getHostName($hostId);

        return $realHostName === $name;
    }
}
