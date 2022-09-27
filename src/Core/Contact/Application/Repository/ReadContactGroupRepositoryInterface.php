<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Contact\Application\Repository;

use Core\Contact\Domain\Model\ContactGroup;

interface ReadContactGroupRepositoryInterface
{
    /**
     * Get all contact groups
     *
     * @return array<ContactGroup>
     * @throws \Throwable
     */
    public function findAll(): array;

    /**
     * Get all contact groups of a contact.
     *
     * @param integer $userId
     * @return array<ContactGroup>
     * @throws \Throwable
     */
    public function findAllByUserId(int $userId): array;

    /**
     * Get a Contact Group
     *
     * @param int $contactGroupId
     * @return ContactGroup|null
     * @throws \Throwable
     */
    public function find(int $contactGroupId): ?ContactGroup;

    /**
     * Get Contact groups by their ids.
     *
     * @param int[] $contactGroupIds
     * @return ContactGroup[]
     * @throws \Throwable
     */
    public function findByIds(array $contactGroupIds): array;
}
