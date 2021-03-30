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

namespace Centreon\Domain\HostConfiguration\Interfaces\HostSeverity;

use Centreon\Domain\HostConfiguration\Exception\HostSeverityException;
use Centreon\Domain\HostConfiguration\HostSeverityService;
use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This interface gathers all the reading operations on the host severity repository.
 *
 * @package Centreon\Domain\HostConfiguration\Interfaces
 */
interface HostSeverityServiceInterface
{

    /**
     * Find all host severities (for non admin user).
     *
     * @return HostSeverity[]
     * @throws HostSeverityException
     */
    public function findAllWithAcl(): array;

    /**
     * Find all host severities (for admin user).
     *
     * @return HostSeverity[]
     * @throws HostSeverityException
     */
    public function findAllWithoutAcl(): array;

    /**
     * Find a host severity (for admin user).
     *
     * @param int $severityId Id of the host severity to be found
     * @return HostSeverity|null
     * @throws HostSeverityException
     * @throws RepositoryException
     */
    public function findWithoutAcl(int $severityId): ?HostSeverity;

    /**
     * Find a host severity (for non admin user).
     *
     * @param int $severityId Id of the host severity to be found
     * @return HostSeverity|null
     * @throws HostSeverityException
     * @throws RepositoryException
     */
    public function findWithAcl(int $severityId): ?HostSeverity;
}
