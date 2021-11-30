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

namespace Centreon\Infrastructure\MonitoringServer\Repository;

use DateTime;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\MonitoringServer\Interfaces\MonitoringServerConfigurationRepositoryInterface;
use Centreon\Infrastructure\MonitoringServer\Repository\Exception\MonitoringServerConfigurationRepositoryException;

/**
 * This class is designed to represent the API repository to manage the generation/move/reload of the monitoring
 * server configuration.
 *
 * @package Centreon\Infrastructure\MonitoringServer\Repository
 */
class MonitoringServerConfigurationRepositoryFile implements MonitoringServerConfigurationRepositoryInterface
{
    // use LoggerTrait;

    /**
     * @inheritDoc
     */
    public function generateConfiguration(int $monitoringServerId): void
    {
        $_POST['generate'] = true;
        $_POST['debug'] = true;
        $_POST['poller'] = $monitoringServerId;
        include( _CENTREON_PATH_ . 'www/include/configuration/configGenerate/xml/generateFiles.php');
    }

    /**
     * @inheritDoc
     */
    public function moveExportFiles(int $monitoringServerId): void
    {
        $_POST['poller'] = $monitoringServerId;
        include( _CENTREON_PATH_ . 'www/include/configuration/configGenerate/xml/moveFiles.php');
    }

     /**
     * @inheritDoc
     */
    public function reloadConfiguration(int $monitoringServerId): void
    {
        $_POST['poller'] = $monitoringServerId;
        $_POST['mode'] = 1;
        include( _CENTREON_PATH_ . 'www/include/configuration/configGenerate/xml/restartPollers.php');
    }

}
