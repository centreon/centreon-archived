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
declare(strict_types=1);

namespace Centreon\Application\Controller\Configuration;

use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use FOS\RestBundle\Context\Context;
use Centreon\Application\Controller\AbstractController;
use FOS\RestBundle\View\View;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class is designed to manage all requests concerning pollers
 *
 * @package Centreon\Application\Controller
 */
class MonitoringServerController extends AbstractController
{
    /**
     * @var MonitoringServerServiceInterface
     */
    private $monitoringServerService;

    /**
     * PollerController constructor.
     * @param MonitoringServerServiceInterface $monitoringServerService
     */
    public function __construct(MonitoringServerServiceInterface $monitoringServerService)
    {
        $this->monitoringServerService = $monitoringServerService;
    }

    /**
     * Entry point to find the last hosts acknowledgements.
     *
     * @param RequestParametersInterface $requestParameters
     * @param Request $request
     * @return View
     * @throws \Exception
     */
    public function findServer(RequestParametersInterface $requestParameters, Request $request): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $server = $this->monitoringServerService->findServers();
        $context = (new Context())->setGroups([
            MonitoringServer::SERIALIZER_GROUP_MAIN,
        ]);

        return $this->view(
            [
                'result' => $server,
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }
}
