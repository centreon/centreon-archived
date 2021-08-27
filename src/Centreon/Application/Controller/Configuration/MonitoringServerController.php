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

namespace Centreon\Application\Controller\Configuration;

use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\MonitoringServer\Exception\ConfigurationMonitoringServerException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Context\Context;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\MonitoringServer\UseCase\ReloadConfiguration;
use Centreon\Domain\MonitoringServer\UseCase\GenerateConfiguration;
use Centreon\Domain\MonitoringServer\UseCase\ReloadAllConfigurations;
use Centreon\Domain\MonitoringServer\UseCase\GenerateAllConfigurations;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerServiceInterface;

/**
 * This class is designed to manage all requests concerning monitoring servers
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
     * @param MonitoringServerServiceInterface $monitoringServerService
     */
    public function __construct(MonitoringServerServiceInterface $monitoringServerService)
    {
        $this->monitoringServerService = $monitoringServerService;
    }

    /**
     * Entry point to find a monitoring server
     *
     * @param RequestParametersInterface $requestParameters
     * @return View
     * @throws \Exception
     */
    public function findServer(RequestParametersInterface $requestParameters): View
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

    /**
     * @param GenerateConfiguration $generateConfiguration
     * @param int $monitoringServerId
     * @return View
     * @throws EntityNotFoundException
     */
    public function generateConfiguration(GenerateConfiguration $generateConfiguration, int $monitoringServerId): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $response = $generateConfiguration->execute($monitoringServerId);
        return $this->view([
            'status' => (int) $response->isSuccess(),
            'message' => $response->getMessage()
        ]);
    }

    /**
     * @param GenerateAllConfigurations $generateAllConfigurations
     * @return View
     * @throws ConfigurationMonitoringServerException
     */
    public function generateAllConfigurations(GenerateAllConfigurations $generateAllConfigurations): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $response = $generateAllConfigurations->execute();
        return $this->view([
            'status' => (int) $response->isSuccess(),
            'message' => $response->getMessage()
        ]);
    }

    /**
     * @param ReloadConfiguration $reloadConfiguration
     * @param int $monitoringServerId
     * @return View
     * @throws EntityNotFoundException
     */
    public function reloadConfiguration(ReloadConfiguration $reloadConfiguration, int $monitoringServerId): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $response = $reloadConfiguration->execute($monitoringServerId);
        return $this->view([
            'status' => (int) $response->isSuccess(),
            'message' => $response->getMessage()
        ]);
    }

    /**
     * @param ReloadAllConfigurations $reloadAllConfigurations
     * @return View
     * @throws ConfigurationMonitoringServerException
     */
    public function reloadAllConfigurations(ReloadAllConfigurations $reloadAllConfigurations): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $response = $reloadAllConfigurations->execute();
        return $this->view([
            'status' => (int) $response->isSuccess(),
            'message' => $response->getMessage()
        ]);
    }

    /**
     * @param GenerateConfiguration $generateConfiguration
     * @param ReloadConfiguration $reloadConfiguration
     * @param int $monitoringServerId
     * @return View
     * @throws EntityNotFoundException
     */
    public function generateAndReloadConfiguration(
        GenerateConfiguration $generateConfiguration,
        ReloadConfiguration $reloadConfiguration,
        int $monitoringServerId
    ): View {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $response = $generateConfiguration->execute($monitoringServerId);
        if ($response->isSuccess()) {
            $response = $reloadConfiguration->execute($monitoringServerId);
        }
        return $this->view([
            'status' => (int) $response->isSuccess(),
            'message' => $response->getMessage()
        ]);
    }

    /**
     * @param GenerateAllConfigurations $generateAllConfigurations
     * @param ReloadAllConfigurations $reloadAllConfigurations
     * @return View
     * @throws ConfigurationMonitoringServerException
     */
    public function generateAndReloadAllConfigurations(
        GenerateAllConfigurations $generateAllConfigurations,
        ReloadAllConfigurations $reloadAllConfigurations
    ): View {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $response = $generateAllConfigurations->execute();
        if ($response->isSuccess()) {
            $response = $reloadAllConfigurations->execute();
        }
        return $this->view([
            'status' => (int) $response->isSuccess(),
            'message' => $response->getMessage()
        ]);
    }
}
