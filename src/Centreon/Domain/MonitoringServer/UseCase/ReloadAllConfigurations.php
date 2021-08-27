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

namespace Centreon\Domain\MonitoringServer\UseCase;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\MonitoringServer\Exception\ConfigurationMonitoringServerException;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerRepositoryInterface;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerConfigurationRepositoryInterface;

/**
 * This class is designed to represent a use case to reload the monitoring server configurations.
 *
 * @package Centreon\Domain\MonitoringServer\UseCase
 */
class ReloadAllConfigurations
{
    use LoggerTrait;

    public const SUCCESS_MESSAGE = 'Success';

    /**
     * @var MonitoringServerConfigurationRepositoryInterface
     */
    private $repository;

     /**
     * @var MonitoringServerRepositoryInterface
     */
    private $monitoringServerRepository;

    /**
     * @param MonitoringServerRepositoryInterface $monitoringServerRepository
     * @param MonitoringServerConfigurationRepositoryInterface $repository
     */
    public function __construct(
        MonitoringServerRepositoryInterface $monitoringServerRepository,
        MonitoringServerConfigurationRepositoryInterface $repository
    ) {
        $this->monitoringServerRepository = $monitoringServerRepository;
        $this->repository = $repository;
    }

    /**
     * @return GenerateReloadConfigurationResponse
     * @throws ConfigurationMonitoringServerException
     */
    public function execute(): GenerateReloadConfigurationResponse
    {
        try {
            $monitoringServers = $this->monitoringServerRepository->findServersWithRequestParameters();
        } catch (\Throwable $ex) {
            throw ConfigurationMonitoringServerException::errorRetrievingMonitoringServers($ex);
        }

        $message = self::SUCCESS_MESSAGE;
        $isSuccess = true;
        $lastMonitoringServerId = 0;
        try {
            foreach ($monitoringServers as $monitoringServer) {
                $lastMonitoringServerId = $monitoringServer->getId();
                if ($lastMonitoringServerId !== null) {
                    $this->info('Reload configuration for monitoring server #' . $lastMonitoringServerId);
                    $this->repository->reloadConfiguration($lastMonitoringServerId);
                } else {
                    $this->error('Monitoring server id from repository is null');
                }
            }
        } catch (\Exception $ex) {
            $isSuccess = false;
            $message = ConfigurationMonitoringServerException::errorOnReload(
                $lastMonitoringServerId,
                $ex->getMessage()
            )->getMessage();
        }
        return new GenerateReloadConfigurationResponse($isSuccess, $message);
    }
}
