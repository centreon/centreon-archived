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

namespace Centreon\Domain\MonitoringServer\UseCase\RealTimeMonitoringServer;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\MonitoringServer\Exception\RealTimeMonitoringServerException;
use Centreon\Domain\MonitoringServer\Interfaces\RealTimeMonitoringServerServiceInterface;

/**
 * This class is designed to represent a use case to find all host categories.
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21
 */
class FindRealTimeMonitoringServers
{

    /**
     * @var RealTimeMonitoringServerServiceInterface
     */
    private $realTimeMonitoringServerService;
    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * FindRealTimeMonitoringServers constructor.
     *
     * @param ContactInterface $contact
     */
    public function __construct(
        RealTimeMonitoringServerServiceInterface $realTimeMonitoringServerService,
        ContactInterface $contact
    ) {
        $this->contact = $contact;
        $this->realTimeMonitoringServerService = $realTimeMonitoringServerService;
    }

    /**
     * Execute the use case for which this class was designed.
     *
     * @return FindRealTimeMonitoringServersResponse
     * @throws RealTimeMonitoringServerException
     */
    public function execute(): FindRealTimeMonitoringServersResponse
    {
        $response = new FindRealTimeMonitoringServersResponse();
        $realTimeMonitoringServers = ($this->contact->isAdmin())
            ? $this->realTimeMonitoringServerService->findAllWithoutAcl()
            : $this->realTimeMonitoringServerService->findAllWithAcl();
        $response->setRealTimeMonitoringServers($realTimeMonitoringServers);
        return $response;
    }
}
