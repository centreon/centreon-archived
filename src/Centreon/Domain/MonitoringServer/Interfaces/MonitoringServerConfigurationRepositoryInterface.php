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

namespace Centreon\Domain\MonitoringServer\Interfaces;

use Centreon\Domain\Authentication\Exception\AuthenticationException;
use Centreon\Domain\Exception\TimeoutException;
use Centreon\Domain\Repository\RepositoryException;

/**
 * @package Centreon\Domain\MonitoringServer\Interfaces
 */
interface MonitoringServerConfigurationRepositoryInterface
{
    /**
     * @param int $monitoringServerId
     * @throws RepositoryException
     * @throws TimeoutException
     * @throws AuthenticationException
     */
    public function generateConfiguration(int $monitoringServerId): void;

    /**
     * @param int $monitoringServerId
     * @throws RepositoryException
     * @throws TimeoutException
     * @throws AuthenticationException
     */
    public function moveExportFiles(int $monitoringServerId): void;

    /**
     * @param int $monitoringServerId
     * @throws RepositoryException
     * @throws TimeoutException
     * @throws AuthenticationException
     */
    public function reloadConfiguration(int $monitoringServerId): void;
}
