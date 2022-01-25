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

namespace Centreon\Domain\HostConfiguration;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\HostConfiguration\Exception\HostSeverityException;
use Centreon\Domain\HostConfiguration\Interfaces\HostSeverity\HostSeverityReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostSeverity\HostSeverityServiceInterface;
use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This class is designed to manage the host severities.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostSeverityService implements HostSeverityServiceInterface
{
    /**
     * @var HostSeverityReadRepositoryInterface
     */
    private $readRepository;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @param HostSeverityReadRepositoryInterface $repository
     * @param ContactInterface $contact
     */
    public function __construct(
        HostSeverityReadRepositoryInterface $repository,
        ContactInterface $contact
    ) {
        $this->readRepository = $repository;
        $this->contact = $contact;
    }

    /**
     * @inheritDoc
     */
    public function findAllWithAcl(): array
    {
        try {
            return $this->readRepository->findAllByContact($this->contact);
        } catch (\Throwable $ex) {
            throw HostSeverityException::findHostSeveritiesException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAllWithoutAcl(): array
    {
        try {
            return $this->readRepository->findAll();
        } catch (\Throwable $ex) {
            throw HostSeverityException::findHostSeveritiesException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findWithAcl(int $severityId): ?HostSeverity
    {
        try {
            return $this->readRepository->findByIdAndContact($severityId, $this->contact);
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw HostSeverityException::findHostSeverityException(['id' => $severityId], $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findWithoutAcl(int $severityId): ?HostSeverity
    {
        try {
            return $this->readRepository->findById($severityId);
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw HostSeverityException::findHostSeverityException(['id' => $severityId], $ex);
        }
    }
}
