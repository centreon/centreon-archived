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

namespace Centreon\Domain\HostConfiguration\Interfaces\HostGroup;

use Centreon\Domain\HostConfiguration\Exception\HostGroupException;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Repository\RepositoryException;

/**
 * @package Centreon\Domain\HostConfiguration\Interfaces\HostGroup
 */
interface HostGroupServiceInterface
{
    /**
     * Find a host group (for non admin user).
     *
     * @param int $groupId Id of the host group to be found
     * @return HostGroup|null
     * @throws HostGroupException
     * @throws RepositoryException
     */
    public function findWithAcl(int $groupId): ?HostGroup;

    /**
     * Find a host group (for admin user).
     *
     * @param int $groupId Id of the host group to be found
     * @return HostGroup|null
     * @throws HostGroupException
     * @throws RepositoryException
     */
    public function findWithoutAcl(int $groupId): ?HostGroup;

    /**
     * Find all host groups (for non admin user).
     *
     * @return HostGroup[]
     * @throws HostGroupException
     * @throws RepositoryException
     */
    public function findAllWithAcl(): array;

    /**
     * Find all host groups (for admin user).
     *
     * @return HostGroup[]
     * @throws HostGroupException
     * @throws RepositoryException
     */
    public function findAllWithoutAcl(): array;
}
