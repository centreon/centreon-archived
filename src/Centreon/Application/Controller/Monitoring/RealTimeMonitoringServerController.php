<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
declare(strict_types=1);

namespace Centreon\Application\Controller\Monitoring;

use FOS\RestBundle\View\View;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\MonitoringServer\Exception\RealTimeMonitoringServerException;
use Centreon\Infrastructure\MonitoringServer\API\Model\RealTimeMonitoringServerFactory;
use Centreon\Domain\MonitoringServer\UseCase\RealTimeMonitoringServer\FindRealTimeMonitoringServers;

/**
 * This class is designed to provide APIs for the context of RealTime Monitoring Servers.
 *
 * @package Centreon\Application\Controller\RealTimeMonitoringServer\Controller
 */
class RealTimeMonitoringServerController extends AbstractController
{
    /**
     * @param RequestParametersInterface $requestParameters
     * @param FindRealTimeMonitoringServers $findRealTimeMonitoringServers
     * @return View
     * @throws RealTimeMonitoringServerException
     */
    public function findRealTimeMonitoringServers(
        RequestParametersInterface $requestParameters,
        FindRealTimeMonitoringServers $findRealTimeMonitoringServers
    ): View {
        $this->denyAccessUnlessGrantedForApiRealtime();
        $response = $findRealTimeMonitoringServers->execute();
        return $this->view(
            [
                'result' => RealTimeMonitoringServerFactory::createFromResponse($response),
                'meta' => $requestParameters->toArray()
            ]
        );
    }
}
