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

namespace Centreon\Domain\PlatformTopology\Model;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformInterface;

class PlatformDto
{
    /**
     * @param PlatformInterface $platformSource
     * @return array<int|string>
     */
    public function createPlatformDto(PlatformInterface $platformSource): array
    {
        $platformToCreate = [];

        if (null !== $platformSource->getId()) {
            $platformToCreate['id'] = $platformSource->getId();
        }

        if (null !== $platformSource->getName()) {
            $platformToCreate['name'] = $platformSource->getName();
        }

        if (null !== $platformSource->getHostname()) {
            $platformToCreate['hostname'] = $platformSource->getHostname();
        }

        if (null !== $platformSource->getType()) {
            $platformToCreate['type'] = $platformSource->getType();
        }

        if (null !== $platformSource->getAddress()) {
            $platformToCreate['address'] = $platformSource->getAddress();
        }

        if (null !== $platformSource->getParentAddress()) {
            $platformToCreate['parentAddress'] = $platformSource->getParentAddress();
        }

        if (null !== $platformSource->getServerId()) {
            $platformToCreate['serverId'] = $platformSource->getServerId();
        }

        $platformToCreate['pending'] = (true === $platformSource->isPending() ? '1' : '0');

        return $platformToCreate;
    }
}
