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

namespace Centreon\Domain\MetaServiceConfiguration\Interfaces;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;

/**
 * This interface gathers all the reading operations on the meta service configuration repository.
 *
 * @package Centreon\Domain\MetaServiceConfiguration\Interfaces
 */
interface MetaServiceConfigurationReadRepositoryInterface
{
    /**
     * Find a meta service configuration by id.
     *
     * @param int $metaId Id of the meta service configuration to be found
     * @return MetaServiceConfiguration|null
     * @throws \Throwable
     */
    public function findById(int $metaId): ?MetaServiceConfiguration;

    /**
     * Find a meta service configuration by id and contact.
     *
     * @param int $metaId Id of the meta service configuration to be found
     * @param ContactInterface $contact Contact related to host category
     * @return MetaServiceConfiguration|null
     * @throws \Throwable
     */
    public function findByIdAndContact(int $metaId, ContactInterface $contact): ?MetaServiceConfiguration;

    /**
     * Find all meta services configurations.
     *
     * @return MetaServiceConfiguration[]
     * @throws \Throwable
     */
    public function findAll(): array;

    /**
     * Find all meta services configurations by contact.
     *
     * @param ContactInterface $contact Contact related to meta services configurations.
     * @return MetaServiceConfiguration[]
     * @throws \Throwable
     */
    public function findAllByContact(ContactInterface $contact): array;
}
