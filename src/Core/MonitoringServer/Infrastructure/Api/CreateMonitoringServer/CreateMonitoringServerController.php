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

declare(strict_types=1);

namespace Core\MonitoringServer\Infrastructure\Api\CreateMonitoringServer;

use Symfony\Component\HttpFoundation\Request;
use Core\MonitoringServer\Application\UseCase\CreateMonitoringServer\CreateMonitoringServer;
use Core\MonitoringServer\Application\UseCase\CreateMonitoringServer\CreateMonitoringServerPresenterInterface;
use Core\MonitoringServer\Application\UseCase\CreateMonitoringServer\CreateMonitoringServerRequest;

class CreateMonitoringServerController
{
    public function __invoke(
        CreateMonitoringServer $useCase,
        Request $request,
        CreateMonitoringServerPresenterInterface $presenter
    ): object {
        $createMonitoringServerRequest = $this->createCreateMonitoringServerRequest($request);
        $useCase($presenter, $createMonitoringServerRequest);

        return $presenter->show();
    }

    public function createCreateMonitoringServerRequest(Request $request): CreateMonitoringServerRequest
    {
        $requestData = json_decode((string) $request->getContent(), true);
        $createMonitoringServerRequest = new CreateMonitoringServerRequest($requestData);

        return $createMonitoringServerRequest;
    }
}
