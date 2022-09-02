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

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This interface gathers all the reading operations on the host severity repository.
 *
 * @package Centreon\Domain\HostConfiguration\Interfaces
 */
interface HostSeverityReadRepositoryInterface
{
    /**
     * Find all host severities.
     *
     * @return HostSeverity[]
     * @throws \Throwable
     */
    public function findAll(): array;

    /**
     * Find all host severities by contact.
     *
     * @param ContactInterface $contact Contact related to host severities
     * @return Hostseverity[]
     * @throws \Throwable
     */
    public function findAllByContact(ContactInterface $contact): array;

    /**
     * Find a host severity by id.
     *
     * @param int $hostSeverityId Id of the host severity to be found
     * @return HostSeverity|null
     * @throws RepositoryException
     * @throws \Exception
     */
    public function findById(int $hostSeverityId): ?HostSeverity;

    /**
     * Find a host severity by id and contact.
     *
     * @param int $hostSeverityId Id of the host severity to be found
     * @param ContactInterface $contact Contact related to host severity
     * @return HostSeverity|null
     * @throws RepositoryException
     * @throws \Exception
     */
    public function findByIdAndContact(int $hostSeverityId, ContactInterface $contact): ?HostSeverity;

    /**
     * Find a host severity by host
     *
     * @param Host $host
     * @return HostSeverity|null
     */
    public function findByHost(Host $host): ?HostSeverity;
}
