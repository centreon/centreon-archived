<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Test\Api\Context;

use Centreon\Test\Behat\Api\Context\ApiContext;

class ServiceMonitoringContext extends ApiContext
{
    /**
     * @When I send a request to have the details of service :service from host :host
     */
    public function iSendARequestToHaveTheDetailsOfServiceFromHost($service, $host)
    {
        $hostId = $this->iWaitUntilServiceIsMonitored($service, $host)[0];
        $serviceId = $this->iWaitUntilServiceIsMonitored($service, $host)[1];
        $response = $this->iSendARequestTo('GET', "/api/beta/monitoring/hosts/$hostId/services/$serviceId");
        return $response;
    }
}
