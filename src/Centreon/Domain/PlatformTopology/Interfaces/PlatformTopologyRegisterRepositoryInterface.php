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

namespace Centreon\Domain\PlatformTopology\Interfaces;

use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformTopology\PlatformTopology;
use Centreon\Domain\PlatformTopology\PlatformTopologyConflictException;
use Centreon\Domain\Proxy\Proxy;
use Centreon\Domain\Repository\RepositoryException;

interface PlatformTopologyRegisterRepositoryInterface
{
    /**
     * Register the platform on its parent
     *
     * @param PlatformTopology $platformTopology
     * @param PlatformInformation $platformInformation
     * @param Proxy|null $proxy
     * @throws RepositoryException
     * @throws PlatformTopologyConflictException
     */
    public function registerPlatformTopologyToParent(
        PlatformTopology $platformTopology,
        PlatformInformation $platformInformation,
        Proxy $proxy = null
    ): void;
}
