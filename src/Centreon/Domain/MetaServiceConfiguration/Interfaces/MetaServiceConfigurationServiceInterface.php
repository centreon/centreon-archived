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

use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;
use Centreon\Domain\MetaServiceConfiguration\Exception\MetaServiceConfigurationException;

/**
 * @package Centreon\Domain\MetaServiceConfiguration\Interfaces
 */
interface MetaServiceConfigurationServiceInterface
{
    /**
     * Find a meta service (for non admin user).
     *
     * @param int $metaId Id of the meta service to be found
     * @return MetaServiceConfiguration|null
     * @throws MetaServiceConfigurationException
     */
    public function findWithAcl(int $metaId): ?MetaServiceConfiguration;

    /**
     * Find a meta service (for admin user).
     *
     * @param int $metaId Id of the meta service to be found
     * @return MetaServiceConfiguration|null
     * @throws MetaServiceConfigurationException
     */
    public function findWithoutAcl(int $metaId): ?MetaServiceConfiguration;

    /**
     * Find all meta services configurations (for non admin user).
     *
     * @return MetaServiceConfiguration[]
     * @throws MetaServiceConfigurationException
     */
    public function findAllWithAcl(): array;

    /**
     * Find all meta services configurations (for admin user).
     *
     * @return MetaServiceConfiguration[]
     * @throws MetaServiceConfigurationException
     */
    public function findAllWithoutAcl(): array;
}
