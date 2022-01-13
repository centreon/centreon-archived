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
use Centreon\Domain\Exception\TimeoutException;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\MonitoringServer\Exception\MonitoringServerException;
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
use Symfony\Component\HttpFoundation\Response;

/**
 * This class is designed to manage all requests concerning monitoring servers
 *
 * @package Centreon\Application\Controller
 */
class MonitoringServerController extends AbstractController
{
    use LoggerTrait;

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
    public function findServers(RequestParametersInterface $requestParameters): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $context = (new Context())->setGroups([
            MonitoringServer::SERIALIZER_GROUP_MAIN,
        ]);
        return $this->view(
            [
                'result' => $this->monitoringServerService->findServers(),
                'meta' => $requestParameters->toArray()
            ]
        )->setContext($context);
    }

    /**
     * @param GenerateConfiguration $generateConfiguration
     * @param int $monitoringServerId
     * @return View
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     */
    public function generateConfiguration(GenerateConfiguration $generateConfiguration, int $monitoringServerId): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->execute(
            function () use ($generateConfiguration, $monitoringServerId) {
                $generateConfiguration->execute($monitoringServerId);
            }
        );
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param GenerateAllConfigurations $generateAllConfigurations
     * @return View
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     */
    public function generateAllConfigurations(GenerateAllConfigurations $generateAllConfigurations): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->execute(
            function () use ($generateAllConfigurations) {
                $generateAllConfigurations->execute();
            }
        );
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param ReloadConfiguration $reloadConfiguration
     * @param int $monitoringServerId
     * @return View
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     */
    public function reloadConfiguration(ReloadConfiguration $reloadConfiguration, int $monitoringServerId): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->execute(
            function () use ($reloadConfiguration, $monitoringServerId) {
                $reloadConfiguration->execute($monitoringServerId);
            }
        );
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param ReloadAllConfigurations $reloadAllConfigurations
     * @return View
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     */
    public function reloadAllConfigurations(ReloadAllConfigurations $reloadAllConfigurations): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->execute(
            function () use ($reloadAllConfigurations) {
                $reloadAllConfigurations->execute();
            }
        );
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Generate and reload the configuration of a monitoring server.
     *
     * @param GenerateConfiguration $generateConfiguration
     * @param ReloadConfiguration $reloadConfiguration
     * @param int $monitoringServerId
     * @return View
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     */
    public function generateAndReloadConfiguration(
        GenerateConfiguration $generateConfiguration,
        ReloadConfiguration $reloadConfiguration,
        int $monitoringServerId
    ): View {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->execute(
            function () use ($generateConfiguration, $reloadConfiguration, $monitoringServerId) {
                $generateConfiguration->execute($monitoringServerId);
                $reloadConfiguration->execute($monitoringServerId);
            }
        );
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Generate and reload all monitoring servers configurations.
     *
     * @param GenerateAllConfigurations $generateAllConfigurations
     * @param ReloadAllConfigurations $reloadAllConfigurations
     * @return View
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     */
    public function generateAndReloadAllConfigurations(
        GenerateAllConfigurations $generateAllConfigurations,
        ReloadAllConfigurations $reloadAllConfigurations
    ): View {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $this->execute(
            function () use ($generateAllConfigurations, $reloadAllConfigurations) {
                $generateAllConfigurations->execute();
                $reloadAllConfigurations->execute();
            }
        );
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param callable $callable
     * @throws EntityNotFoundException
     * @throws MonitoringServerException
     */
    private function execute(callable $callable): void
    {
        try {
            $callable();
        } catch (TimeoutException $ex) {
            $this->error($ex->getMessage());
            throw new MonitoringServerException(
                'The operation timed out - please use the legacy export menu to workaround this problem'
            );
        } catch (EntityNotFoundException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
            throw new MonitoringServerException(
                'There was an consistency error in the exported files  - please use the legacy export menu to '
                . 'troubleshoot'
            );
        }
    }
}
