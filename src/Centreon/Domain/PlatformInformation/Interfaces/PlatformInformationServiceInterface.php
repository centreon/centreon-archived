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

namespace Centreon\Domain\PlatformInformation\Interfaces;

use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformInformation\PlatformInformationException;

interface PlatformInformationServiceInterface
{
    /**
     * Get monitoring server data
     * @return PlatformInformation|null
     * @throws PlatformInformationException
     */
    public function getInformation(): ?PlatformInformation;

    /**
     * Update platform information
     *
     * @param PlatformInformation $platformInformationUpdated
     * @return PlatformInformation|null
     * @throws PlatformInformationException
     */
    public function updatePlatformInformation(PlatformInformation $platformInformationUpdated): ?PlatformInformation;

    /**
     * Update the Existing PlatformInformation from an array of properties.
     *
     * @param array $platformUpdateProperty
     * @return PlatformInformation|null
     */
    public function updateExistingInformationFromArray(array $platformUpdateProperty): ?PlatformInformation;
}
