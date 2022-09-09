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

namespace Centreon\Domain\HostConfiguration\Interfaces;

use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This interface gathers all the writing operations on the repository.
 *
 * @package Centreon\Domain\HostConfiguration\Interfaces
 */
interface HostConfigurationWriteRepositoryInterface
{
    /**
     * Add a host
     *
     * @param Host $host Host to add
     * @throws RepositoryException
     * @throws \Throwable
     */
    public function addHost(Host $host): void;

    /**
     * Update a host.
     *
     * @param Host $host
     * @throws \Throwable
     */
    public function updateHost(Host $host): void;

    /**
     * Change the activation status of host.
     *
     * @param int $hostId Host id for which we want to change the activation status
     * @param bool $shouldBeActivated TRUE to activate a host
     */
    public function changeActivationStatus(int $hostId, bool $shouldBeActivated): void;
}
