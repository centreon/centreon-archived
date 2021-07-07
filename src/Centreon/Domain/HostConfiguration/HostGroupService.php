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
use Centreon\Domain\HostConfiguration\Exception\HostGroupException;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupServiceInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupWriteRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Repository\RepositoryException;

/**
 * This class is designed to manage the host groups.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostGroupService implements HostGroupServiceInterface
{
    /**
     * @var HostGroupReadRepositoryInterface
     */
    private $readRepository;

    /**
     * @var ContactInterface
     */
    private $contact;
    /**
     * @var HostGroupWriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * @param HostGroupReadRepositoryInterface $readRepository
     * @param HostGroupWriteRepositoryInterface $writeRepository
     * @param ContactInterface $contact
     */
    public function __construct(
        HostGroupReadRepositoryInterface $readRepository,
        HostGroupWriteRepositoryInterface $writeRepository,
        ContactInterface $contact
    ) {
        $this->readRepository = $readRepository;
        $this->writeRepository = $writeRepository;
        $this->contact = $contact;
    }

    /**
     * @inheritDoc
     */
    public function addGroup(HostGroup $group): void
    {
        try {
            $this->writeRepository->addGroup($group);
        } catch (\Throwable $ex) {
            throw HostGroupException::addGroupException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAllWithAcl(): array
    {
        try {
            return $this->readRepository->findAllByContact($this->contact);
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw HostGroupException::findHostGroupsException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAllWithoutAcl(): array
    {
        try {
            return $this->readRepository->findAll();
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw HostGroupException::findHostGroupsException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findByNamesWithoutAcl(array $groupsName): array
    {
        try {
            return $this->readRepository->findByNames($groupsName);
        } catch (\Throwable $ex) {
            throw HostGroupException::findHostGroupsException($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findWithAcl(int $groupId): ?HostGroup
    {
        try {
            return $this->readRepository->findByIdAndContact($groupId, $this->contact);
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw HostGroupException::findHostGroupException($ex, ['id' => $groupId]);
        }
    }

    /**
     * @inheritDoc
     */
    public function findWithoutAcl(int $groupId): ?HostGroup
    {
        try {
            return $this->readRepository->findById($groupId);
        } catch (RepositoryException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw HostGroupException::findHostGroupException($ex, ['id' => $groupId]);
        }
    }
}
